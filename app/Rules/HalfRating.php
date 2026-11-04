<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class HalfRating implements ValidationRule
{
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (! is_scalar($value) || ! preg_match('/^(?:[1-4](?:\.(?:0+|50*))?|5(?:\.0+)?)$/D', (string) $value)) {
            $fail('Rating harus 1 sampai 5 dengan kelipatan 0,5.');
        }
    }
}
