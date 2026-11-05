import test from 'node:test';
import assert from 'node:assert/strict';
import eventCoverUpload from '../../resources/js/event-cover-upload.js';

test('invalid replacement restores saved cover and clears file selection', () => {
    const state = eventCoverUpload('/saved-cover');
    const event = { target: { files: [{ type: 'image/svg+xml', size: 4 }], value: 'bad.svg' } };
    state.choose(event);
    assert.equal(state.preview, '/saved-cover');
    assert.equal(event.target.value, '');
    assert.match(state.error, /JPG/);
});

test('remove clears preview, file input and marks persisted cover for removal', () => {
    const state = eventCoverUpload('/saved-cover');
    state.$refs = { file: { value: 'selected.png' } };
    state.remove();
    assert.equal(state.preview, null);
    assert.equal(state.removed, true);
    assert.equal(state.$refs.file.value, '');
});

test('oversized image is rejected before making a preview', () => {
    const state = eventCoverUpload(null, true);
    state.choose({ target: { files: [{ type: 'image/jpeg', size: 5 * 1024 * 1024 + 1 }], value: 'big.jpg' } });
    assert.equal(state.preview, null);
    assert.match(state.error, /5 MB/);
    assert.equal(state.removed, true);
});
