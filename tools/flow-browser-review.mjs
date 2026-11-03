import { chromium } from 'playwright';
import path from 'node:path';
import { pathToFileURL } from 'node:url';
import { createServer } from 'node:http';

const sessionMode = process.argv.includes('--sessions');
const directory = path.resolve(`storage/app/private/${sessionMode ? 'session-review' : 'flow-review'}`);
const pages = sessionMode ? ['execution', 'session-detail', 'session-history'] : ['organizer', 'volunteer', 'profile', 'legacy', 'packages', 'package-form', 'organizers', 'notifications'];
const browser = await chromium.launch({
    executablePath: 'C:/Program Files/Google/Chrome/Application/chrome.exe',
    headless: true,
    args: ['--allow-file-access-from-files'],
});
let probe;
try {
    const page = await browser.newPage();
    for (const name of pages) {
        for (const width of [360, 768, 1280]) {
            await page.setViewportSize({ width, height: 1000 });
            await page.goto(pathToFileURL(path.join(directory, `${name}.html`)).href);
            await page.evaluate(() => document.fonts.ready);
            const state = await page.evaluate(() => ({
                readable: document.body.innerText.length > 100,
                overflow: document.documentElement.scrollWidth > innerWidth,
                alpine: Boolean(window.Alpine),
            }));
            if (!state.readable || state.overflow || !state.alpine) throw new Error(`Layout/runtime failure: ${name} ${width}`);
            if (name === 'profile') {
                await page.getByRole('button', { name: 'Edit Profil', exact: true }).click();
                await page.locator('[name=confirm_reverification]').waitFor({ state: 'visible' });
            }
            if (name === 'packages') {
                await page.getByRole('button', { name: 'Tambah Paket', exact: true }).click();
                await page.locator('[name=name]').first().waitFor({ state: 'visible' });
            }
            // Let the existing Alpine opacity/transform transitions finish before capture.
            await page.waitForTimeout(450);
            await page.screenshot({ path: path.join(directory, `${name}-${width}.png`), fullPage: true });
            console.log(`PASS ${name} ${width}px: readable, no horizontal overflow, Alpine loaded.`);
        }
    }
    if (sessionMode) {
        await page.goto(pathToFileURL(path.join(directory, 'execution.html')).href);
        const candidate = page.locator('form[action*="/attendance-sessions/"]').first();
        await candidate.evaluate(element => { element.dataset.sessionSubmitProbe = '1'; });
        const form = page.locator('form[data-session-submit-probe]');
        let receive;
        const submitted = new Promise(resolve => { receive = resolve; });
        probe = createServer((request, response) => {
            let body = '';
            request.on('data', chunk => { body += chunk; });
            request.on('end', () => {
                receive({ method: request.method, payload: new URLSearchParams(body) });
                response.writeHead(200, { 'Content-Type': 'text/html' });
                response.end('<p>Isolated submit probe.</p>');
            });
        });
        await new Promise(resolve => probe.listen(0, '127.0.0.1', resolve));
        const probeUrl = `http://127.0.0.1:${probe.address().port}/session-submit-probe`;
        await form.evaluate((element, url) => { element.action = url; }, probeUrl);
        await form.evaluate(element => {
            window.__attendanceSubmit = { valid: element.checkValidity(), alpine: Boolean(element._x_dataStack) };
            element.addEventListener('submit', event => {
                window.__attendanceSubmit.fired = true;
                setTimeout(() => { window.__attendanceSubmit.prevented = event.defaultPrevented; }, 0);
            });
        });
        const timeout = setTimeout(() => receive(null), 7000);
        await form.getByRole('button', { name: 'Simpan kehadiran sesi', exact: true }).click();
        const request = await submitted;
        clearTimeout(timeout);
        if (!request) {
            const state = await page.evaluate(() => window.__attendanceSubmit ?? null);
            throw new Error(`Session submit probe received no request: ${JSON.stringify(state)}`);
        }
        const payload = request.payload;
        if (request.method !== 'POST' || payload.get('_method') !== 'PATCH' || !payload.has('_token') || !payload.has('revision') || !payload.has('status')) {
            throw new Error('Session form did not submit its native POST/PATCH/CSRF payload.');
        }
        console.log('PASS session form: real browser click submits POST/PATCH with CSRF, revision and status to an isolated local HTTP probe; no application data changed.');
    }
} finally {
    probe?.closeAllConnections();
    probe?.close();
    await browser.close();
}
