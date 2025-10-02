<?php

namespace App\Http\Controllers\Vet;

use App\Http\Controllers\Controller;
use App\Models\Tutor;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class TutorController extends Controller
{
    public function index(Request $request)
    {
        $q = trim((string) $request->query('q'));

        $tutores = Tutor::withCount('mascotas')
            ->when($q !== '', function ($query) use ($q) {
                $needle = mb_strtolower($q);
                $query->where(function ($q2) use ($needle) {
                    $q2->whereRaw('LOWER(correo_tutor) LIKE ?', ["%{$needle}%"]) // correo
                       ->orWhere('ced_tutor', 'like', "%{$needle}%")        // cédula
                       ->orWhereRaw('LOWER(nomb_tutor) LIKE ?', ["%{$needle}%"]) // nombre
                       ->orWhereRaw('LOWER(apell_tutor) LIKE ?', ["%{$needle}%"]) // apellido
                       // nombre completo: "nombre apellido"
                       ->orWhereRaw("LOWER(CONCAT(COALESCE(nomb_tutor,''),' ',COALESCE(apell_tutor,''))) LIKE ?", ["%{$needle}%"]);
                });
            })
            ->orderBy('nomb_tutor')
            ->get();

        if ($request->expectsJson()) {
            return response()->json([
                // Normaliza al formato esperado por las vistas (results[])
                'results' => $tutores->map(fn (Tutor $t) => [
                    'id' => $t->id_tutor,
                    'cedula' => $t->ced_tutor,
                    'nombre' => trim(($t->nomb_tutor ?? '').' '.($t->apell_tutor ?? '')),
                    'telefono' => $t->tel_tutor,
                    'email' => $t->correo_tutor,
                    'direccion' => $t->direc_tutor,
                    'mascotas' => (int) $t->mascotas_count,
                ])->values(),
            ]);
        }

        return view('vet.tutores.index', [
            'tutores' => $tutores,
            'q' => $q,
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'ced_tutor' => ['nullable','string','max:30','unique:tutor,ced_tutor'],
            'nomb_tutor' => ['required','string','max:120'],
            'apell_tutor' => ['nullable','string','max:120'],
            'tel_tutor' => ['nullable','string','max:60'],
            'correo_tutor' => ['required','email','max:150','unique:tutor,correo_tutor'],
            'direc_tutor' => ['nullable','string','max:180'],
        ]);

        $tutor = Tutor::create($data);

        return response()->json([
            'message' => 'Tutor creado correctamente.',
            'owner' => [
                'id' => $tutor->id_tutor,
                'nombre' => trim(($tutor->nomb_tutor ?? '').' '.($tutor->apell_tutor ?? '')),
                'correo' => $tutor->correo_tutor,
            ],
        ], 201);
    }
}
