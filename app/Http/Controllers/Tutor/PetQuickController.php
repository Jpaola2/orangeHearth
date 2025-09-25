<?php

namespace App\Http\Controllers\Tutor;

use App\Http\Controllers\Controller;
use App\Models\Mascota;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PetQuickController extends Controller
{
    private function tutorId(): int
    {
        $email = auth()->user()->email ?? null;
        $id = DB::table('tutor')->where('correo_tutor', $email)->value('id_tutor');
        abort_unless($id, 403, 'No se encontró el tutor para este usuario.');
        return (int) $id;
    }

    public function store(Request $r)
    {
        $r->validate([
            'nom_masc'  => 'required|string|max:100',
            'espe_masc' => 'required|in:canino,felino,otro',
            'gene_masc' => 'required|in:macho,hembra',
            'edad_masc' => 'nullable|integer|min:0',
            'unidad_edad' => 'nullable|string|in:dias,meses,años',
        ]);

        $pet = Mascota::create([
            'nom_masc' => $r->nom_masc,
            'espe_masc' => $r->espe_masc,
            'gene_masc' => $r->gene_masc,
            'edad_masc' => $r->edad_masc,
            'unidad_edad' => $r->unidad_edad,
            'id_tutor' => $this->tutorId(),
        ]);

        return response()->json([
            'message' => 'Mascota creada',
            'pet' => [
                'id' => $pet->id_masc,
                'nombre' => $pet->nom_masc,
                'especie' => $pet->espe_masc,
                'genero' => $pet->gene_masc,
                'edad' => $pet->edad_masc,
                'unidad_edad' => $pet->unidad_edad,
            ],
        ], 201);
    }
}

