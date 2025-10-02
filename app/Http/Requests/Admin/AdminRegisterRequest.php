<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use App\Rules\MatchesAllowedNit;

class AdminRegisterRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $adminId  = optional($this->user())->id;
        $isUpdate = $this->isMethod('PUT') || $this->isMethod('PATCH');

        return [
            'nombre_completo' => ['required','string','min:3','max:120'],
            'email' => [
                'required','string','email:rfc,dns','max:160',
                Rule::unique('admins','email')->ignore($adminId),
            ],
            'telefono' => ['nullable','string','max:20','regex:/^\+?\d{7,20}$/'],
            'cedula' => [
                'required','regex:/^\d{6,10}$/',
                Rule::unique('admins','cedula')->ignore($adminId),
            ],
            'empresa_nombre' => ['required','string','min:2','max:160'],
            'nit' => [
                'required','string','max:20',
                new MatchesAllowedNit,
            ],
            'password' => [
                $isUpdate ? 'nullable' : 'required',
                'string','min:8','max:72','confirmed',
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'cedula.regex' => 'La cédula debe contener solo dígitos (6 a 10).',
            'telefono.regex' => 'El teléfono debe contener solo dígitos y opcionalmente un prefijo +.',
            'password.confirmed' => 'La confirmación de contraseña no coincide.',
        ];
    }
}
