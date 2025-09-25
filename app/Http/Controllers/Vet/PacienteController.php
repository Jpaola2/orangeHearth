<?php

namespace App\Http\Controllers\Vet;

use App\Http\Controllers\Controller;
use App\Models\Mascota;
use App\Models\Cita;
use App\Models\Tutor;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PacienteController extends Controller
{
    public function index(Request $request)
    {
        $q = trim((string) $request->query('q'));

        $mascotas = Mascota::with('tutor')
            ->when($q !== '', function ($query) use ($q) {
                $query->where('nom_masc', 'like', "%{$q}%")
                    ->orWhere('espe_masc', 'like', "%{$q}%")
                    ->orWhereHas('tutor', function ($t) use ($q) {
                        $t->where('nomb_tutor', 'like', "%{$q}%")
                          ->orWhere('apell_tutor', 'like', "%{$q}%")
                          ->orWhere('correo_tutor', 'like', "%{$q}%");
                    });
            })
            ->orderBy('nom_masc')
            ->get();

        if ($request->expectsJson()) {
            return response()->json([
                'pets' => $mascotas->map(function (Mascota $m) {
                    $tutorNombre = $m->tutor ? trim(($m->tutor->nomb_tutor ?? '').' '.($m->tutor->apell_tutor ?? '')) : 'Sin tutor';
                    return [
                        'id' => $m->id_masc,
                        'nombre' => $m->nom_masc,
                        'especie' => $m->espe_masc,
                        'genero' => $m->gene_masc,
                        'tutor' => $tutorNombre,
                        'email_tutor' => $m->tutor->correo_tutor ?? null,
                    ];
                }),
            ]);
        }

        return view('vet.pacientes.index', [
            'mascotas' => $mascotas,
            'q' => $q,
        ]);
    }

    public function show(Mascota $mascota)
    {
        $mascota->load('tutor');
        $citas = Cita::where('id_masc', $mascota->id_masc)
            ->orderByDesc('fech_cons')
            ->limit(10)
            ->get();

        return view('vet.pacientes.show', [
            'mascota' => $mascota,
            'citas' => $citas,
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'nom_masc' => ['required','string','max:120'],
            'espe_masc' => ['nullable','string','max:50'],
            'gene_masc' => ['nullable','string','max:20'],
            'tutor_id' => ['required','integer','exists:tutor,id_tutor'],
        ], [
            'tutor_id.required' => 'Debes seleccionar un tutor antes de guardar la mascota.',
        ]);

        $tutor = Tutor::findOrFail($data['tutor_id']);

        $mascota = Mascota::create([
            'nom_masc' => $data['nom_masc'],
            'espe_masc' => $data['espe_masc'] ?? null,
            'gene_masc' => $data['gene_masc'] ?? null,
            'id_tutor' => $tutor->id_tutor,
        ]);

        return response()->json([
            'message' => 'Paciente creado correctamente.',
            'pet' => [
                'id' => $mascota->id_masc,
                'nombre' => $mascota->nom_masc,
                'especie' => $mascota->espe_masc,
                'genero' => $mascota->gene_masc,
                'tutor' => trim(($tutor->nomb_tutor ?? '').' '.($tutor->apell_tutor ?? '')),
                'email_tutor' => $tutor->correo_tutor,
            ],
        ], 201);
    }

    public function searchTutor(Request $request): JsonResponse
    {
        $q = trim((string) $request->query('q'));
        if ($q === '') {
            return response()->json(['results' => []]);
        }

        $items = Tutor::query()
            ->where('correo_tutor', 'like', "%{$q}%")
            ->orWhere('nomb_tutor', 'like', "%{$q}%")
            ->orWhere('apell_tutor', 'like', "%{$q}%")
            ->orderBy('nomb_tutor')
            ->limit(10)
            ->get()
            ->map(fn (Tutor $t) => [
                'id' => $t->id_tutor,
                'nombre' => trim(($t->nomb_tutor ?? '').' '.($t->apell_tutor ?? '')),
                'email' => $t->correo_tutor,
                'telefono' => $t->tel_tutor,
            ]);

        return response()->json(['results' => $items]);
    }
}
