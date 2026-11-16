<form method="POST" action="{{ route('volunteer.assessments.start', $application) }}" class="vac-actions">
    @csrf
    <input type="hidden" name="confirmed" value="1" />
    <button type="submit" class="vac-start">Mulai sekarang</button>
    <a href="{{ route('volunteer.applications.show', $application) }}" class="volunteer-action volunteer-action--danger vac-back">Batal</a>
</form>
