@props(['variant' => 'primary'])
@switch($variant)
    @case('danger')<x-danger-button {{ $attributes }}>{{ $slot }}</x-danger-button>@break
    @case('secondary')<x-secondary-button {{ $attributes }}>{{ $slot }}</x-secondary-button>@break
    @default <x-primary-button {{ $attributes }}>{{ $slot }}</x-primary-button>
@endswitch
