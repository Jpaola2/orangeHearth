<?php
declare(strict_types=1);

namespace App\Http\Controllers\Tutor;

use App\Http\Controllers\Controller;
use App\Models\Cita;
use App\Models\Mascota;
use App\Models\Medico;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

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

        $mascotas = Mascota::where('id_tutor', $tutorId)
            ->orderBy('nom_masc')
            ->get(['id_masc','nom_masc']);

        $mascotaId = (int) $request->query('mascota', 0);

        // Mostrar TODAS las que el tutor podría querer ver/modificar
        $estados = ['pendiente','confirmada','completada','cancelada'];

        $query = Cita::with(['mascota','medico'])
            ->where('id_tutor', $tutorId)
            ->whereIn('estado', $estados);

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

        $mascotas = Mascota::where('id_tutor', $tutorId)
            ->orderBy('nom_masc')
            ->get(['id_masc','nom_masc']);

        return view('tutor.citas.create', [
            'mascotas' => $mascotas,
            'prefMascota' => (int) request()->query('mascota', 0),
        ]);
    }

    public function store(Request $request)
    {
        $tutorId = $this->tutorId();

        $data = $request->validate([
            'fecha'       => ['required','date','date_format:Y-m-d'],
            'hora'        => ['required','date_format:H:i'],
            'motivo'      => ['required','string','max:200'],
            'mascota_id'  => ['required','integer','exists:mascota,id_masc'],
            'medico_id'   => ['required','integer','exists:medico_veterinario,id_mv'],
        ]);

        // Validar que la mascota pertenezca al tutor
        abort_unless(
            Mascota::where('id_masc',$data['mascota_id'])->where('id_tutor',$tutorId)->exists(),
            403,
            'La mascota no pertenece a este tutor.'
        );

        $datetime = Carbon::createFromFormat('Y-m-d H:i', "{$data['fecha']} {$data['hora']}");

        // Conflicto de horario con el médico (misma fecha y hora)
        $ocupado = Cita::where('id_mv', $data['medico_id'])
            ->whereDate('fech_cons', $datetime->toDateString())
            ->when(
                Schema::hasColumn((new Cita)->getTable(),'hora_cons'),
                function ($q) use ($datetime) {
                    $slotStart = (clone $datetime)->second(0);
                    $slotStart->minute($slotStart->minute < 30 ? 0 : 30);
                    $slotEnd = (clone $slotStart)->addMinutes(30);
                    $q->whereTime('hora_cons', '>=', $slotStart->format('H:i:s'))
                      ->whereTime('hora_cons', '<',  $slotEnd->format('H:i:s'));
                }
            )
            ->exists();

        if ($ocupado) {
            return back()->withErrors(['medico_id' => 'El médico ya tiene una cita en esa fecha y hora.'])->withInput();
        }

        $cita = new Cita();
        $cita->fech_cons  = $datetime->toDateString();
        if (Schema::hasColumn($cita->getTable(),'hora_cons')) {
            $cita->hora_cons = $datetime->format('H:i:s');
        }
        $cita->motiv_cons = $data['motivo'];
        $cita->diag_cons  = '';
        $cita->trata_cons = '';
        $cita->estado     = 'confirmada';
        $cita->id_tutor   = $tutorId;
        $cita->id_masc    = $data['mascota_id'];
        $cita->id_mv      = $data['medico_id'];
        $cita->save();

        return redirect()->route('tutor.citas.index')->with('ok','Cita creada');
    }

    // AJAX: devuelve médicos disponibles para una fecha
    public function availableVets(Request $request)
    {
        $validated = $request->validate([
            'fecha' => ['required','date','date_format:Y-m-d'],
            'hora'  => ['nullable','date_format:H:i'],
        ]);
        $fecha = $validated['fecha'];
        $hora  = $validated['hora'] ?? null;

        $ocupadosQuery = Cita::whereDate('fech_cons', $fecha);
        if ($hora && Schema::hasColumn((new Cita)->getTable(),'hora_cons')) {
            $h = Carbon::createFromFormat('H:i', $hora);
            $slotStart = (clone $h)->second(0);
            $slotStart->minute($slotStart->minute < 30 ? 0 : 30);
            $slotEnd = (clone $slotStart)->addMinutes(30);
            $ocupadosQuery->whereTime('hora_cons', '>=', $slotStart->format('H:i:s'))
                          ->whereTime('hora_cons', '<',  $slotEnd->format('H:i:s'));
        }
        $ocupados = $ocupadosQuery->pluck('id_mv')->unique()->all();

        $vets = Medico::query()
            ->when(!empty($ocupados), fn($q)=>$q->whereNotIn('id_mv', $ocupados))
            ->orderBy('nombre_mv')
            ->get(['id_mv','nombre_mv','apell_mv','especialidad']);

        return response()->json([
            'veterinarios' => $vets->map(fn($m)=>[
                'id' => $m->id_mv,
                'nombre' => trim(($m->nombre_mv ?? '').' '.($m->apell_mv ?? '')),
                'especialidad' => $m->especialidad,
            ])->values(),
        ]);
    }

    public function show($id)
    {
        $tutorId = $this->tutorId();
        $cita = Cita::with(['mascota','medico'])
            ->where('id_tutor',$tutorId)
            ->findOrFail($id);

        return view('tutor.citas.show', ['cita'=>$cita]);
    }

    public function edit($id)
    {
        $tutorId = $this->tutorId();
        $cita = Cita::with(['mascota','medico'])
            ->where('id_tutor',$tutorId)
            ->findOrFail($id);

        // Mascotas del tutor para cambiarla si hace falta
        $mascotas = Mascota::where('id_tutor',$tutorId)->orderBy('nom_masc')->get(['id_masc','nom_masc']);

        return view('tutor.citas.edit', [
            'cita' => $cita,
            'mascotas' => $mascotas,
        ]);
    }

    public function update(Request $request, $id)
    {
        $tutorId = $this->tutorId();

        $data = $request->validate([
            'fecha'       => ['required','date','date_format:Y-m-d'],
            'hora'        => ['required','date_format:H:i'],
            'motivo'      => ['required','string','max:200'],
            'mascota_id'  => ['required','integer','exists:mascota,id_masc'],
            'medico_id'   => ['required','integer','exists:medico_veterinario,id_mv'],
        ]);

        // Cita del tutor
        $cita = Cita::where('id_tutor',$tutorId)->findOrFail($id);

        // Pertenencia mascota→tutor
        abort_unless(
            Mascota::where('id_masc',$data['mascota_id'])->where('id_tutor',$tutorId)->exists(),
            403,
            'La mascota no pertenece a este tutor.'
        );

        $datetime = Carbon::createFromFormat('Y-m-d H:i', "{$data['fecha']} {$data['hora']}");

        // Conflicto de horario (excluyendo esta misma cita) en bloque de 30 minutos
        $conflicto = Cita::where('id_mv', $data['medico_id'])
            ->where('id_cita_medi', '!=', $cita->id_cita_medi)
            ->whereDate('fech_cons', $datetime->toDateString())
            ->when(
                Schema::hasColumn((new Cita)->getTable(),'hora_cons'),
                function ($q) use ($datetime) {
                    $slotStart = (clone $datetime)->second(0);
                    $slotStart->minute($slotStart->minute < 30 ? 0 : 30);
                    $slotEnd = (clone $slotStart)->addMinutes(30);
                    $q->whereTime('hora_cons', '>=', $slotStart->format('H:i:s'))
                      ->whereTime('hora_cons', '<',  $slotEnd->format('H:i:s'));
                }
            )
            ->exists();

        if ($conflicto) {
            if ($request->expectsJson()) {
                return response()->json(['message' => 'El médico ya tiene una cita dentro de ese bloque de 30 minutos.'], 422);
            }
            return back()->withErrors(['medico_id' => 'El médico ya tiene una cita dentro de ese bloque de 30 minutos.'])->withInput();
        }

        // Actualizar
        $cita->fech_cons  = $datetime->toDateString();
        if (Schema::hasColumn($cita->getTable(),'hora_cons')) {
            $cita->hora_cons = $datetime->format('H:i:s');
        }
        $cita->motiv_cons = $data['motivo'];
        $cita->id_masc    = $data['mascota_id'];
        $cita->id_mv      = $data['medico_id'];
        $cita->estado     = 'confirmada';
        $cita->save();

        if ($request->expectsJson()) {
            return response()->json(['ok' => true, 'reprogramada_id' => $cita->id_cita_medi]);
        }
        return redirect()->route('tutor.citas.index')->with('ok', 'Cita actualizada')->with('reprogramada_id', $cita->id_cita_medi);
    }

    public function destroy($id)
    {
        $tutorId = $this->tutorId();
        $cita = Cita::where('id_tutor',$tutorId)->findOrFail($id);
        $cita->estado = 'cancelada';
        $cita->save();

        return redirect()->route('tutor.citas.index')->with('ok', 'Cita cancelada');
    }

    // Cambiar estado: confirmar o cancelar (no reprograma)
    public function updateStatus(Request $request, $id)
    {
        $tutorId = $this->tutorId();
        $cita = Cita::where('id_tutor',$tutorId)->findOrFail($id);

        $data = $request->validate([
            'estado' => ['required','in:confirmada,cancelada,pendiente'],
        ]);

        $cita->estado = $data['estado'];
        $cita->save();

        if ($request->expectsJson()) {
            return response()->json(['ok' => true, 'estado' => $cita->estado]);
        }
        return back()->with('ok', 'Estado actualizado');
    }
}






