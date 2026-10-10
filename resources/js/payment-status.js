export default function paymentStatus(initialStatus, automaticallyCheck) {
    return {
        busy: false,
        message: '',
        failed: false,
        leaving: false,
        requestController: null,

        navigateAway(event) {
            if (event.defaultPrevented || event.button !== 0 || event.ctrlKey || event.metaKey || event.shiftKey || event.altKey) return;
            const link = event.target.closest?.('a[href]');
            if (!link || link.hasAttribute('download') || (link.target && link.target !== '_self')) return;
            const destination = new URL(link.href, window.location.href);
            if (!['http:', 'https:'].includes(destination.protocol)) return;
            const current = new URL(window.location.href);
            if (destination.origin === current.origin && destination.pathname === current.pathname && destination.search === current.search && destination.hash) return;
            this.cancel(); // Leave the native link click intact; do not redirect or preventDefault.
        },

        cancel() {
            this.leaving = true;
            this.requestController?.abort();
        },

        destroy() {
            this.cancel();
        },

        init() {
            if (automaticallyCheck && initialStatus === 'pending') {
                return this.sync();
            }
        },

        async sync() {
            if (this.busy || this.leaving) return;
            this.busy = true;
            this.failed = false;
            this.message = 'Memeriksa pembayaran ke Midtrans...';
            const form = this.$refs.syncForm;
            const controller = new AbortController();
            this.requestController = controller;
            const timeout = setTimeout(() => controller.abort(), 8000);

            try {
                const response = await fetch(form.action, {
                    method: 'POST',
                    credentials: 'same-origin',
                    headers: {
                        Accept: 'application/json',
                        'X-CSRF-TOKEN': form.querySelector('[name="_token"]').value,
                    },
                    signal: controller.signal,
                });
                if (this.leaving) return;
                if (!response.ok) {
                    this.failed = true;
                    this.message = response.status === 401 || response.status === 419
                        ? 'Sesi berakhir. Muat ulang halaman dan masuk kembali.'
                        : response.status === 403
                            ? 'Anda tidak memiliki akses untuk memeriksa transaksi ini.'
                            : response.status === 429
                                ? 'Terlalu banyak pemeriksaan. Tunggu sebentar sebelum mencoba lagi.'
                                : 'Status belum dapat diperiksa. Klik Sinkronkan status untuk mencoba lagi.';
                    return;
                }
                const result = await response.json();
                if (this.leaving) return;
                const statuses = ['pending', 'paid', 'failed', 'expired', 'cancelled', 'review_required'];
                if (!statuses.includes(result.status)) throw new Error('Invalid status response');
                if (result.status !== 'pending') {
                    window.location.reload();
                    return;
                }
                this.message = result.status === 'pending'
                    ? 'Midtrans masih mencatat pembayaran menunggu. Jika baru selesai membayar, tunggu sebentar lalu sinkronkan kembali.'
                    : 'Status pembayaran sudah diperiksa melalui server.';
            } catch {
                if (this.leaving) return;
                this.failed = true;
                this.message = 'Koneksi pemeriksaan terputus. Klik Sinkronkan status untuk mencoba lagi.';
            } finally {
                clearTimeout(timeout);
                this.busy = false;
                this.requestController = null;
            }
        },
    };
}
