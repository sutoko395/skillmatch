@props(['value' => '', 'required' => false])
<x-form-field
    name="phone"
    label="Nomor telepon"
    type="tel"
    :value="$value"
    :required="$required"
    inputmode="tel"
    autocomplete="tel"
    pattern="\+?[0-9]{8,15}"
    minlength="8"
    maxlength="16"
    :title="\App\Rules\PhoneNumber::MESSAGE"
    helper="8–15 digit, boleh diawali +. Contoh: 081234567890 atau +6281234567890."
    x-data
    x-on:beforeinput="if ($event.data && /[^0-9+]/.test($event.data)) $event.preventDefault()"
    x-on:paste="if (/[^0-9+]/.test($event.clipboardData.getData('text'))) $event.preventDefault()"
    {{ $attributes }}
/>
