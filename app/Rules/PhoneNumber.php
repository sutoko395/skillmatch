<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class PhoneNumber implements ValidationRule
{
    public const MESSAGE = 'Nomor telepon harus berisi 8–15 digit, boleh diawali +, tanpa huruf, spasi, atau tanda baca lain.';

    public static function isValid(mixed $value): bool
    {
        return is_string($value) && preg_match('/\A\+?[0-9]{8,15}\z/', $value) === 1;
    }

    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (! self::isValid($value)) {
            $fail(self::MESSAGE);
        }
    }
}
