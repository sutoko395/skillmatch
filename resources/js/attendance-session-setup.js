export default function attendanceSessionSetup({ url, applications }) {
    return {
        busy: false,
        error: '',
        init() {
            if (applications.length) this.prepare();
        },
        async prepare() {
            if (this.busy) return;
            this.busy = true;
            this.error = '';
            let failureMessage = 'Sesi belum berhasil dimuat. Coba lagi atau muat ulang halaman.';
            try {
                const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content;
                if (!csrfToken) {
                    failureMessage = 'Sesi keamanan belum tersedia. Muat ulang halaman lalu coba lagi.';
                    throw new Error();
                }
                const response = await fetch(url, {
                    method: 'POST',
                    credentials: 'same-origin',
                    headers: {
                        Accept: 'application/json',
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': csrfToken,
                    },
                    body: JSON.stringify({ applications }),
                });
                if (!response.ok || response.redirected) {
                    const result = await response.json().catch(() => ({}));
                    failureMessage = Object.values(result.errors ?? {}).flat()[0] ?? failureMessage;
                    throw new Error();
                }
                window.location.reload();
            } catch {
                this.error = failureMessage;
                this.busy = false;
            }
        },
    };
}
