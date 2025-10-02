<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class NitColombia implements ValidationRule
{
    /**
     * Valida NIT con o sin guion. Si trae guion, valida DV exacto.
     * Si no trae guion, se calcula DV y se acepta como válido.
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $raw = strtoupper(trim((string) $value));
        $sinGuion = preg_replace('/[^0-9]/', '', $raw);

        if ($sinGuion === '' || !preg_match('/^\d{9,10}$/', $sinGuion)) {
            $fail('El :attribute debe tener 9 o 10 dígitos (NIT sin caracteres especiales).');
            return;
        }

        // Detectar si la entrada venía con guion y DV explícito
        $dvExplicito = null;
        if (str_contains($raw, '-')) {
            // formato esperado #######...-D
            if (!preg_match('/^(\d{5,15})-(\d)$/', $raw, $m)) {
                $fail('El :attribute no cumple el formato NIT con guion (#########-#).');
                return;
            }
            $numero = $m[1];
            $dvExplicito = (int)$m[2];
        } else {
            // sin guion: asumimos último dígito puede ser parte del número completo
            $numero = $sinGuion;
        }

        // Cálculo DV (módulo 11) con pesos estándar DIAN
        // Pesos: 71, 67, 59, 53, 47, 43, 41, 37, 29, 23, 19, 17, 13, 7, 3
        // Usamos los últimos N pesos según longitud del número
        $pesos = [71,67,59,53,47,43,41,37,29,23,19,17,13,7,3];
        $digitos = array_map('intval', str_split($numero));
        $offset = count($pesos) - count($digitos);
        if ($offset < 0) {
            $fail('El :attribute tiene demasiados dígitos.');
            return;
        }

        $suma = 0;
        foreach ($digitos as $i => $d) {
            $suma += $d * $pesos[$i + $offset];
        }
        $dvCalc = $suma % 11;
        if ($dvCalc > 1) {
            $dvCalc = 11 - $dvCalc;
        }

        if ($dvExplicito !== null && $dvExplicito !== $dvCalc) {
            $fail('El dígito de verificación del :attribute no es válido.');
            return;
        }
        // Pasa validación.
    }
}
