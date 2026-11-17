@props(['cities', 'selected' => null])

@php
    $selectedValue = (string) old('city_id', $selected);
    $groups = $cities->groupBy(fn ($city) => $city->province?->name ?? 'Wilayah lainnya');
@endphp

@foreach($groups as $province => $group)
    <optgroup label="{{ $province }}">
        @foreach($group as $city)
            <option value="{{ $city->id }}" @selected($selectedValue === (string) $city->id)>{{ $city->name }}</option>
        @endforeach
    </optgroup>
@endforeach