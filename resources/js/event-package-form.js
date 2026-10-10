export default function eventPackageForm(packages, selected, opens, deadline) {
    return {
        packages,
        packageId: String(selected ?? ''),
        opens: opens ?? '',
        deadline: deadline ?? '',
        busy: false,
        get selectedPackage() {
            return this.packages.find(item => String(item.id) === this.packageId);
        },
        get durationSeconds() {
            // datetime-local input represents WIB, independently of the browser timezone.
            const start = Date.parse(`${this.opens}+07:00`);
            const end = Date.parse(`${this.deadline}+07:00`);
            return Number.isFinite(start) && Number.isFinite(end) && end > start ? (end - start) / 1000 : 0;
        },
        get exceedsLimit() {
            return !!this.selectedPackage && this.durationSeconds > this.selectedPackage.max_registration_days * 86400;
        },
        get warning() {
            if (!this.exceedsLimit) return '';
            const plan = this.selectedPackage;
            return `Durasi pendaftaran melebihi batas paket ${plan.name} (${plan.max_registration_days} hari). Pilih paket dengan durasi lebih besar atau pendekkan jadwal pendaftaran.`;
        },
        submit(event) {
            if (!this.selectedPackage || this.exceedsLimit) {
                event.preventDefault();
                return;
            }
            this.busy = true;
        },
    };
}
