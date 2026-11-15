import test from 'node:test';
import assert from 'node:assert/strict';
import setup from '../../resources/js/attendance-session-setup.js';

test('automatic setup posts once with CSRF and reloads only after success', async () => {
    const originals = { fetch: globalThis.fetch, document: globalThis.document, window: globalThis.window };
    try {
        let calls = 0, reloads = 0, resolve;
        globalThis.document = { querySelector: () => ({ content: 'test-csrf' }) };
        globalThis.window = { location: { reload: () => reloads++ } };
        globalThis.fetch = (url, request) => {
            calls++;
            assert.equal(url, '/initialize');
            assert.equal(request.method, 'POST');
            assert.equal(request.headers['X-CSRF-TOKEN'], 'test-csrf');
            assert.equal(request.credentials, 'same-origin');
            assert.deepEqual(JSON.parse(request.body), { applications: [12, 13] });
            return new Promise(done => { resolve = done; });
        };
        const state = setup({ url: '/initialize', applications: [12, 13] });
        state.init();
        await state.prepare();
        assert.equal(calls, 1);
        assert.equal(reloads, 0);
        resolve({ ok: true, redirected: false });
        await new Promise(done => setImmediate(done));
        assert.equal(reloads, 1);
        assert.equal(state.error, '');
    } finally { Object.assign(globalThis, originals); }
});

test('failure stops automatic retries and preserves validation text until explicit retry', async () => {
    const originals = { fetch: globalThis.fetch, document: globalThis.document, window: globalThis.window };
    try {
        let calls = 0, reloads = 0;
        globalThis.document = { querySelector: () => ({ content: 'test-csrf' }) };
        globalThis.window = { location: { reload: () => reloads++ } };
        globalThis.fetch = async () => { calls++; return { ok: false, json: async () => ({ errors: { attendance: ['Snapshot jadwal tidak tersedia.'] } }) }; };
        const state = setup({ url: '/initialize', applications: [12] });
        await state.prepare();
        assert.equal(state.busy, false);
        assert.equal(state.error, 'Snapshot jadwal tidak tersedia.');
        assert.equal(reloads, 0);
        assert.equal(calls, 1);
        await new Promise(done => setImmediate(done));
        assert.equal(calls, 1);
        globalThis.fetch = async () => { calls++; return { ok: true, redirected: false }; };
        await state.prepare();
        assert.equal(calls, 2);
        assert.equal(reloads, 1);
    } finally { Object.assign(globalThis, originals); }
});

test('no pending participants makes no request', () => {
    const state = setup({ url: '/initialize', applications: [] });
    state.prepare = () => { throw new Error('Unexpected mutation'); };
    state.init();
    assert.equal(state.busy, false);
});

test('missing CSRF token shows a recovery message and never posts without CSRF', async () => {
    const originals = { fetch: globalThis.fetch, document: globalThis.document, window: globalThis.window };
    try {
        let calls = 0;
        globalThis.document = { querySelector: () => null };
        globalThis.fetch = async () => { calls++; throw new Error('Must not post'); };
        const state = setup({ url: '/initialize', applications: [12] });
        await state.prepare();
        assert.equal(calls, 0);
        assert.equal(state.busy, false);
        assert.match(state.error, /Muat ulang halaman/);
        assert.doesNotMatch(state.error, /null|content|TypeError/);
    } finally { Object.assign(globalThis, originals); }
});

test('network exceptions do not leak technical details to the page', async () => {
    const originals = { fetch: globalThis.fetch, document: globalThis.document, window: globalThis.window };
    try {
        globalThis.document = { querySelector: () => ({ content: 'test-csrf' }) };
        globalThis.fetch = async () => { throw new TypeError('Technical transport exception'); };
        const state = setup({ url: '/initialize', applications: [12] });
        await state.prepare();
        assert.equal(state.busy, false);
        assert.match(state.error, /Coba lagi/);
        assert.doesNotMatch(state.error, /Technical/);
    } finally { Object.assign(globalThis, originals); }
});
