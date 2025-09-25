<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
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
use Illuminate\Validation\Rule;

use Symfony\Component\HttpFoundation\StreamedResponse;
$options = new \Dompdf\Options();
$dompdf  = new \Dompdf\Dompdf($options);


class AdminDashboardController extends Controller
{
    public function index()
    {
        $veterinarios = Medico::query()
            ->orderBy('nombre_mv')
            ->get()
            ->map(fn (Medico $medico) => [
                'id' => $medico->id_mv,
                'nombre' => trim("{$medico->nombre_mv} {$medico->apell_mv}"),
                'estado' => $medico->estado ?? 'activo',
            ]);

        $preTotals = [
            'usuarios' => Tutor::count(),
            'mascotas' => Mascota::count(),
            'citas' => Cita::count(),
            'veterinarios' => Medico::count(),
        ];

        $preDistribution = [
            'labels' => ['Tutores', 'Veterinarios'],
            'data' => [$preTotals['usuarios'], $preTotals['veterinarios']],
        ];
        
        return view('admin.dashboard.index', [
            'veterinarios' => $veterinarios,
            'preload' => [
                'totals' => $preTotals,
                'distribution' => $preDistribution,
            ],
        ]);
    }
public function summary(): JsonResponse
    {
        $totalTutores = Tutor::count();
        $totalMascotas = Mascota::count();
        $totalCitas = Cita::count();
        $totalVeterinarios = Medico::count();

        return response()->json([
            'totals' => [
                'usuarios' => $totalTutores,
                'mascotas' => $totalMascotas,
                'citas' => $totalCitas,
                'veterinarios' => $totalVeterinarios,
            ],
            'distribution' => [
                'labels' => ['Tutores', 'Veterinarios'],
                'data' => [$totalTutores, $totalVeterinarios],
            ],
        ]);
    }

    // Lista de citas para la vista de administración (JSON)
    public function appointments(Request $request): JsonResponse
    {
        $filters = [
            'estado' => $request->query('estado'),
            'veterinario' => $request->query('veterinario'),
            'fecha' => $request->query('fecha'),
        ];

        $query = $this->buildAppointmentQuery($filters)
            ->with(['mascota', 'tutor', 'medico'])
            ->orderByDesc('fech_cons');

        $appointments = $query->get();

        return response()->json([
            'appointments' => $appointments->map(fn (Cita $c) => $this->transformAppointment($c))->values(),
            'summary' => $this->summarizeAppointments($appointments),
        ]);
    }

    // Cambiar estado de una cita
    public function updateAppointmentStatus(Request $request, Cita $cita): JsonResponse
    {
        $data = $request->validate([
            'estado' => ['required', Rule::in(['pendiente', 'confirmada', 'completada', 'cancelada'])],
        ]);

        $old = $cita->estado;
        $cita->estado = $data['estado'];
        $cita->save();

        // Log simple en tabla cita_activity para notificaciones
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
        } catch (\Throwable $e) {
            // Ignorar si la tabla no existe todavia
        }

        $cita->load(['mascota', 'tutor', 'medico']);

        return response()->json([
            'message' => 'Estado actualizado correctamente.',
            'appointment' => $this->transformAppointment($cita),
        ]);
    }

    // Exportar citas como CSV simple respetando filtros
    public function exportAppointments(Request $request): StreamedResponse
    {
        $filters = [
            'estado' => $request->query('estado'),
            'veterinario' => $request->query('veterinario'),
            'fecha' => $request->query('fecha'),
        ];

        $rows = $this->buildAppointmentQuery($filters)
            ->with(['mascota', 'tutor', 'medico'])
            ->orderByDesc('fech_cons')
            ->get();

        $filename = 'citas_' . Carbon::now()->format('Ymd_His') . '.csv';

        return response()->streamDownload(function () use ($rows) {
            $out = fopen('php://output', 'w');
            // Encabezados
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
        }, $filename, [
            'Content-Type' => 'text/csv; charset=UTF-8',
        ]);
    }

    // Reporte PDF de citas (similar al del veterinario)
    public function exportAppointmentsPdf(Request $request): StreamedResponse
    {
        $filters = [
            'estado' => $request->query('estado'),
            'veterinario' => $request->query('veterinario'),
            'fecha' => $request->query('fecha'),
        ];

        $rows = $this->buildAppointmentQuery($filters)
            ->with(['mascota', 'tutor', 'medico'])
            ->orderByDesc('fech_cons')
            ->get();

        $html = view('admin.reports.appointments', [
            'now' => \Carbon\Carbon::now(),
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

        return response()->streamDownload(function () use ($dompdf) {
            echo $dompdf->output();
        }, $filename, ['Content-Type' => 'application/pdf']);
    }
    // Listar usuarios para la sección de administración
    public function users(): JsonResponse
    {
        return response()->json([
            'users' => $this->buildUserTableData(),
        ]);
    }

    // Exportar usuarios como CSV
    public function exportUsers(): StreamedResponse
    {
        $rows = $this->buildUserTableData();
        $filename = 'usuarios_' . Carbon::now()->format('Ymd_His') . '.csv';

        return response()->streamDownload(function () use ($rows) {
            $out = fopen('php://output', 'w');
            fputcsv($out, ['Nombre', 'Email', 'Tipo', 'Tarjeta/Mascotas', 'Especialidad', 'Fecha registro', 'Estado']);
            foreach ($rows as $u) {
                fputcsv($out, [
                    $u['nombre'] ?? '',
                    $u['email'] ?? '',
                    $u['tipo'] ?? '',
                    $u['detalle'] ?? '',
                    $u['especialidad'] ?? '',
                    $u['fecha_registro'] ?? '',
                    $u['estado'] ?? '',
                ]);
            }
            fclose($out);
        }, $filename, [
            'Content-Type' => 'text/csv; charset=UTF-8',
        ]);
    }

    public function storeVeterinarian(Request $request): JsonResponse|RedirectResponse
    {
        $data = $request->validate([
            'nombre' => ['required', 'string', 'max:150'],
            'cedula' => ['required', 'string', 'max:20'],
            'correo' => ['required', 'email', 'max:150', 'unique:users,email'],
            'clave' => ['required', 'string', 'min:8'],
            'especialidad' => ['required', 'string', 'max:120'],
            'tarjeta_profesional' => ['required', 'string', 'max:30', 'unique:medico_veterinario,tarjeta_profesional_mv'],
            'telefono' => ['required', 'string', 'max:30'],
        ]);

        [$nombre, $apellido] = $this->splitNombre($data['nombre']);

        $user = User::create([
            'name' => $data['nombre'],
            'email' => $data['correo'],
            'password' => Hash::make($data['clave']),
            'role' => 'vet',
        ]);

        $medico = Medico::create([
            'nombre_mv' => $nombre,
            'apell_mv' => $apellido,
            'cedu_mv' => $data['cedula'],
            'tarjeta_profesional_mv' => $data['tarjeta_profesional'],
            'user_id' => $user->id,
            'especialidad' => $data['especialidad'],
            'telefono' => $data['telefono'],
            'estado' => 'activo',
        ]);

        $payload = [
            'message' => 'Veterinario registrado exitosamente.',
            'veterinario' => [
                'id' => $medico->id_mv,
                'nombre' => trim("{$medico->nombre_mv} {$medico->apell_mv}"),
            ],
        ];

        if ($request->expectsJson()) {
            return response()->json($payload, 201);
        }

        return redirect()->route('admin.dashboard')->with('success', $payload['message']);
    }
    private function buildMonthlyRegistrations(): array
    {
        Carbon::setLocale('es');
        $start = Carbon::now()->startOfMonth()->subMonths(5);
        $months = [];

        for ($i = 0; $i < 6; $i++) {
            $current = $start->copy()->addMonths($i);
            $count = User::whereIn('role', ['tutor', 'vet'])
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

        $days = [];
        $registrations = [];
        $appointments = [];

        foreach ($period as $day) {
            $days[] = ucfirst($day->translatedFormat('D'));

            $registrations[] = User::whereIn('role', ['tutor', 'vet'])
                ->whereDate('created_at', $day)
                ->count();

            $appointments[] = Cita::whereDate('fech_cons', $day)->count();
        }

        return [
            'labels' => $days,
            'registrations' => $registrations,
            'appointments' => $appointments,
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
                'icon' => $user->role === 'vet' ? 'fas fa-user-md' : 'fas fa-user-plus',
                'message' => ($user->role === 'vet' ? 'Nuevo veterinario: ' : 'Nuevo tutor: ') . $user->name,
                'timestamp' => $user->created_at,
            ]);
        }

        $recentAppointments = Cita::with(['mascota'])
            ->orderByDesc('fech_cons')
            ->take(5)
            ->get();

        foreach ($recentAppointments as $cita) {
            $feed->push([
                'icon' => 'fas fa-calendar-plus',
                'message' => 'Cita programada para ' . ($cita->mascota?->nom_masc ?? 'Mascota'),
                'timestamp' => Carbon::parse($cita->fech_cons),
            ]);
        }

        return $feed
            ->sortByDesc('timestamp')
            ->take(8)
            ->map(fn ($item) => [
                'icon' => $item['icon'],
                'message' => $item['message'],
                'time' => $item['timestamp'] ? Carbon::parse($item['timestamp'])->diffForHumans() : 'Recientemente',
            ])
            ->values()
            ->all();
    }

    private function buildUserTableData(): array
    {
        Carbon::setLocale('es');

        $tutores = Tutor::with(['mascotas', 'user'])->get()->map(function (Tutor $tutor) {
            $fullName = trim("{$tutor->nomb_tutor} {$tutor->apell_tutor}");
            $createdAt = $tutor->user?->created_at;

            return [
                'id' => $tutor->user?->id,   // 👈 agregado
                'nombre' => $fullName,
                'email' => $tutor->correo_tutor,
                'tipo' => 'Tutor',
                'detalle' => $tutor->mascotas->count() . ' mascota(s)',
                'especialidad' => '-',
                'fecha_registro' => $createdAt ? $createdAt->format('d/m/Y') : 'N/D',
                'estado' => 'Activo',
            ];
        });

        $veterinarios = Medico::with('user')->get()->map(function (Medico $medico) {
            $fullName = trim("{$medico->nombre_mv} {$medico->apell_mv}");
            $createdAt = $medico->user?->created_at;

            return [
                'id' => $medico->user?->id,   // 👈 agregado
                'nombre' => $fullName,
                'email' => $medico->user?->email ?? 'Sin usuario',
                'tipo' => 'Veterinario',
                'detalle' => $medico->tarjeta_profesional_mv ?: 'Sin tarjeta',
                'especialidad' => $medico->especialidad ?? 'Sin especificar',
                'fecha_registro' => $createdAt ? $createdAt->format('d/m/Y') : 'N/D',
                'estado' => ucfirst($medico->estado ?? 'activo'),
            ];
        });

        return $tutores
            ->concat($veterinarios)
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

    private function transformAppointment(Cita $cita): array
    {
        $fecha = Carbon::parse($cita->fech_cons);

        return [
            'id' => $cita->id_cita_medi,
            'fecha' => $fecha->format('d/m/Y'),
            'fecha_iso' => $fecha->toDateString(),
            'mascota' => $cita->mascota?->nom_masc ?? 'Sin mascota',
            'tutor' => $cita->tutor ? trim("{$cita->tutor->nomb_tutor} {$cita->tutor->apell_tutor}") : 'Sin tutor',
            'tutor_email' => $cita->tutor?->correo_tutor ?? '',
            'veterinario' => $cita->medico ? trim("{$cita->medico->nombre_mv} {$cita->medico->apell_mv}") : 'Sin asignar',
            'veterinario_id' => $cita->medico?->id_mv,
            'especialidad' => $cita->medico?->especialidad ?? 'Sin especificar',
            'estado' => $cita->estado ?? 'pendiente',
            'motivo' => $cita->motiv_cons,
        ];
    }

    private function summarizeAppointments(Collection $appointments): array
    {
        $today = Carbon::today();
        $startOfWeek = (clone $today)->startOfWeek();
        $endOfWeek = (clone $startOfWeek)->endOfWeek();

        return [
            'citas_hoy' => $appointments->filter(fn ($cita) => Carbon::parse($cita->fech_cons)->isSameDay($today))->count(),
            'citas_semana' => $appointments->filter(fn ($cita) => Carbon::parse($cita->fech_cons)->between($startOfWeek, $endOfWeek))->count(),
            'confirmadas' => $appointments->filter(fn ($cita) => $cita->estado === 'confirmada')->count(),
            'pendientes' => $appointments->filter(fn ($cita) => ($cita->estado ?? 'pendiente') === 'pendiente')->count(),
        ];
    }

    private function normalizeSpecies(?string $species): string
    {
        if (!$species) {
            return 'Sin especificar';
        }

        return ucfirst(str_replace('_', ' ', $species));
    }

    private function splitNombre(string $nombre): array
    {
        $parts = preg_split('/\s+/', trim($nombre), 2);
        $first = $parts[0] ?? $nombre;
        $last = $parts[1] ?? '';

        return [$first, $last];
    }

    // Genera y descarga un reporte PDF del sistema
    public function exportSystemReportPdf()
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

        return response()->streamDownload(function () use ($dompdf) {
            echo $dompdf->output();
        }, $filename, ['Content-Type' => 'application/pdf']);
    }

    public function updateUser(Request $request, User $user): JsonResponse
    {
        // Editaremos solo nombre y email (cambiar role puede ser más delicado por relaciones)
        $data = $request->validate([
            'name'  => ['required','string','max:150'],
            'email' => ['required','email','max:150', Rule::unique('users','email')->ignore($user->id)],
        ]);

        $user->update($data);

        // Si es vet, opcional: sincronizar nombre en su ficha (si quieres)
        if ($user->role === 'vet') {
            $medico = Medico::where('user_id', $user->id)->first();
            if ($medico) {
                // dividir nombre en nombre/apellido de forma simple
                [$first, $last] = preg_split('/\s+/', trim($data['name']), 2) + [1 => ''];
                $medico->update(['nombre_mv' => $first, 'apell_mv' => $last]);
            }
        }

        return response()->json([
            'message' => 'Usuario actualizado correctamente.',
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
            ],
        ]);
    }

    public function destroyUser(User $user): JsonResponse
    {
        // Protecciones básicas para no romper integridad
        if ($user->role === 'admin') {
            return response()->json([
                'message' => 'No puedes eliminar un administrador desde aquí.'
            ], 422);
        }

        return DB::transaction(function () use ($user) {
            // Si es veterinario, borra su ficha primero (evita FK)
            if ($user->role === 'vet') {
                Medico::where('user_id', $user->id)->delete();
            }

            // Si es tutor, puedes impedir eliminar si tiene mascotas/citas (seguro).
            // Ajusta esto a tu modelo real:
            $tutor = Tutor::where('user_id', $user->id)->first();
            if ($tutor) {
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

}






