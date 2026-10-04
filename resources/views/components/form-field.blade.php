@props(['name', 'label', 'value' => '', 'type' => 'text', 'required' => false, 'helper' => null])
<div>
    <x-input-label :for="$name" :value="$label.($required ? ' *' : '')" />
    @if($slot->isNotEmpty()){{ $slot }}@else
        <x-text-input :id="$name" :name="$name" :type="$type" :value="old($name, $value)" :required="$required" class="mt-2 block w-full" {{ $attributes }} />
    @endif
    @if($helper)<p class="mt-1 text-sm text-slate-600">{{ $helper }}</p>@endif
    <x-input-error :messages="$errors->get($name)" class="mt-1" />
</div>
