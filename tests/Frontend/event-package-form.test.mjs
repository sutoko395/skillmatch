import test from 'node:test';
import assert from 'node:assert/strict';
import eventPackageForm from '../../resources/js/event-package-form.js';

const plans = [
    { id: 1, name: 'Free', max_registration_days: 7 },
    { id: 2, name: 'Standard', max_registration_days: 30 },
    { id: 3, name: 'Premium', max_registration_days: 60 },
];

test('Free permits exactly seven days, warns one minute beyond, and blocks submit', () => {
    const form = eventPackageForm(plans, 1, '2026-11-01T09:00', '2026-11-08T09:00');
    assert.equal(form.durationSeconds, 7 * 86400);
    assert.equal(form.exceedsLimit, false);
    form.deadline = '2026-11-08T09:01';
    assert.equal(form.exceedsLimit, true);
    assert.match(form.warning, /Free \(7 hari\)/);
    let prevented = false;
    form.submit({ preventDefault() { prevented = true; } });
    assert.equal(prevented, true);
    assert.equal(form.busy, false);
    form.packageId = '2';
    assert.equal(form.exceedsLimit, false);
    assert.equal(form.warning, '');
    form.submit({ preventDefault() { assert.fail('Valid form was blocked'); } });
    assert.equal(form.busy, true);
});

test('Standard and Premium use their own exact duration limits', () => {
    const form = eventPackageForm(plans, 2, '2026-11-01T09:00', '2026-12-01T09:00');
    assert.equal(form.exceedsLimit, false);
    form.deadline = '2026-12-01T09:01';
    assert.equal(form.exceedsLimit, true);
    form.packageId = '3';
    assert.equal(form.exceedsLimit, false);
    form.deadline = '2026-12-31T09:00';
    assert.equal(form.durationSeconds, 60 * 86400);
    assert.equal(form.exceedsLimit, false);
    form.deadline = '2026-12-31T09:01';
    assert.equal(form.exceedsLimit, true);
});

test('Admin configured limits and shortening dates update the warning', () => {
    const form = eventPackageForm([{ id: 8, name: 'Custom', max_registration_days: 3 }], 8, '2026-11-01T09:00', '2026-11-05T09:00');
    assert.equal(form.exceedsLimit, true);
    assert.match(form.warning, /Custom \(3 hari\)/);
    form.deadline = '2026-11-03T09:00';
    assert.equal(form.exceedsLimit, false);
});

test('Missing package blocks submit and empty dates do not show a duration warning', () => {
    const form = eventPackageForm(plans, '', '', '');
    assert.equal(form.exceedsLimit, false);
    let prevented = false;
    form.submit({ preventDefault() { prevented = true; } });
    assert.equal(prevented, true);
    form.packageId = '1';
    assert.equal(form.durationSeconds, 0);
    assert.equal(form.warning, '');
});
