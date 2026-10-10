import test from 'node:test';
import assert from 'node:assert/strict';
import paymentStatus from '../../resources/js/payment-status.js';

function windowFor(t, reload) {
    const original = globalThis.window;
    globalThis.window = { location: { reload, href: 'http://localhost/organizer/orders/1' } };
    t.after(() => {
        if (original === undefined) delete globalThis.window;
        else globalThis.window = original;
    });
}

function form(status = 'pending', automatic = true) {
    const state = paymentStatus(status, automatic);
    state.$refs = { syncForm: {
        action: 'http://localhost/organizer/orders/1/sync',
        querySelector: () => ({ value: 'test-csrf' }),
    } };
    return state;
}

test('pending checkout checks once via CSRF POST and reloads only after server confirmation', async (t) => {
    let calls = 0;
    let reloads = 0;
    t.mock.method(globalThis, 'fetch', async (url, options) => {
        calls++;
        assert.equal(url, 'http://localhost/organizer/orders/1/sync');
        assert.equal(options.method, 'POST');
        assert.equal(options.credentials, 'same-origin');
        assert.equal(options.headers.Accept, 'application/json');
        assert.equal(options.headers['X-CSRF-TOKEN'], 'test-csrf');
        assert.equal(options.body, undefined); // Never forwards status from checkout/browser.
        return { ok: true, json: async () => ({ status: 'paid' }) };
    });
    windowFor(t, () => reloads++);
    const state = form();
    await state.init();
    assert.equal(calls, 1);
    assert.equal(reloads, 1);
    assert.equal(state.busy, false);
});

test('unprepared checkout and paid page do not automatically check', async (t) => {
    t.mock.method(globalThis, 'fetch', () => assert.fail('Unexpected automatic check'));
    await form('pending', false).init();
    await form('paid', true).init();
});

test('pending from server shows retry guidance without reload or duplicate simultaneous request', async (t) => {
    let resolve;
    let calls = 0;
    t.mock.method(globalThis, 'fetch', () => {
        calls++;
        return new Promise(done => { resolve = done; });
    });
    windowFor(t, () => assert.fail('Pending must not reload'));
    const state = form();
    const first = state.init();
    assert.equal(state.busy, true);
    await state.sync();
    assert.equal(calls, 1);
    resolve({ ok: true, json: async () => ({ status: 'pending' }) });
    await first;
    assert.equal(state.failed, false);
    assert.equal(state.busy, false);
    assert.match(state.message, /masih mencatat pembayaran menunggu/);
});

test('expired session, forbidden, throttled and gateway failures allow manual retry', async (t) => {
    for (const [status, message] of [[401, /Sesi berakhir/], [419, /Sesi berakhir/], [403, /tidak memiliki akses/], [429, /Terlalu banyak/], [422, /belum dapat diperiksa/]]) {
        t.mock.method(globalThis, 'fetch', async () => ({ ok: false, status }));
        const state = form();
        await state.sync();
        assert.equal(state.failed, true);
        assert.equal(state.busy, false);
        assert.match(state.message, message);
        t.mock.restoreAll();
    }
});

test('network and malformed response do not show raw errors or reload', async (t) => {
    windowFor(t, () => assert.fail('Invalid response must not reload'));
    for (const fetch of [async () => { throw new Error('private diagnostic'); }, async () => ({ ok: true, json: async () => ({ status: 'forged' }) })]) {
        const mock = t.mock.method(globalThis, 'fetch', fetch);
        const state = form();
        await state.sync();
        assert.equal(state.failed, true);
        assert.equal(state.busy, false);
        assert.match(state.message, /Koneksi pemeriksaan terputus/);
        assert.doesNotMatch(state.message, /private diagnostic|forged/);
        mock.mock.restore();
    }
});

function linkClick(href, attributes = {}, eventOptions = {}) {
    const link = { href, target: '', hasAttribute: name => attributes[name] === true, ...attributes };
    return { button: 0, defaultPrevented: false, target: { closest: () => link }, ...eventOptions };
}

test('one native menu click aborts an in-flight check and late paid response cannot reload the page', async (t) => {
    let resolve;
    let signal;
    t.mock.method(globalThis, 'fetch', async (url, options) => {
        signal = options.signal;
        return new Promise(done => { resolve = done; });
    });
    windowFor(t, () => assert.fail('Late payment response must not interrupt navigation'));
    const state = form();
    const check = state.init();
    const event = linkClick('http://localhost/organizer/events');
    state.navigateAway(event);
    assert.equal(event.defaultPrevented, false);
    assert.equal(state.leaving, true);
    assert.equal(signal.aborted, true);
    resolve({ ok: true, json: async () => ({ status: 'paid' }) });
    await check;
    assert.equal(state.failed, false);
    assert.equal(state.busy, false);
    assert.equal(state.requestController, null);
});

test('modifier clicks, new tabs, downloads and same-page anchors do not cancel the check', (t) => {
    windowFor(t, () => {});
    const state = form();
    for (const event of [
        linkClick('http://localhost/organizer/events', {}, { ctrlKey: true }),
        linkClick('http://localhost/organizer/events', {}, { button: 1 }),
        linkClick('http://localhost/organizer/events', {}, { defaultPrevented: true }),
        linkClick('http://localhost/organizer/events', { target: '_blank' }),
        linkClick('http://localhost/file.pdf', { download: true }),
        linkClick('http://localhost/organizer/orders/1#detail'),
        linkClick('mailto:contact@example.test'),
    ]) {
        state.navigateAway(event);
        assert.equal(state.leaving, false);
    }
});

test('navigation during response parsing suppresses reload and component cleanup aborts request', async (t) => {
    let resolveBody;
    t.mock.method(globalThis, 'fetch', async () => ({
        ok: true,
        json: () => new Promise(resolve => { resolveBody = resolve; }),
    }));
    windowFor(t, () => assert.fail('Response body arriving after navigation must not reload'));
    const state = form();
    const check = state.init();
    await Promise.resolve();
    const controller = state.requestController;
    state.destroy();
    assert.equal(controller.signal.aborted, true);
    resolveBody({ status: 'paid' });
    await check;
    await state.sync(); // A departing page cannot start another gateway request.
    assert.equal(state.busy, false);
});
