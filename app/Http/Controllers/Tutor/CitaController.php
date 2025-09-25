<?php

namespace App\Http\Controllers\Tutor;

use App\Http\Controllers\Controller;
use App\Models\Cita;
use App\Models\Mascota;
use App\Models\Medico;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CitaController extends Controller
{
    private function tutorId(): int
    {
        $email = auth()->user()->email ?? null;
        $id = DB::table('tutor')->where('correo_tutor', $email)->value('id_tutor');
        abort_unless($id, 403, 'No se encontró el tutor para este usuario.');
        return (int) $id;
    }

    public function index(Request $request)
    {
        $tutorId = $this->tutorId();
        $mascotas = Mascota::where('id_tutor', $tutorId)->orderBy('nom_masc')->get(['id_masc','nom_masc']);

        $mascotaId = (int) $request->query('mascota', 0);
        $query = Cita::with(['mascota','medico'])
            ->where('id_tutor', $tutorId)
            ->whereIn('estado', ['completada','cancelada']);

        if ($mascotaId) {
            $query->where('id_masc', $mascotaId);
        }

        $citas = $query->orderByDesc('fech_cons')->get();

        return view('tutor.citas.index', [
            'citas' => $citas,
            'mascotas' => $mascotas,
            'mascotaSeleccionada' => $mascotaId,
        ]);
    }

    public function create()
    {
        $tutorId = $this->tutorId();
        $mascotas = Mascota::where('id_tutor', $tutorId)->orderBy('nom_masc')->get(['id_masc','nom_masc']);
        return view('tutor.citas.create', [
            'mascotas' => $mascotas,
            'prefMascota' => request()->query('mascota') ? (int) request()->query('mascota') : 0,
        ]);
    }

    public function store(Request $request)
    {
        $tutorId = $this->tutorId();
        $data = $request->validate([
            'fecha' => ['required','date'],
            'motivo' => ['required','string','max:200'],
            'mascota_id' => ['required','integer','exists:mascota,id_masc'],
            'medico_id' => ['required','integer','exists:medico_veterinario,id_mv'],
        ]);

        // Validar pertenencia mascota→tutor
        abort_unless(Mascota::where('id_masc',$data['mascota_id'])->where('id_tutor',$tutorId)->exists(), 403);

        // Validar disponibilidad del médico (no otra cita ese día)
        $ocupado = \App\Models\Cita::where('id_mv', $data['medico_id'])
            ->whereDate('fech_cons', $data['fecha'])
            ->exists();
        if ($ocupado) {
            return back()->withErrors(['medico_id' => 'El médico ya tiene citas en esa fecha.'])->withInput();
        }

        $cita = new \App\Models\Cita();
        $cita->fech_cons = $data['fecha'];
        $cita->motiv_cons = $data['motivo'];
        $cita->diag_cons = '';
        $cita->trata_cons = '';
        $cita->estado = 'pendiente';
        $cita->id_tutor = $tutorId;
        $cita->id_masc = $data['mascota_id'];
        $cita->id_mv = $data['medico_id'];
        $cita->save();

        return redirect()->route('tutor.citas.index')->with('ok','Cita creada');
    }

    // AJAX: devuelve médicos disponibles para una fecha
    public function availableVets(Request $request)
    {
        $request->validate(['fecha' => ['required','date']]);
        $fecha = $request->query('fecha');

        $ocupados = Cita::whereDate('fech_cons', $fecha)->pluck('id_mv')->unique()->all();
        $vets = Medico::query()
            ->when(!empty($ocupados), fn($q)=>$q->whereNotIn('id_mv', $ocupados))
            ->orderBy('nombre_mv')
            ->get(['id_mv','nombre_mv','apell_mv','especialidad']);

        return response()->json([
            'veterinarios' => $vets->map(fn($m)=>[
                'id' => $m->id_mv,
                'nombre' => trim(($m->nombre_mv ?? '').' '.($m->apell_mv ?? '')),
                'especialidad' => $m->especialidad,
            ]),
        ]);
    }

    public function show($id)
    {
        return response("Detalle de cita {$id}", 200);
    }

    public function edit($id)
    {
        return response("Editar cita {$id}", 200);
    }

    public function update(Request $request, $id)
    {
        return redirect()->route('tutor.citas.index')->with('ok', 'Cita actualizada');
    }

    public function destroy($id)
    {
        return redirect()->route('tutor.citas.index')->with('ok', 'Cita eliminada');
    }
}
