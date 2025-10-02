<?php

namespace App\Rules;

use App\Models\Setting;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class MatchesAllowedNit implements ValidationRule
{
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $input = preg_replace('/[^0-9]/', '', strtoupper((string) $value));
        if ($input === '') {
            $fail('El :attribute es requerido.');
            return;
        }

        $record = Setting::query()->where('key', 'allowed_nit')->first();
        if (!$record || !is_string($record->value) || $record->value === '') {
            $fail('No hay NIT permitido configurado en el sistema.');
            return;
        }

        $allowed = preg_replace('/[^0-9]/', '', strtoupper((string) $record->value));
        if ($allowed === '') {
            $fail('El NIT permitido configurado es inválido.');
            return;
        }

        if ($input !== $allowed) {
            $fail('El :attribute no coincide con el NIT autorizado.');
        }
    }
}

