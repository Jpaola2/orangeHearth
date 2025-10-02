<?php

namespace App\Http\Controllers\Vet;

use App\Http\Controllers\Controller;
use App\Models\Cita;
use App\Models\Medico;
use App\Models\Tutor;
use App\Models\Mascota;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Symfony\Component\HttpFoundation\StreamedResponse;

class AgendaController extends Controller
{
    public function page()
    {
        return view('vet.agenda.index');
    }

    public function appointments(Request $request): JsonResponse
    {
        $vet = Medico::where('user_id', $request->user()->id)->first();
        $filters = [
            'estado' => $request->query('estado'),
            'fecha' => $request->query('fecha'),
        ];

        $q = Cita::with(['mascota','tutor','medico']);
        // Si el veterinario está vinculado a su ficha, filtramos por su id_mv.
        // En instalaciones antiguas puede no existir ese vínculo; en ese caso mostramos todas para no dejar la vista vacía.
        if ($vet && $vet->id_mv) {
            $q->where('id_mv', $vet->id_mv);
        }

        if (!empty($filters['estado'])) $q->where('estado', $filters['estado']);
        if (!empty($filters['fecha'])) $q->whereDate('fech_cons', $filters['fecha']);

        $rows = $q->orderByDesc('fech_cons')->get();

        return response()->json([
            'appointments' => $rows->map(fn ($c) => $this->transform($c))->values(),
            'summary' => $this->summary($rows),
        ]);
    }

    public function updateStatus(Request $request, Cita $cita): JsonResponse
    {
        // Restringir a citas del propio vet
        $vet = Medico::where('user_id', $request->user()->id)->first();
        if (!$vet || $cita->id_mv !== $vet->id_mv) {
            abort(403);
        }

        $data = $request->validate([
            'estado' => ['required', Rule::in(['pendiente','confirmada','completada','cancelada'])],
        ]);

        $old = $cita->estado;
        $cita->estado = $data['estado'];
        $cita->save();

        // Log en cita_activity
        try {
            \Illuminate\Support\Facades\DB::table('cita_activity')->insert([
                'cita_id' => $cita->id_cita_medi,
                'old_estado' => (string) $old,
                'new_estado' => (string) $cita->estado,
                'actor_user_id' => optional($request->user())->id,
                'actor_name' => optional($request->user())->name,
                'actor_role' => optional($request->user())->role,
                'created_at' => now(),
            ]);
        } catch (\Throwable $e) {}

        $cita->load(['mascota','tutor','medico']);

        return response()->json([
            'message' => 'Estado actualizado.',
            'appointment' => $this->transform($cita),
        ]);
    }

    public function notifications(Request $request): JsonResponse
    {
        $vet = Medico::where('user_id', $request->user()->id)->first();
        if (!$vet) return response()->json(['items' => []]);

        try {
            // Buscar actividades de citas de este vet (ultimas 50)
            $items = \Illuminate\Support\Facades\DB::table('cita_activity as a')
                ->join('cita_medica as c', 'c.id_cita_medi', '=', 'a.cita_id')
                ->leftJoin('tutor as t', 't.id_tutor', '=', 'c.id_tutor')
                ->leftJoin('mascota as m', 'm.id_masc', '=', 'c.id_masc')
                ->where('c.id_mv', $vet->id_mv)
                ->orderByDesc('a.created_at')
                ->limit(50)
                ->get([
                    'a.created_at','a.old_estado','a.new_estado','a.actor_name','a.actor_role','c.fech_cons','m.nom_masc as mascota','t.nomb_tutor','t.apell_tutor'
                ])
                ->map(function ($r) {
                    $who = trim(($r->actor_name ?? '').' ('.($r->actor_role ?? 'sistema').')');
                    $tutor = trim(($r->nomb_tutor ?? '').' '.($r->apell_tutor ?? ''));
                    return [
                        'time' => Carbon::parse($r->created_at)->diffForHumans(),
                        'message' => sprintf('%s cambio estado de %s: %s -> %s (Mascota: %s, Tutor: %s)', $who, Carbon::parse($r->fech_cons)->format('d/m/Y'), $r->old_estado ?? 'N/D', $r->new_estado ?? 'N/D', $r->mascota ?? 'N/D', $tutor ?: 'N/D'),
                    ];
                });
        } catch (\Throwable $e) {
            $items = collect();
        }

        return response()->json(['items' => $items]);
    }

    private function appointmentDateTime(Cita $c): Carbon
    {
        return $c->fecha_hora ?? Carbon::parse($c->fech_cons);
    }

    private function transform(Cita $c): array
    {
        $fecha = $this->appointmentDateTime($c);
        return [
            'id' => $c->id_cita_medi,
            'fecha' => $fecha->format('d/m/Y'),
            'hora' => $fecha->format('H:i'),
            'fecha_hora' => $fecha->format('d/m/Y H:i'),
            'fecha_iso' => $fecha->format('Y-m-d'),
            'datetime_local' => $fecha->format('Y-m-d\TH:i'),
            'mascota' => $c->mascota?->nom_masc ?? 'Sin mascota',
            'tutor' => $c->tutor ? trim(($c->tutor->nomb_tutor ?? '').' '.($c->tutor->apell_tutor ?? '')) : 'Sin tutor',
            'tutor_email' => $c->tutor->correo_tutor ?? '',
            'veterinario' => $c->medico ? trim(($c->medico->nombre_mv ?? '').' '.($c->medico->apell_mv ?? '')) : 'Yo',
            'especialidad' => $c->medico?->especialidad ?? 'Sin especificar',
            'estado' => $c->estado ?? 'pendiente',
            'motivo' => $c->motiv_cons,
        ];
    }

    private function summary($rows): array
    {
        $today = Carbon::today();
        $startOfWeek = (clone $today)->startOfWeek();
        $endOfWeek = (clone $startOfWeek)->endOfWeek();

        return [
            'citas_hoy' => $rows->filter(fn ($c) => $this->appointmentDateTime($c)->isSameDay($today))->count(),
            'citas_semana' => $rows->filter(fn ($c) => $this->appointmentDateTime($c)->between($startOfWeek, $endOfWeek))->count(),
            'confirmadas' => $rows->filter(fn ($c) => $c->estado === 'confirmada')->count(),
            'pendientes' => $rows->filter(fn ($c) => ($c->estado ?? 'pendiente') === 'pendiente')->count(),
        ];
    }

    public function exportPdf(Request $request): StreamedResponse
    {
        $vet = Medico::where('user_id', $request->user()->id)->first();

        $q = Cita::with(['mascota','tutor','medico']);
        if ($vet && $vet->id_mv) { $q->where('id_mv', $vet->id_mv); }
        $rows = $q->orderByDesc('fech_cons')->get();

        $html = view('vet.reports.agenda', [
            'doctor' => $request->user()->name ?? 'Veterinario',
            'now' => Carbon::now(),
            'citas' => $rows,
        ])->render();

        $options = new \Dompdf\Options();
        $options->set('isRemoteEnabled', false);
        $options->set('defaultFont', 'DejaVu Sans');
        $options->setIsHtml5ParserEnabled(true);

        $dompdf = new \Dompdf\Dompdf($options);
        $dompdf->loadHtml($html, 'UTF-8');
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->render();

        $filename = 'agenda_vet_' . Carbon::now()->format('Ymd_His') . '.pdf';

        return response()->streamDownload(function () use ($dompdf) {
            echo $dompdf->output();
        }, $filename, ['Content-Type' => 'application/pdf']);
    }

    public function getTutorPets(Request $request, Tutor $tutor): JsonResponse
    {
        $pets = Mascota::where('id_tutor', $tutor->id_tutor)
            ->orderBy('nom_masc')
            ->get(['id_masc','nom_masc']);
        return response()->json(['pets' => $pets]);
    }

    public function storeAppointment(Request $request): JsonResponse
    {
        $vet = Medico::where('user_id', $request->user()->id)->first();
        if (!$vet) {
            return response()->json(['message' => 'No se encontró la ficha del médico.'], 422);
        }

        $data = $request->validate([
            'fecha' => ['required','date'],
            'hora' => ['required','date_format:H:i'],
            'motivo' => ['required','string','max:200'],
            'tutor_id' => ['required','integer','exists:tutor,id_tutor'],
            'mascota_id' => ['required','integer','exists:mascota,id_masc'],
        ]);

        // Validar que la mascota pertenezca al tutor
        $exists = Mascota::where('id_masc', $data['mascota_id'])
            ->where('id_tutor', $data['tutor_id'])->exists();
        if (!$exists) {
            return response()->json(['message' => 'La mascota seleccionada no pertenece al tutor indicado.'], 422);
        }

        $datetime = Carbon::createFromFormat('Y-m-d H:i', sprintf('%s %s', $data['fecha'], $data['hora']));

        $cita = new Cita();
        $cita->fech_cons = $datetime->toDateString();
        $cita->hora_cons = $datetime->format('H:i:s');
        $cita->motiv_cons = $data['motivo'];
        // Algunos esquemas tienen diag_cons/trata_cons como NOT NULL; usar cadenas vacías
        $cita->diag_cons = '';
        $cita->trata_cons = '';
        $cita->estado = 'pendiente';
        $cita->id_tutor = $data['tutor_id'];
        $cita->id_mv = $vet->id_mv;
        $cita->id_masc = $data['mascota_id'];
        $cita->save();

        // Log actividad
        try {
            \Illuminate\Support\Facades\DB::table('cita_activity')->insert([
                'cita_id' => $cita->id_cita_medi,
                'old_estado' => null,
                'new_estado' => 'pendiente',
                'actor_user_id' => optional($request->user())->id,
                'actor_name' => optional($request->user())->name,
                'actor_role' => optional($request->user())->role,
                'created_at' => now(),
            ]);
        } catch (\Throwable $e) {}

        $cita->load(['mascota','tutor','medico']);
        return response()->json([
            'message' => 'Cita creada exitosamente.',
            'appointment' => $this->transform($cita),
        ], 201);
    }
}
