// Keep the search field and filters in place while replacing server-rendered results.
export function initializeAttendanceSearch(root) {
    const form = root.querySelector('form');
    const input = form?.querySelector('[name="search"]');
    if (!input) return;
    const feedback = document.createElement('p');
    feedback.className = 'text-xs text-slate-500 my-2';
    feedback.setAttribute('role', 'status');
    form.after(feedback);
    let timer, controller, sequence = 0;
    const search = async (url) => {
        clearTimeout(timer);
        controller?.abort();
        controller = new AbortController();
        const current = ++sequence;
        feedback.textContent = 'Mencari peserta...';
        root.querySelector('[data-attendance-results]')?.setAttribute('aria-busy', 'true');
        try {
            const response = await fetch(url, {signal: controller.signal, credentials: 'same-origin'});
            if (!response.ok) throw new Error('search');
            const page = new DOMParser().parseFromString(await response.text(), 'text/html');
            const results = page.querySelector('[data-attendance-results]');
            if (!results) throw new Error('search');
            if (current !== sequence) return;
            root.querySelector('[data-attendance-results]').replaceWith(results);
            history.replaceState(null, '', url);
            feedback.textContent = 'Daftar peserta diperbarui.';
        } catch (error) {
            if (current === sequence && error.name !== 'AbortError') feedback.textContent = 'Pencarian gagal. Tekan Enter untuk mencoba kembali.';
        } finally {
            if (current === sequence) root.querySelector('[data-attendance-results]')?.removeAttribute('aria-busy');
        }
    };
    const formUrl = () => {
        const url = new URL(form.action, location.href);
        url.search = new URLSearchParams(new FormData(form)).toString();
        return url;
    };
    input.addEventListener('input', () => {
        // Invalidate an older response immediately, before the debounce expires.
        clearTimeout(timer);
        controller?.abort();
        sequence++;
        timer = setTimeout(() => search(formUrl()), 350);
    });
    root.addEventListener('click', event => {
        const link = event.target.closest('[data-attendance-results] a[href]');
        if (!link || event.ctrlKey || event.metaKey || event.shiftKey || event.altKey) return;
        const url = new URL(link.href);
        if (url.origin !== location.origin || url.pathname !== location.pathname) return;
        event.preventDefault();
        search(url);
    });
    window.addEventListener('pagehide', () => { clearTimeout(timer); controller?.abort(); });
}
