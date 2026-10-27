export default (config) => ({
    revision: config.revision,
    busy: false,
    finished: false,
    message: 'Jawaban terakhir dimuat dari server.',
    timeLeft: '',
    queue: Promise.resolve(),
    offset: Date.parse(config.serverNow) - Date.now(),
    init() {
        this.tick();
        this.timer = setInterval(() => this.tick(), 1000);
        this.autosave = setInterval(() => this.save(), 15000);
    },
    destroy() {
        clearInterval(this.timer);
        clearInterval(this.autosave);
    },
    tick() {
        const seconds = Math.max(0, Math.ceil((Date.parse(config.expiresAt) - Date.now() - this.offset) / 1000));
        this.timeLeft = `${Math.floor(seconds / 60)}:${String(seconds % 60).padStart(2, '0')}`;
        if (!seconds && !this.finished && !this.busy) this.send(true);
    },
    payload() {
        const answers = {};
        this.$refs.answers.querySelectorAll('input[type=radio]:checked').forEach(input => {
            answers[input.name.match(/\[(\d+)\]/)[1]] = Number(input.value);
        });
        return { revision: this.revision, answers };
    },
    save() { return this.send(false); },
    submit() {
        if (confirm('Kirim assessment? Jawaban tidak dapat diubah setelah dikirim.')) this.send(true);
    },
    send(finalize) {
        this.queue = this.queue.then(async () => {
            if (this.finished) return;
            this.busy = true;
            this.message = finalize ? 'Mengirim assessment…' : 'Menyimpan jawaban…';
            try {
                const response = await fetch(finalize ? config.submitUrl : config.saveUrl, {
                    method: finalize ? 'POST' : 'PUT', credentials: 'same-origin',
                    headers: { 'Content-Type': 'application/json', Accept: 'application/json',
                        'X-CSRF-TOKEN': this.$refs.answers.querySelector('[name=_token]').value },
                    body: JSON.stringify(this.payload()),
                });
                const result = await response.json();
                if (!response.ok) {
                    this.message = response.status === 422
                        ? (Object.values(result.errors ?? {}).flat()[0] ?? 'Jawaban belum tersimpan. Periksa isian.')
                        : 'Jawaban belum tersimpan. Periksa koneksi dan sesi login, lalu coba simpan kembali.';
                    return;
                }
                this.revision = result.revision;
                this.offset = Date.parse(result.server_now) - Date.now();
                this.finished = result.status !== 'in_progress';
                this.message = this.finished ? 'Pengerjaan assessment telah berakhir.' : 'Jawaban tersimpan.';
                if (this.finished) window.location.assign(config.returnUrl);
            } catch {
                this.message = 'Gagal menyimpan. Jawaban di halaman ini belum tersimpan. Periksa koneksi lalu coba kembali.';
            } finally { this.busy = false; }
        });
        return this.queue;
    },
});
