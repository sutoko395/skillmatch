import { chromium } from 'playwright';
import path from 'node:path';
import { pathToFileURL } from 'node:url';

const directory = path.resolve('storage/app/private/phone-review');
const browser = await chromium.launch({
    executablePath: 'C:/Program Files/Google/Chrome/Application/chrome.exe',
    headless: true,
    args: ['--allow-file-access-from-files'],
});
try {
    const page = await browser.newPage();
    const errors = [];
    page.on('pageerror', error => errors.push(error.message));
    for (const name of ['phone-volunteer', 'phone-organizer-create', 'phone-organizer-edit']) {
        for (const width of [360, 1280]) {
            await page.setViewportSize({ width, height: 1000 });
            await page.goto(pathToFileURL(path.join(directory, `${name}.html`)).href);
            if (name === 'phone-organizer-edit') await page.getByRole('button', { name: 'Edit Profil', exact: true }).click();
            const input = page.locator('input[name=phone]:visible');
            await input.waitFor({ state: 'visible' });
            await input.fill('');
            await input.pressSequentially('abc081234567890xyz');
            if (await input.inputValue() !== '081234567890') throw new Error(`Alphabetic keyboard input was accepted: ${name}`);
            const pasteBlocked = await input.evaluate(element => {
                const data = new DataTransfer();
                data.setData('text', 'abc123');
                const event = new ClipboardEvent('paste', { clipboardData: data, bubbles: true, cancelable: true });
                return !element.dispatchEvent(event);
            });
            if (!pasteBlocked) throw new Error(`Alphabetic paste was accepted: ${name}`);
            for (const [value, valid] of [['letters', false], ['08123abc456', false], ['1234567', false],
                ['1234567890123456', false], ['0812 3456 7890', false], ['++628123456789', false],
                ['081234567890', true], ['+6281234567890', true]]) {
                // Bypass the typing guard to test native validation of scripted/autofilled values too.
                await input.evaluate((element, next) => {
                    element.value = next;
                    element.dispatchEvent(new Event('input', { bubbles: true }));
                }, value);
                if (await input.evaluate(element => element.checkValidity()) !== valid) throw new Error(`Native phone validity mismatch: ${name}`);
            }
            const overflow = await page.evaluate(() => document.documentElement.scrollWidth > innerWidth);
            if (overflow || errors.length) throw new Error(`Browser/layout error: ${name}`);
            await page.screenshot({ path: path.join(directory, `${name}-${width}.png`), fullPage: true });
            console.log(`PASS ${name} ${width}px: typing, paste, native validation, valid phone, no overflow.`);
        }
    }
    for (const name of ['phone-packages', 'phone-package-form']) {
        await page.goto(pathToFileURL(path.join(directory, `${name}.html`)).href);
        if (name === 'phone-packages') await page.getByRole('button', { name: 'Tambah Paket', exact: true }).click();
        for (const [field, max] of Object.entries({ price: 1000000000, max_positions: 1000, max_applications: 1000000, max_registration_days: 365 })) {
            const input = page.locator(`input[name=${field}]:visible`);
            await input.fill(String(max + 1));
            if (await input.evaluate(element => element.checkValidity())) throw new Error(`Package limit accepted: ${field}`);
            await input.fill('1.5');
            if (await input.evaluate(element => element.checkValidity())) throw new Error(`Fractional count accepted: ${field}`);
            await input.fill(String(max));
            if (!await input.evaluate(element => element.checkValidity())) throw new Error(`Valid package limit rejected: ${field}`);
        }
        console.log(`PASS ${name}: numeric upper bounds and integer steps match server validation.`);
    }
    if (errors.length) throw new Error('Browser runtime errors.');
    console.log('Synthetic HTML only; no real user data, form submission or network messages.');
} finally {
    await browser.close();
}
