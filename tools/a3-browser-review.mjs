import { spawn } from 'node:child_process';
import fs from 'node:fs/promises';
import path from 'node:path';
import { pathToFileURL } from 'node:url';
import { createServer } from 'node:http';

const directory = path.resolve('storage/app/private/a3-review');
const browser = spawn('C:/Program Files/Google/Chrome/Application/chrome.exe', [
  '--headless=new', '--disable-gpu', '--allow-file-access-from-files',
  '--remote-debugging-port=9338', `--user-data-dir=${path.join(directory, 'chrome-profile')}`, 'about:blank',
], { windowsHide: true, stdio: 'ignore' });
let socket;
let probe;
try {
  let pages;
  for (let i = 0; i < 100; i++) {
    try { pages = await (await fetch('http://127.0.0.1:9338/json')).json(); if (pages.length) break; } catch {}
    await new Promise(resolve => setTimeout(resolve, 100));
  }
  if (!pages?.length) throw new Error('Browser did not start');
  socket = new WebSocket(pages.find(page => page.type === 'page').webSocketDebuggerUrl);
  await new Promise((resolve, reject) => { socket.onopen = resolve; socket.onerror = reject; });
  let id = 0;
  const pending = new Map();
  socket.onmessage = ({ data }) => {
    const message = JSON.parse(data);
    if (message.id && pending.has(message.id)) {
      const { resolve, reject, timeout } = pending.get(message.id);
      pending.delete(message.id); clearTimeout(timeout);
      message.error ? reject(new Error(message.error.message)) : resolve(message.result);
    }
  };
  const send = (method, params = {}) => new Promise((resolve, reject) => {
    const current = ++id;
    const timeout = setTimeout(() => reject(new Error(`Timeout: ${method}`)), 15000);
    pending.set(current, { resolve, reject, timeout });
    socket.send(JSON.stringify({ id: current, method, params }));
  });
  await send('Page.enable');
  if (process.argv.includes('--accept-submit')) {
    let resolvePost;
    const received = new Promise(resolve => { resolvePost = resolve; });
    probe = createServer((request, response) => {
      let body = '';
      request.on('data', chunk => { body += chunk; });
      request.on('end', () => {
        resolvePost({ method: request.method, csrf: new URLSearchParams(body).has('_token') });
        response.writeHead(200, { 'Content-Type': 'text/html' });
        response.end('<p>Local submit probe received.</p>');
      });
    });
    await new Promise(resolve => probe.listen(0, '127.0.0.1', resolve));
    await send('Page.navigate', { url: pathToFileURL(path.join(directory, 'selection.html')).href });
    await new Promise(resolve => setTimeout(resolve, 1000));
    const setup = await send('Runtime.evaluate', { expression: `(() => {
      const form = document.querySelector('form[action$="/accept"]');
      if (!form || !window.Alpine || !form._x_dataStack) return false;
      form.action = 'http://127.0.0.1:${probe.address().port}/accept-probe';
      form.querySelector('button[type=submit]').click();
      return true;
    })()`, returnByValue: true });
    if (!setup.result.value) throw new Error('Accept form or Alpine unavailable');
    const timeout = setTimeout(() => resolvePost(null), 5000);
    const result = await received;
    clearTimeout(timeout);
    if (!result || result.method !== 'POST' || !result.csrf) throw new Error('Accept did not send POST with CSRF');
    console.log('PASS accept: real browser click with Alpine sends POST and CSRF to isolated local probe; no application data changed.');
  } else {
  for (const page of ['execution', 'selection', 'history']) {
    for (const width of [360, 768, 1280]) {
      await send('Emulation.setDeviceMetricsOverride', { width, height: 1000, deviceScaleFactor: 1, mobile: false });
      await send('Page.navigate', { url: pathToFileURL(path.join(directory, `${page}.html`)).href });
      await new Promise(resolve => setTimeout(resolve, 800));
      const result = await send('Runtime.evaluate', { expression: `(async () => {
        await document.fonts.ready;
        const control = document.querySelector('main input:not([type=hidden]), main button, main a');
        control?.focus();
        return {width: innerWidth, content: document.documentElement.scrollWidth,
          readable: document.body.innerText.length > 100, focused: !control || document.activeElement === control};
      })()`, awaitPromise: true, returnByValue: true });
      const state = result.result.value;
      if (!state || state.content > state.width || !state.readable || !state.focused) throw new Error(`Layout check failed: ${page} ${width}`);
      const shot = await send('Page.captureScreenshot', { format: 'png', captureBeyondViewport: true });
      await fs.writeFile(path.join(directory, `${page}-${width}.png`), Buffer.from(shot.data, 'base64'));
      console.log(`PASS ${page} ${width}px: readable, keyboard focus, no horizontal overflow.`);
    }
  }
  }
} finally {
  probe?.closeAllConnections();
  probe?.close();
  socket?.close();
  browser.kill();
}
