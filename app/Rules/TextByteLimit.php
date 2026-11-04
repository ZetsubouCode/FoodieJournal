<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class TextByteLimit implements ValidationRule
{
    // MySQL TEXT limits bytes, whereas Laravel's string max rule counts characters.
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (is_string($value) && strlen($value) > 65535) {
            $fail('Catatan terlalu panjang dalam UTF-8. Perpendek isi sebelum menyimpan (maksimum 65.535 byte).');
        }
    }
}
