<?php
declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Admin;
use App\Models\Cita;
use App\Models\Mascota;
use App\Models\Medico;
use App\Models\Tutor;
use App\Models\User;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\Rule;
use Symfony\Component\HttpFoundation\StreamedResponse;

class AdminDashboardController extends Controller
{
    public function index()
    {
        $veterinarios = Medico::query()
            ->orderBy('nombre_mv')
            ->get()
            ->map(fn (Medico $medico) => [
                'id'     => $medico->id_mv,
                'nombre' => trim("{$medico->nombre_mv} {$medico->apell_mv}"),
            ]);

        $preTotals = [
            'usuarios'     => Tutor::count(),
            'mascotas'     => Mascota::count(),
            'citas'        => Cita::count(),
            'veterinarios' => Medico::count(),
        ];

        $preDistribution = [
            'labels' => ['Tutores', 'Veterinarios'],
            'data'   => [$preTotals['usuarios'], $preTotals['veterinarios']],
        ];

        return view('admin.dashboard.index', [
            'veterinarios' => $veterinarios,
            'preload'      => [
                'totals'        => $preTotals,
                'distribution'  => $preDistribution,
            ],
        ]);
    }

    public function summary(): JsonResponse
    {
        $totalTutores      = Tutor::count();
        $totalMascotas     = Mascota::count();
        $totalCitas        = Cita::count();
        $totalVeterinarios = Medico::count();

        return response()->json([
            'totals' => [
                'usuarios'     => $totalTutores,
                'mascotas'     => $totalMascotas,
                'citas'        => $totalCitas,
                'veterinarios' => $totalVeterinarios,
            ],
            'distribution' => [
                'labels' => ['Tutores', 'Veterinarios'],
                'data'   => [$totalTutores, $totalVeterinarios],
            ],
        ]);
    }

    // Lista de citas para la vista de administración (JSON)
    public function appointments(Request $request): JsonResponse
    {
        $filters = [
            'estado'      => $request->query('estado'),
            'veterinario' => $request->query('veterinario'),
            'fecha'       => $request->query('fecha'),
        ];

        $appointments = $this->buildAppointmentQuery($filters)
            ->with(['mascota', 'tutor', 'medico'])
            ->orderByDesc('fech_cons')
            ->get();

        return response()->json([
            'appointments' => $appointments->map(fn (Cita $c) => $this->transformAppointment($c))->values(),
            'summary'      => $this->summarizeAppointments($appointments),
        ]);
    }

    // Cambiar estado de una cita
    public function updateAppointmentStatus(Request $request, Cita $cita): JsonResponse
    {
        $data = $request->validate([
            'estado' => ['required', Rule::in(['pendiente', 'confirmada', 'completada', 'cancelada'])],
        ]);

        $old          = $cita->estado;
        $cita->estado = $data['estado'];
        $cita->save();

        // Log simple en tabla cita_activity para notificaciones (ignorar si no existe)
        try {
            DB::table('cita_activity')->insert([
                'cita_id'       => $cita->id_cita_medi,
                'old_estado'    => (string) $old,
                'new_estado'    => (string) $cita->estado,
                'actor_user_id' => optional($request->user())->id,
                'actor_name'    => optional($request->user())->name,
                'actor_role'    => optional($request->user())->role,
                'created_at'    => now(),
            ]);
        } catch (\Throwable $e) {}

        $cita->load(['mascota', 'tutor', 'medico']);

        return response()->json([
            'message'     => 'Estado actualizado correctamente.',
            'appointment' => $this->transformAppointment($cita),
        ]);
    }

    // Exportar citas como CSV simple respetando filtros
    public function exportAppointments(Request $request): StreamedResponse
    {
        $filters = [
            'estado'      => $request->query('estado'),
            'veterinario' => $request->query('veterinario'),
            'fecha'       => $request->query('fecha'),
        ];

        $rows = $this->buildAppointmentQuery($filters)
            ->with(['mascota', 'tutor', 'medico'])
            ->orderByDesc('fech_cons')
            ->get();

        $filename = 'citas_' . Carbon::now()->format('Ymd_His') . '.csv';

        return response()->streamDownload(function () use ($rows) {
            $out = fopen('php://output', 'w');
            fputcsv($out, ['Fecha', 'Mascota', 'Tutor', 'Veterinario', 'Especialidad', 'Estado']);
            foreach ($rows as $cita) {
                $t = $this->transformAppointment($cita);
                fputcsv($out, [
                    $t['fecha'],
                    $t['mascota'],
                    $t['tutor'],
                    $t['veterinario'],
                    $t['especialidad'],
                    $t['estado'],
                ]);
            }
            fclose($out);
        }, $filename, ['Content-Type' => 'text/csv; charset=UTF-8']);
    }

    // Reporte PDF de citas
    public function exportAppointmentsPdf(Request $request): StreamedResponse
    {
        $filters = [
            'estado'      => $request->query('estado'),
            'veterinario' => $request->query('veterinario'),
            'fecha'       => $request->query('fecha'),
        ];

        $rows = $this->buildAppointmentQuery($filters)
            ->with(['mascota', 'tutor', 'medico'])
            ->orderByDesc('fech_cons')
            ->get();

        $html = view('admin.reports.appointments', [
            'now'   => Carbon::now(),
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

        $filename = 'reporte_citas_' . now()->format('Ymd_His') . '.pdf';

        return response()->streamDownload(fn () => print $dompdf->output(), $filename, [
            'Content-Type' => 'application/pdf',
        ]);
    }

    // Listar usuarios (JSON)
    public function users(Request $request): JsonResponse
    {
        return response()->json([
            'users' => $this->buildUserTableData($request->query('role')),
        ]);
    }

    // Exportar usuarios CSV (sin columna Estado)
    public function exportUsers(Request $request): StreamedResponse
    {
        $role = $request->query('role');
        $rows = $this->buildUserTableData($role);
        $filename = 'usuarios_' . Carbon::now()->format('Ymd_His') . '.csv';

        return response()->streamDownload(function () use ($rows) {
            $out = fopen('php://output', 'w');
            fputcsv($out, ['Nombre', 'Email', 'Tipo', 'Tarjeta/Mascotas', 'Especialidad', 'Fecha registro']);
            foreach ($rows as $u) {
                fputcsv($out, [
                    $u['nombre'] ?? '',
                    $u['email'] ?? '',
                    $u['tipo'] ?? '',
                    $u['detalle'] ?? '',
                    $u['especialidad'] ?? '',
                    $u['fecha_registro'] ?? '',
                ]);
            }
            fclose($out);
        }, $filename, ['Content-Type' => 'text/csv; charset=UTF-8']);
    }

    public function exportUsersPdf(Request $request): StreamedResponse
    {
        $role  = $request->query('role');
        $users = $this->buildUserTableData($role);

        $title = $role === 'tutor' ? 'Reporte de Tutores' : ($role === 'vet' ? 'Reporte de Veterinarios' : 'Reporte de Usuarios');

        $logoPath = public_path('img/LogoOrangeHearth.png');
        $logoData = is_file($logoPath) ? base64_encode((string) file_get_contents($logoPath)) : null;
        $logoSrc  = $logoData ? 'data:image/png;base64,' . $logoData : null;

        $html = view('admin.reports.users', [
            'users' => $users,
            'title' => $title,
            'logo'  => $logoSrc,
            'date'  => Carbon::now()->format('d/m/Y H:i'),
        ])->render();

        $options = new \Dompdf\Options();
        $options->set('isRemoteEnabled', true);
        $options->set('defaultFont', 'DejaVu Sans');
        $options->setIsHtml5ParserEnabled(true);

        $dompdf = new \Dompdf\Dompdf($options);
        $dompdf->loadHtml($html, 'UTF-8');
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->render();

        $filename = 'reporte_usuarios_' . now()->format('Ymd_His') . '.pdf';

        return response()->streamDownload(fn () => print $dompdf->output(), $filename, [
            'Content-Type' => 'application/pdf',
        ]);
    }

    public function storeAdmin(Request $request): JsonResponse|RedirectResponse|\Illuminate\Contracts\View\View
    {
        $data = $request->validate([
            'name'     => ['required', 'string', 'max:255'],
            'cedula'   => ['required', 'string', 'max:20', 'unique:administrador,ced_adm'],
            'email'    => ['required', 'email:rfc,dns', 'max:255', 'unique:users,email'],
            'password' => ['required', 'string', 'min:8'],
        ]);

        $user = User::create([
            'name'     => $data['name'],
            'email'    => $data['email'],
            'password' => Hash::make($data['password']),
            'role'     => 'admin',
        ]);

        Admin::create([
            'ced_adm'  => $data['cedula'],
            'nomb_adm' => $data['name'],
            'user_id'  => $user->id,
        ]);

        $message = 'Administrador registrado exitosamente.';

        if ($request->expectsJson()) {
            return response()->json(['message' => $message]);
        }

        // Reutiliza el index para mostrar dashboard con preload
        return $this->index()->with('success_admin', $message);
    }

    public function storeVeterinarian(Request $request): JsonResponse|RedirectResponse
    {
        $data = $request->validate([
            'nombre'               => ['required', 'string', 'max:150'],
            'cedula'               => ['required', 'string', 'max:20'],
            'correo'               => ['required', 'email:rfc,dns', 'max:150', 'unique:users,email'],
            'clave'                => ['required', 'string', 'min:8'],
            'especialidad'         => ['required', 'string', 'max:120'],
            'tarjeta_profesional'  => ['required', 'string', 'max:30', 'unique:medico_veterinario,tarjeta_profesional_mv'],
            'telefono'             => ['required', 'string', 'max:30'],
        ]);

        [$nombre, $apellido] = $this->splitNombre($data['nombre']);

        try {
            DB::beginTransaction();

            $user = User::create([
                'name'     => $data['nombre'],
                'email'    => $data['correo'],
                'password' => Hash::make($data['clave']),
                'role'     => 'vet',
            ]);

            $medico = Medico::create([
                'nombre_mv'              => $nombre,
                'apell_mv'               => $apellido,
                'cedu_mv'                => $data['cedula'],
                'tarjeta_profesional_mv' => $data['tarjeta_profesional'],
                'user_id'                => $user->id,
                'especialidad'           => $data['especialidad'],
                'telefono'               => $data['telefono'],
            ]);

            DB::commit();
        } catch (\Throwable $e) {
            DB::rollBack();

            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'message' => 'No se pudo registrar el veterinario',
                    'error'   => $e->getMessage(),
                ], 422);
            }

            return back()->withErrors(['general' => 'No se pudo registrar el veterinario.'])->withInput();
        }

        $payload = [
            'message'     => 'Veterinario registrado exitosamente.',
            'veterinario' => [
                'id'     => $medico->id_mv,
                'nombre' => trim("{$medico->nombre_mv} {$medico->apell_mv}"),
            ],
        ];

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json($payload, 201);
        }

        return redirect()->route('admin.dashboard')->with('success', $payload['message']);
    }

    private function buildMonthlyRegistrations(): array
    {
        Carbon::setLocale('es');
        $start  = Carbon::now()->startOfMonth()->subMonths(5);
        $months = [];

        for ($i = 0; $i < 6; $i++) {
            $current = $start->copy()->addMonths($i);
            $count   = User::whereIn('role', ['tutor', 'vet'])
                ->whereBetween('created_at', [$current->copy()->startOfMonth(), $current->copy()->endOfMonth()])
                ->count();

            $months[] = [
                'label' => ucfirst($current->translatedFormat('F')),
                'value' => $count,
            ];
        }

        return $months;
    }

    private function buildSpeciesDistribution(): array
    {
        $species = Mascota::query()
            ->select('espe_masc', DB::raw('count(*) as total'))
            ->groupBy('espe_masc')
            ->orderByDesc('total')
            ->get()
            ->map(fn ($row) => [
                'label' => $this->normalizeSpecies($row->espe_masc),
                'value' => (int) $row->total,
            ])
            ->values();

        if ($species->isEmpty()) {
            return [
                ['label' => 'Canino', 'value' => 0],
                ['label' => 'Felino', 'value' => 0],
            ];
        }

        return $species->all();
    }

    private function buildWeeklyActivity(): array
    {
        Carbon::setLocale('es');
        $period = CarbonPeriod::create(Carbon::now()->startOfDay()->subDays(6), Carbon::now()->startOfDay());

        $days          = [];
        $registrations = [];
        $appointments  = [];

        foreach ($period as $day) {
            $days[] = ucfirst($day->translatedFormat('D'));

            $registrations[] = User::whereIn('role', ['tutor', 'vet'])
                ->whereDate('created_at', $day)
                ->count();

            $appointments[] = Cita::whereDate('fech_cons', $day)->count();
        }

        return [
            'labels'        => $days,
            'registrations' => $registrations,
            'appointments'  => $appointments,
        ];
    }

    private function buildActivityFeed(): array
    {
        Carbon::setLocale('es');

        $feed = collect();

        $recentUsers = User::whereIn('role', ['tutor', 'vet'])
            ->latest('created_at')
            ->take(5)
            ->get();

        foreach ($recentUsers as $user) {
            $feed->push([
                'icon'      => $user->role === 'vet' ? 'fas fa-user-md' : 'fas fa-user-plus',
                'message'   => ($user->role === 'vet' ? 'Nuevo veterinario: ' : 'Nuevo tutor: ') . $user->name,
                'timestamp' => $user->created_at,
            ]);
        }

        $recentAppointments = Cita::with(['mascota'])
            ->orderByDesc('fech_cons')
            ->take(5)
            ->get();

        foreach ($recentAppointments as $cita) {
            $feed->push([
                'icon'      => 'fas fa-calendar-plus',
                'message'   => 'Cita programada para ' . ($cita->mascota?->nom_masc ?? 'Mascota'),
                'timestamp' => Carbon::parse($cita->fech_cons),
            ]);
        }

        return $feed->sortByDesc('timestamp')
            ->take(8)
            ->map(fn ($item) => [
                'icon'  => $item['icon'],
                'message' => $item['message'],
                'time'  => $item['timestamp']
                    ? Carbon::parse($item['timestamp'])->diffForHumans()
                    : 'Recientemente',
            ])
            ->values()
            ->all();
    }

    private function buildUserTableData(?string $role = null): array
    {
        Carbon::setLocale('es');

        $tutores = collect();
        if (!$role || $role === 'tutor') {
            $tutores = Tutor::with(['mascotas', 'user'])
                ->get()
                ->map(function (Tutor $tutor) {
                    $fullName  = trim("{$tutor->nomb_tutor} {$tutor->apell_tutor}");
                    $createdAt = $tutor->user?->created_at;

                    return [
                        'id'              => $tutor->user?->id,
                        'nombre'          => $fullName,
                        'email'           => $tutor->correo_tutor,
                        'tipo'            => 'Tutor',
                        'detalle'         => $tutor->mascotas->count() . ' mascota(s)',
                        'especialidad'    => '-',
                        'fecha_registro'  => $createdAt ? $createdAt->format('d/m/Y') : 'N/D',
                    ];
                });
        }

        $veterinarios = collect();
        if (!$role || $role === 'vet') {
            $veterinarios = Medico::with('user')
                ->get()
                ->map(function (Medico $medico) {
                    $fullName  = trim("{$medico->nombre_mv} {$medico->apell_mv}");
                    $createdAt = $medico->user?->created_at;

                    return [
                        'id'              => $medico->user?->id,
                        'nombre'          => $fullName,
                        'email'           => $medico->user?->email ?? 'Sin usuario',
                        'tipo'            => 'Veterinario',
                        'detalle'         => $medico->tarjeta_profesional_mv ?: 'Sin tarjeta',
                        'especialidad'    => $medico->especialidad ?? 'Sin especificar',
                        'fecha_registro'  => $createdAt ? $createdAt->format('d/m/Y') : 'N/D',
                    ];
                });
        }

        return $tutores->concat($veterinarios)
            ->sortBy('nombre', SORT_NATURAL | SORT_FLAG_CASE)
            ->values()
            ->all();
    }

    private function buildAppointmentQuery(array $filters)
    {
        $query = Cita::query();

        if (!empty($filters['estado'])) {
            $query->where('estado', $filters['estado']);
        }
        if (!empty($filters['veterinario'])) {
            $query->where('id_mv', $filters['veterinario']);
        }
        if (!empty($filters['fecha'])) {
            $query->whereDate('fech_cons', $filters['fecha']);
        }

        return $query;
    }

    /**
     * Intenta convertir un string a Carbon usando varios formatos comunes.
     */
    private function tryCarbonFormats(string $candidate): ?Carbon
    {
        $candidate = trim(preg_replace('/\s+/', ' ', $candidate));
        if ($candidate === '') {
            return null;
        }

        $formats = [
            'Y-m-d H:i:s.u',
            'Y-m-d H:i:s',
            'Y-m-d H:i',
            'Y-m-d\TH:i:s.u',
            'Y-m-d\TH:i:s',
            'Y-m-d\TH:i',
            'd/m/Y H:i:s',
            'd/m/Y H:i',
            'd-m-Y H:i:s',
            'd-m-Y H:i',
            'Y-m-d h:i A',
            'd/m/Y h:i A',
            'd-m-Y h:i A',
            'Y-m-d',
            'd/m/Y',
            'd-m-Y',
        ];

        foreach ($formats as $fmt) {
            try {
                return Carbon::createFromFormat($fmt, $candidate);
            } catch (\Throwable $e) {}
        }

        try {
            return Carbon::parse($candidate);
        } catch (\Throwable $e) {
            return null;
        }
    }

    /**
     * Obtiene la fecha/hora de una cita de forma segura y tolerante.
     */
    private function appointmentDateTime(Cita $cita): Carbon
    {
        $fecha = trim((string) ($cita->fech_cons ?? ''));
        $hora  = trim((string) ($cita->hora_cons ?? ''));

        $candidates = [];

        if ($fecha !== '' && $hora !== '') {
            $horaNorm = preg_match('/^\d{2}:\d{2}$/', $hora) ? ($hora . ':00') : $hora;
            $candidates[] = "{$fecha} {$horaNorm}";
            $candidates[] = "{$fecha}T{$horaNorm}";
        }

        if (!empty($cita->fecha_hora)) {
            $raw = preg_replace('/\s+(\d{2}:\d{2}:\d{2})\s+\1$/', ' $1', (string) $cita->fecha_hora);
            $candidates[] = $raw;
        }

        if ($fecha !== '' && $hora === '') {
            $candidates[] = $fecha;
        }

        foreach ($candidates as $cand) {
            if ($dt = $this->tryCarbonFormats($cand)) {
                return $dt;
            }
        }

        $fallback = trim($fecha !== '' ? $fecha : ($hora !== '' ? $hora : ((string) ($cita->fecha_hora ?? ''))));
        try {
            return Carbon::parse($fallback !== '' ? $fallback : Carbon::now());
        } catch (\Throwable $e) {
            return Carbon::now();
        }
    }

    private function transformAppointment(Cita $cita): array
    {
        $fecha = $this->appointmentDateTime($cita);

        return [
            'id'             => $cita->id_cita_medi,
            'fecha'          => $fecha->format('d/m/Y'),
            'hora'           => $fecha->format('H:i'),
            'fecha_hora'     => $fecha->format('d/m/Y H:i'),
            'fecha_iso'      => $fecha->format('Y-m-d'),
            'datetime_local' => $fecha->format('Y-m-d\TH:i'),
            'mascota'        => $cita->mascota?->nom_masc ?? 'Sin mascota',
            'tutor'          => $cita->tutor ? trim("{$cita->tutor->nomb_tutor} {$cita->tutor->apell_tutor}") : 'Sin tutor',
            'tutor_email'    => $cita->tutor?->correo_tutor ?? '',
            'veterinario'    => $cita->medico ? trim("{$cita->medico->nombre_mv} {$cita->medico->apell_mv}") : 'Sin asignar',
            'veterinario_id' => $cita->medico?->id_mv,
            'especialidad'   => $cita->medico?->especialidad ?? 'Sin especificar',
            'estado'         => $cita->estado ?? 'pendiente',
            'motivo'         => $cita->motiv_cons,
        ];
    }

    private function summarizeAppointments(Collection $appointments): array
    {
        $today       = Carbon::today();
        $startOfWeek = (clone $today)->startOfWeek();
        $endOfWeek   = (clone $startOfWeek)->endOfWeek();

        return [
            'citas_hoy'    => $appointments->filter(fn ($c) => $this->appointmentDateTime($c)->isSameDay($today))->count(),
            'citas_semana' => $appointments->filter(fn ($c) => $this->appointmentDateTime($c)->between($startOfWeek, $endOfWeek))->count(),
            'confirmadas'  => $appointments->where('estado', 'confirmada')->count(),
            'pendientes'   => $appointments->filter(fn ($c) => ($c->estado ?? 'pendiente') === 'pendiente')->count(),
        ];
    }

    private function normalizeSpecies(?string $species): string
    {
        return $species ? ucfirst(str_replace('_', ' ', $species)) : 'Sin especificar';
    }

    private function splitNombre(string $nombre): array
    {
        $parts = preg_split('/\s+/', trim($nombre), 2);
        return [$parts[0] ?? $nombre, $parts[1] ?? ''];
    }

    // Genera y descarga un reporte PDF del sistema
    public function exportSystemReportPdf(): StreamedResponse
    {
        $now = Carbon::now();

        $totTutores  = Tutor::count();
        $totVets     = Medico::count();
        $totUsers    = $totTutores + $totVets;
        $totMascotas = Mascota::count();
        $totCitas    = Cita::count();

        $lastUsers = User::whereIn('role', ['tutor', 'vet'])
            ->latest('created_at')
            ->take(5)
            ->get(['name','email','role','created_at']);

        $html = view('admin.reports.system', compact(
            'now','totTutores','totVets','totUsers','totMascotas','totCitas','lastUsers'
        ))->render();

        $options = new \Dompdf\Options();
        $options->set('isRemoteEnabled', false);
        $options->set('defaultFont', 'DejaVu Sans');
        $options->setIsHtml5ParserEnabled(true);

        $dompdf = new \Dompdf\Dompdf($options);
        $dompdf->loadHtml($html, 'UTF-8');
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->render();

        $filename = 'reporte_orangehearth_' . $now->format('Ymd_His') . '.pdf';

        return response()->streamDownload(fn () => print $dompdf->output(), $filename, [
            'Content-Type' => 'application/pdf',
        ]);
    }

    public function updateUser(Request $request, User $user): JsonResponse
    {
        $role = $user->role;

        $baseRules = [
            'name'  => ['required', 'string', 'max:150'],
            'email' => ['required', 'email', 'max:150', Rule::unique('users', 'email')->ignore($user->id)],
        ];

        if ($role === 'tutor') {
            $tutor = Tutor::where('user_id', $user->id)->first();
            $roleRules = [
                'ced_tutor'   => ['required', 'string', 'max:20', Rule::unique('tutor', 'ced_tutor')->ignore(optional($tutor)->id_tutor, 'id_tutor')],
                'tel_tutor'   => ['required', 'string', 'max:20'],
                'direc_tutor' => ['required', 'string', 'max:255'],
            ];
        } elseif ($role === 'vet') {
            $medico = Medico::where('user_id', $user->id)->first();
            $roleRules = [
                'cedu_mv'                => ['required', 'string', 'max:20', Rule::unique('medico_veterinario', 'cedu_mv')->ignore(optional($medico)->id_mv, 'id_mv')],
                'tarjeta_profesional_mv' => ['required', 'string', 'max:30', Rule::unique('medico_veterinario', 'tarjeta_profesional_mv')->ignore(optional($medico)->id_mv, 'id_mv')],
                'especialidad'           => ['required', 'string', 'max:120'],
                'telefono'               => ['required', 'string', 'max:30'],
               // 'password' => ['sometimes', 'nullable', 'string', 'min:8', 'confirmed'],
            ];
        } else {
            $roleRules = [];
        }

        $data = $request->validate(array_merge($baseRules, $roleRules));

        DB::transaction(function () use ($user, $data, $role) {
            $user->update([
                'name'  => $data['name'],
                'email' => $data['email'],
            ]);

            

            if ($role === 'tutor') {
                $tutor = Tutor::where('user_id', $user->id)->first();
                if ($tutor) {
                    [$nomb, $apell] = $this->splitNombre($data['name']);
                    $tutor->update([
                        'nomb_tutor'   => $nomb,
                        'apell_tutor'  => $apell,
                        'ced_tutor'    => $data['ced_tutor'],
                        'correo_tutor' => $data['email'],
                        'tel_tutor'    => $data['tel_tutor'],
                        'direc_tutor'  => $data['direc_tutor'],
                    ]);
                }
            } elseif ($role === 'vet') {
                $medico = Medico::where('user_id', $user->id)->first();
                if ($medico) {
                    [$nombre, $apellido] = $this->splitNombre($data['name']);
                    $medico->update([
                        'nombre_mv'              => $nombre,
                        'apell_mv'               => $apellido,
                        'cedu_mv'                => $data['cedu_mv'],
                        'tarjeta_profesional_mv' => $data['tarjeta_profesional_mv'],
                        'especialidad'           => $data['especialidad'],
                        'telefono'               => $data['telefono'],
                    ]);
                }
            }
        });

        return response()->json(['message' => 'Usuario actualizado correctamente.']);
    }

    public function userDetails(User $user): JsonResponse
    {
        $userData = $user->toArray();

        if ($user->role === 'tutor') {
            if ($tutorData = Tutor::where('user_id', $user->id)->first()) {
                $userData['tutor'] = $tutorData->toArray();
            }
        } elseif ($user->role === 'vet') {
            if ($vetData = Medico::where('user_id', $user->id)->first()) {
                $userData['vet'] = $vetData->toArray();
            }
        }

        return response()->json($userData);
    }

    public function destroyUser(User $user): JsonResponse
    {
        if ($user->role === 'admin') {
            return response()->json(['message' => 'No puedes eliminar un administrador desde aquí.'], 422);
        }

        return DB::transaction(function () use ($user) {
            if ($user->role === 'vet') {
                Medico::where('user_id', $user->id)->delete();
            }

            if ($tutor = Tutor::where('user_id', $user->id)->first()) {
                $tieneMascotas = $tutor->mascotas()->exists();
                $tieneCitas    = $tutor->citas()->exists();
                if ($tieneMascotas || $tieneCitas) {
                    return response()->json([
                        'message' => 'No se puede eliminar: el tutor tiene mascotas y/o citas vinculadas.'
                    ], 409);
                }
                $tutor->delete();
            }

            $user->delete();

            return response()->json(['message' => 'Usuario eliminado.']);
        });
    }

    // public function rescheduleAppointment(Request $request, Cita $cita): JsonResponse
    // {
    //     $data = $request->validate([
    //         'fecha' => ['required', 'date', 'date_format:Y-m-d'],
    //         'hora'  => ['required', 'date_format:H:i'],
    //     ]);
    //
    //     try {
    //         $datetime = Carbon::createFromFormat('Y-m-d H:i', sprintf('%s %s', $data['fecha'], $data['hora']));
    //     } catch (\Throwable $e) {
    //         return response()->json(['message' => 'El formato de la fecha u hora es inválido.'], 422);
    //     }
    //
    //     $oldDateTime   = $this->appointmentDateTime($cita);
    //     $cita->fech_cons = $datetime->toDateString();
    //     if (Schema::hasColumn($cita->getTable(), 'hora_cons')) {
    //         $cita->hora_cons = $datetime->format('H:i:s');
    //     }
    //     $cita->estado = 'reprogramada';
    //     $cita->save();
    //
    //     try {
    //         DB::table('cita_activity')->insert([
    //             'cita_id'       => $cita->id_cita_medi,
    //             'old_estado'    => 'reprogramada de ' . $oldDateTime->format('Y-m-d H:i'),
    //             'new_estado'    => $datetime->format('Y-m-d H:i'),
    //             'actor_user_id' => optional($request->user())->id,
    //             'actor_name'    => optional($request->user())->name,
    //             'actor_role'    => optional($request->user())->role,
    //             'created_at'    => now(),
    //         ]);
    //     } catch (\Throwable $e) {}
    //
    //     $cita->load(['mascota', 'tutor', 'medico']);
    //
    //     return response()->json([
    //         'message'     => 'La cita ha sido reprogramada con éxito.',
    //         'appointment' => $this->transformAppointment($cita),
    //     ]);
    // }

    // Asignar o cambiar el médico veterinario de una cita
    public function assignDoctor(Request $request, Cita $cita): JsonResponse
    {
        $data = $request->validate([
            'id_mv' => ['required', 'integer', Rule::exists('medico_veterinario', 'id_mv')],
        ]);

        $cita->id_mv = $data['id_mv'];
        $cita->save();

        try {
            DB::table('cita_activity')->insert([
                'cita_id'       => $cita->id_cita_medi,
                'old_estado'    => (string) ($cita->getOriginal('estado') ?? ''),
                'new_estado'    => 'asignado_medico:' . $data['id_mv'],
                'actor_user_id' => optional($request->user())->id,
                'actor_name'    => optional($request->user())->name,
                'actor_role'    => optional($request->user())->role,
                'created_at'    => now(),
            ]);
        } catch (\Throwable $e) {}

        $cita->load(['mascota', 'tutor', 'medico']);

        return response()->json([
            'message'     => 'Médico asignado correctamente.',
            'appointment' => $this->transformAppointment($cita),
        ]);
    }
}
