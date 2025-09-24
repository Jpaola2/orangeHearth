<?php

namespace App\Http\Controllers\Tutor;

use App\Http\Controllers\Controller;
use App\Models\Mascota;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class MascotaController extends Controller
{
    private function tutorId(): int
    {
        $email = auth()->user()->email ?? null;
        $id = DB::table('tutor')->where('correo_tutor', $email)->value('id_tutor');
        abort_unless($id, 403, 'No se encontró el tutor para este usuario.');
        return (int) $id;
    }

    public function index()
    {
        $mascotas = Mascota::where('id_tutor', $this->tutorId())
            ->orderBy('nom_masc')->get();
        return view('tutor.mascotas.index', compact('mascotas'));
    }

    public function create()
    {
        return view('tutor.mascotas.create');
    }

    public function store(Request $r)
    {
        $r->validate([
            'nom_masc'  => 'required|string|max:100',
            'espe_masc' => 'required|in:canino,felino,otro',
            'gene_masc' => 'required|in:macho,hembra',
            'edad_masc' => 'nullable|integer|min:1',
            'unidad_edad' => 'nullable|string|in:dias,meses,años',
        ]);

        Mascota::create([
            'nom_masc'  => $r->nom_masc,
            'espe_masc' => $r->espe_masc,
            'gene_masc' => $r->gene_masc,
            'edad_masc' => $r->edad_masc,
            'unidad_edad' => $r->unidad_edad,
            'id_tutor'  => $this->tutorId(),
        ]);

        return redirect()->route('tutor.mascotas.index')->with('ok', 'Mascota creada');
    }

    public function edit(Mascota $mascota)
    {
        abort_if($mascota->id_tutor !== $this->tutorId(), 403);
        return view('tutor.mascotas.edit', compact('mascota'));
    }

    public function update(Request $r, Mascota $mascota)
    {
        abort_if($mascota->id_tutor !== $this->tutorId(), 403);

        $r->validate([
            'nom_masc'  => 'required|string|max:100',
            'espe_masc' => 'required|in:canino,felino,otro',
            'gene_masc' => 'required|in:macho,hembra',
            'edad_masc' => 'nullable|integer|min:1',
            'unidad_edad' => 'nullable|string|in:dias,meses,años',
        ]);

        $mascota->update($r->only('nom_masc', 'espe_masc', 'gene_masc', 'edad_masc', 'unidad_edad'));
        return redirect()->route('tutor.mascotas.index')->with('ok', 'Mascota actualizada');
    }

    public function destroy(Mascota $mascota)
    {
        abort_if($mascota->id_tutor !== $this->tutorId(), 403);
        $mascota->delete();
        return back()->with('ok', 'Mascota eliminada');
    }
}
