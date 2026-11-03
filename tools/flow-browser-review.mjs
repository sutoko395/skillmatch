import { chromium } from 'playwright';
import path from 'node:path';
import { pathToFileURL } from 'node:url';
import { createServer } from 'node:http';

const sessionMode = false;
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
} finally {
    probe?.closeAllConnections();
    probe?.close();
    await browser.close();
}
