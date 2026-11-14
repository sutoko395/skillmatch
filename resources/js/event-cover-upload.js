export default (existing = null, removed = false) => ({
    preview: removed ? null : existing,
    removed,
    error: '',
    saving: false,
    objectUrl: null,
    choose(event) {
        const file = event.target.files[0];
        this.error = '';
        if (!file) return;
        if (!['image/jpeg', 'image/png', 'image/webp'].includes(file.type) || file.size > 5 * 1024 * 1024) {
            this.error = file.size > 5 * 1024 * 1024 ? 'Ukuran foto maksimal 5 MB.' : 'Foto harus berformat JPG, PNG, atau WebP.';
            event.target.value = '';
            this.release();
            this.preview = this.removed ? null : existing;
            return;
        }
        this.release();
        this.objectUrl = URL.createObjectURL(file);
        this.preview = this.objectUrl;
        this.removed = false;
    },
    cancel() {
        this.release();
        this.preview = existing;
        this.removed = false;
        this.error = '';
        this.$refs.file.value = '';
    },
    remove() {
        this.release();
        this.preview = null;
        this.removed = true;
        this.error = '';
        this.$refs.file.value = '';
    },
    release() {
        if (this.objectUrl) URL.revokeObjectURL(this.objectUrl);
        this.objectUrl = null;
    },
    destroy() { this.release(); },
});
