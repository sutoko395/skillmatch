<form method="POST" action="{{ route('volunteer.assessments.start', $application) }}" class="vac-actions">
    @csrf
    <input type="hidden" name="confirmed" value="1" />
    <a href="{{ route('volunteer.applications.show', $application) }}" class="vac-back">Kembali ke lamaran</a>
    <button type="submit" class="vac-start">Mulai sekarang</button>
</form>
