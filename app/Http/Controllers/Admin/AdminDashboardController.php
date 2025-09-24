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
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;

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

    public function statistics(): JsonResponse
    {
        Carbon::setLocale('es');

        return response()->json([
            'monthly' => $this->buildMonthlyRegistrations(),
            'species' => $this->buildSpeciesDistribution(),
            'activity' => $this->buildWeeklyActivity(),
        ]);
    }

    public function activities(): JsonResponse
    {
        return response()->json(['activities' => $this->buildActivityFeed()]);
    }

    public function users(): JsonResponse
    {
        return response()->json(['users' => $this->buildUserTableData()]);
    }

    public function appointments(Request $request): JsonResponse
    {
        $filters = $request->validate([
            'estado' => ['nullable', 'in:pendiente,confirmada,cancelada,completada'],
            'veterinario' => ['nullable', 'integer', 'exists:medico_veterinario,id_mv'],
            'fecha' => ['nullable', 'date'],
        ]);

        $appointments = $this->buildAppointmentQuery($filters)
            ->with(['mascota', 'tutor', 'medico'])
            ->orderBy('fech_cons')
            ->get();

        $transformed = $appointments->map(fn (Cita $cita) => $this->transformAppointment($cita));
        $summary = $this->summarizeAppointments($appointments);
        $globalSummary = $this->summarizeAppointments(
            Cita::with(['mascota', 'tutor', 'medico'])->get()
        );

        return response()->json([
            'appointments' => $transformed,
            'summary' => $summary,
            'globalSummary' => $globalSummary,
        ]);
    }

    public function updateAppointmentStatus(Request $request, Cita $cita): JsonResponse
    {
        $data = $request->validate([
            'estado' => ['required', 'in:pendiente,confirmada,cancelada,completada'],
        ]);

        $cita->estado = $data['estado'];
        $cita->save();

        return response()->json([
            'message' => 'Estado actualizado correctamente.',
            'appointment' => $this->transformAppointment($cita->load(['mascota', 'tutor', 'medico'])),
        ]);
    }

    public function exportAppointments(Request $request): StreamedResponse
    {
        $filters = $request->validate([
            'estado' => ['nullable', 'in:pendiente,confirmada,cancelada,completada'],
            'veterinario' => ['nullable', 'integer', 'exists:medico_veterinario,id_mv'],
            'fecha' => ['nullable', 'date'],
        ]);

        $appointments = $this->buildAppointmentQuery($filters)
            ->with(['mascota', 'tutor', 'medico'])
            ->orderBy('fech_cons')
            ->get();

        $filename = 'citas_orangehearth_' . Carbon::now()->format('Ymd_His') . '.csv';

        return Response::streamDownload(function () use ($appointments) {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, ['Fecha', 'Tutor', 'Mascota', 'Veterinario', 'Especialidad', 'Estado', 'Motivo']);
            foreach ($appointments as $cita) {
                fputcsv($handle, [
                    $cita->fech_cons,
                    $cita->tutor ? trim("{$cita->tutor->nomb_tutor} {$cita->tutor->apell_tutor}") : 'Sin tutor',
                    optional($cita->mascota)->nom_masc ?? 'Sin mascota',
                    $cita->medico ? trim("{$cita->medico->nombre_mv} {$cita->medico->apell_mv}") : 'Sin asignar',
                    $cita->medico?->especialidad ?? 'Sin especificar',
                    $cita->estado ?? 'pendiente',
                    $cita->motiv_cons,
                ]);
            }
            fclose($handle);
        }, $filename, ['Content-Type' => 'text/csv; charset=UTF-8']);
    }

    public function exportUsers(): StreamedResponse
    {
        $filename = 'usuarios_orangehearth_' . Carbon::now()->format('Ymd_His') . '.csv';
        $users = $this->buildUserTableData();

        return Response::streamDownload(function () use ($users) {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, ['Nombre', 'Email', 'Tipo', 'Tarjeta/Mascotas', 'Especialidad', 'Fecha Registro', 'Estado']);
            foreach ($users as $user) {
                fputcsv($handle, [
                    $user['nombre'],
                    $user['email'],
                    $user['tipo'],
                    $user['detalle'],
                    $user['especialidad'],
                    $user['fecha_registro'],
                    $user['estado'],
                ]);
            }
            fclose($handle);
        }, $filename, ['Content-Type' => 'text/csv; charset=UTF-8']);
    }

    public function exportData(): StreamedResponse
    {
        $payload = [
            'generated_at' => Carbon::now()->toIso8601String(),
            'totals' => [
                'tutores' => Tutor::count(),
                'veterinarios' => Medico::count(),
                'mascotas' => Mascota::count(),
                'citas' => Cita::count(),
            ],
            'tutores' => Tutor::with(['mascotas', 'citas'])->get(),
            'veterinarios' => Medico::withCount('citas')->get(),
        ];

        $filename = 'datos_orangehearth_' . Carbon::now()->format('Ymd_His') . '.json';

        return Response::streamDownload(function () use ($payload) {
            echo json_encode($payload, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
        }, $filename, ['Content-Type' => 'application/json; charset=UTF-8']);
    }

    public function generateReport(Request $request): StreamedResponse
    {
        $filters = $request->validate([
            'estado' => ['nullable', 'in:pendiente,confirmada,cancelada,completada'],
            'veterinario' => ['nullable', 'integer', 'exists:medico_veterinario,id_mv'],
            'fecha' => ['nullable', 'date'],
        ]);

        $appointments = $this->buildAppointmentQuery($filters)
            ->with(['mascota', 'tutor', 'medico'])
            ->orderBy('fech_cons')
            ->get();

        $summary = $this->summarizeAppointments($appointments);

        $lines = [];
        $lines[] = '===== REPORTE DEL SISTEMA ORANGEHEARTH =====';
        $lines[] = 'Generado: ' . Carbon::now()->format('d/m/Y H:i');
        $lines[] = '';

        if ($filters) {
            $lines[] = 'Filtros aplicados:';
            if (!empty($filters['estado'])) {
                $lines[] = '- Estado: ' . ucfirst($filters['estado']);
            }
            if (!empty($filters['veterinario'])) {
                $medico = Medico::find($filters['veterinario']);
                if ($medico) {
                    $lines[] = '- Veterinario: ' . trim("{$medico->nombre_mv} {$medico->apell_mv}");
                }
            }
            if (!empty($filters['fecha'])) {
                $lines[] = '- Fecha: ' . Carbon::parse($filters['fecha'])->format('d/m/Y');
            }
            $lines[] = '';
        }

        $lines[] = 'Total de citas analizadas: ' . $appointments->count();
        $lines[] = 'Citas hoy: ' . $summary['citas_hoy'];
        $lines[] = 'Esta semana: ' . $summary['citas_semana'];
        $lines[] = 'Confirmadas: ' . $summary['confirmadas'];
        $lines[] = 'Pendientes: ' . $summary['pendientes'];
        $lines[] = '';

        $states = $appointments->groupBy(fn ($cita) => $cita->estado ?? 'pendiente')->map->count();
        $lines[] = 'DistribuciÃ³n por estado:';
        foreach ($states as $estado => $count) {
            $lines[] = sprintf(' - %s: %d', ucfirst($estado), $count);
        }

        $lines[] = '';
        $lines[] = 'Top veterinarios por nÃºmero de citas:';
        $byVet = $appointments->groupBy(fn ($cita) => $cita->medico?->id_mv ?: 0)
            ->map(function ($group) {
                $medico = $group->first()->medico;
                return [
                    'nombre' => $medico ? trim("{$medico->nombre_mv} {$medico->apell_mv}") : 'Sin asignar',
                    'total' => $group->count(),
                ];
            })
            ->sortByDesc('total')
            ->take(5);

        foreach ($byVet as $data) {
            $lines[] = sprintf(' - %s: %d', $data['nombre'], $data['total']);
        }

        $filename = 'reporte_orangehearth_' . Carbon::now()->format('Ymd_His') . '.txt';

        return Response::streamDownload(function () use ($lines) {
            echo implode(PHP_EOL, $lines);
        }, $filename, ['Content-Type' => 'text/plain; charset=UTF-8']);
    }

    public function storeVeterinarian(Request $request): JsonResponse
    {
        $data = $request->validate([
            'nombre' => ['required', 'string', 'max:150'],
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

        $identificador = strtoupper($data['tarjeta_profesional']);

        $medico = Medico::create([
            'nombre_mv' => $nombre,
            'apell_mv' => $apellido,
            'cedu_mv' => $identificador,
            'tarjeta_profesional_mv' => $identificador,
            'user_id' => $user->id,
            'especialidad' => $data['especialidad'],
            'telefono' => $data['telefono'],
            'estado' => 'activo',
        ]);

        return response()->json([
            'message' => 'Veterinario registrado exitosamente.',
            'veterinario' => [
                'id' => $medico->id_mv,
                'nombre' => trim("{$medico->nombre_mv} {$medico->apell_mv}"),
            ],
        ], 201);
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
}

