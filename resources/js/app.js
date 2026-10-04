

import Alpine from 'alpinejs';

window.Alpine = Alpine;

Alpine.start();

// Server-rendered rows remain editable when JavaScript is unavailable.
document.querySelectorAll('[data-repeater]').forEach(container => {
    const rows = container.querySelector('[data-rows]');
    let index = Math.max(...Array.from(rows.querySelectorAll('input,select')).map(el => Number(el.name.match(/\[(\d+)\]/)?.[1] ?? 0))) + 1;
    container.addEventListener('click', event => {
        if (event.target.closest('[data-add]')) {
            rows.insertAdjacentHTML('beforeend', container.querySelector('template').innerHTML.replaceAll('__INDEX__', index++));
            rows.lastElementChild.querySelector('input,select')?.focus();
        }
        const remove = event.target.closest('[data-remove]');
        if (remove) { remove.closest('[data-row]').remove(); container.querySelector('[data-add]').focus(); }
    });
});
document.querySelectorAll('[data-saving-form]').forEach(form => form.addEventListener('submit', () => {
    form.querySelector('[type=submit]').disabled = true;
    form.querySelector('[data-saving-status]').textContent = 'Menyimpan...';
}));
