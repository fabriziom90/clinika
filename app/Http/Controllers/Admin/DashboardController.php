<?php

namespace App\Http\Controllers\Admin;

use App\Enums\AppointmentStatus;
use App\Http\Controllers\Controller;
use App\Models\Appointment;
use App\Models\Doctor;
use App\Models\InventoryDrug;
use App\Models\InventoryProduct;
use App\Models\Invoice;
use App\Models\Patient;
use App\Services\AuditActivityFormatter;
use Carbon\Carbon;
use Inertia\Inertia;
use OwenIt\Auditing\Models\Audit;

class DashboardController extends Controller
{
    public function index(AuditActivityFormatter $formatter)
    {
        $user = auth()->user();

        if ($user->hasRole(['superadmin', 'secretary'])) {
            return $this->administrativeDashboard($formatter);
        }

        if ($user->hasRole('doctor')) {
            return $this->doctorDashboard($formatter);
        }

        if ($user->hasRole('nurse')) {
            return $this->nurseDashboard($formatter);
        }

        abort(403);
    }

    private function administrativeDashboard(AuditActivityFormatter $formatter)
    {
        $today = Carbon::today();

        /*
         * PATIENTS
         */
        $patients = Patient::with([
            'lastAppointment',
            'nextAppointment',
        ])->get();

        /*
         * APPOINTMENTS
         */
        $appointments = Appointment::whereBetween('start_time', [
            $today->copy()->startOfDay(),
            $today->copy()->endOfDay(),
        ])
            ->with([
                'patient',
                'doctor',
                'service',
            ])
            ->get();

        /*
         * DOCTORS
         */
        $doctors = Doctor::with([
            'user',
            'specialty',
            'appointments' => function ($query) use ($today) {
                $query->whereBetween('start_time', [
                    $today->copy()->startOfDay(),
                    $today->copy()->endOfDay(),
                ]);
            },
        ])
            ->get()
            ->map(function ($doctor) {
                $appointments = $doctor->appointments;

                return [
                    'id' => $doctor->id,
                    'name' => $doctor->user->name,
                    'surname' => $doctor->user->surname,
                    'specialty' => $doctor->specialty?->name,
                    'appointments_count' => $appointments->count(),
                    'completed_count' => $appointments
                        ->where('status', AppointmentStatus::Completed)
                        ->count(),
                    'remaining_count' => $appointments
                        ->where('status', AppointmentStatus::Scheduled)
                        ->count(),
                ];
            });

        /*
         * INVOICES
         */
        $invoices = Invoice::all();

        $invoiceStats = Invoice::query()
            ->selectRaw("
                COUNT(*) as total_count,
                SUM(CASE WHEN status IN ('issued', 'paid') THEN total ELSE 0 END) as issued_total,
                SUM(CASE WHEN status = 'paid' THEN total ELSE 0 END) as paid_total,
                SUM(CASE WHEN status = 'issued' THEN total ELSE 0 END) as outstanding_total,
                SUM(CASE WHEN status = 'cancelled' THEN total ELSE 0 END) as cancelled_total,
                SUM(CASE WHEN status = 'draft' THEN total ELSE 0 END) as draft_total
            ")
            ->first();

        /*
         * INVOICE CHART
         */
        $invoiceChart = $this->getInvoiceChart();

        /*
         * RECENT ACTIVITIES
         */
        $recentActivities = Audit::with('user')
            ->latest()
            ->limit(100)
            ->get()
            ->map(fn ($audit) => $formatter->format($audit))
            ->filter()
            ->take(10)
            ->values();

        /*
         * PENDING OPERATIONS
         */
        $pendingOperations = $this->getPendingOperations();

        return Inertia::render('Dashboard', [
            'invoices' => $invoices,
            'patients' => $patients,
            'doctors' => $doctors,
            'appointments' => $appointments,
            'invoiceStats' => $invoiceStats,
            'invoiceChart' => $invoiceChart,
            'recentActivities' => $recentActivities,
            'pendingOperations' => $pendingOperations,
        ]);
    }

    private function doctorDashboard(AuditActivityFormatter $formatter)
    {
        $today = Carbon::today();

        /*
         * Get doctor for authenticated user
         */
        $doctor = Doctor::where('user_id', auth()->id())->firstOrFail();

        /*
         * doctor appointments
         */
        $appointments = Appointment::where('doctor_id', $doctor->id)
            ->whereBetween('start_time', [
                $today->copy()->startOfDay(),
                $today->copy()->endOfDay(),
            ])
            ->with([
                'patient',
                'doctor',
                'service',
            ])
            ->get();

        /*
         * doctor patients in appointments
         */
        $patients = Patient::whereHas('appointments', function ($query) use ($doctor) {
            $query->where('doctor_id', $doctor->id);
        })
            ->with([
                'lastAppointment',
                'nextAppointment',
            ])
            ->get();

        /*
         * doctor activities
         */
        $recentActivities = Audit::with('user')
            ->where('user_id', auth()->id())
            ->latest()
            ->limit(100)
            ->get()
            ->map(fn ($audit) => $formatter->format($audit))
            ->filter()
            ->take(10)
            ->values();

        return Inertia::render('Dashboard', [
            'patients' => $patients,
            'appointments' => $appointments,
            'recentActivities' => $recentActivities,
            'invoiceStats' => null,
            'invoiceChart' => [],
            'pendingOperations' => [],
        ]);
    }

    private function nurseDashboard(AuditActivityFormatter $formatter)
    {
        $today = Carbon::today();

        /*
         * today's appointments
         */
        $appointments = Appointment::whereBetween('start_time', [
            $today->copy()->startOfDay(),
            $today->copy()->endOfDay(),
        ])
            ->with([
                'patient',
                'doctor',
                'service',
            ])
            ->get();

        /*
         * nurse activities
         */
        $recentActivities = Audit::with('user')
            ->where('user_id', auth()->id())
            ->latest()
            ->limit(100)
            ->get()
            ->map(fn ($audit) => $formatter->format($audit))
            ->filter()
            ->take(10)
            ->values();

        return Inertia::render('Dashboard', [
            'appointments' => $appointments,
            'recentActivities' => $recentActivities,
            'patients' => [],
            'doctors' => [],
            'invoiceStats' => null,
            'invoiceChart' => [],
            'pendingOperations' => [],
        ]);
    }

    private function getInvoiceChart()
    {
        $from = now()->subMonths(11)->startOfMonth();

        $monthlyIssued = Invoice::query()
            ->selectRaw('
                YEAR(created_at) as year,
                MONTH(created_at) as month,
                SUM(total) as total
            ')
            ->whereIn('status', ['issued', 'paid'])
            ->where('created_at', '>=', $from)
            ->groupByRaw('YEAR(created_at), MONTH(created_at)')
            ->orderBy('year')
            ->orderBy('month')
            ->get()
            ->keyBy(fn ($item) => "{$item->year}-{$item->month}");

        $monthlyPaid = Invoice::query()
            ->selectRaw('
                YEAR(updated_at) as year,
                MONTH(updated_at) as month,
                SUM(total) as total
            ')
            ->where('status', 'paid')
            ->where('updated_at', '>=', $from)
            ->groupByRaw('YEAR(updated_at), MONTH(updated_at)')
            ->orderBy('year')
            ->orderBy('month')
            ->get()
            ->keyBy(fn ($item) => "{$item->year}-{$item->month}");

        return collect(range(0, 11))->map(function ($i) use ($from, $monthlyIssued, $monthlyPaid) {
            $date = $from->copy()->addMonths($i);

            $key = "{$date->year}-{$date->month}";

            $issuedTotal = (float) ($monthlyIssued->get($key)?->total ?? 0);
            $paidTotal = (float) ($monthlyPaid->get($key)?->total ?? 0);

            return [
                'year' => $date->year,
                'month' => $date->month,
                'label' => $date->translatedFormat('M Y'),
                'issued' => $issuedTotal,
                'paid' => $paidTotal,
                'collection_rate' => $issuedTotal > 0
                    ? round(($paidTotal / $issuedTotal) * 100, 2)
                    : 0,
            ];
        });
    }

    private function getPendingOperations()
    {
        $pendingOperations = [];

        $today = Carbon::today();

        /*
         * today appointments
         */
        $pendingAppointmentsToday = Appointment::whereBetween('start_time', [
            $today->copy()->startOfDay(),
            $today->copy()->endOfDay(),
        ])
            ->whereIn('status', [
                AppointmentStatus::Scheduled,
            ])
            ->count();

        if ($pendingAppointmentsToday > 0) {
            $pendingOperations[] = [
                'type' => 'warning',
                'icon' => 'calendar',
                'count' => $pendingAppointmentsToday,
                'message' => $pendingAppointmentsToday === 1
                    ? '1 appuntamento di oggi ancora da gestire'
                    : "{$pendingAppointmentsToday} appuntamenti di oggi ancora da gestire",
                'route' => route('admin.appointments.index'),
            ];
        }

        /*
         * not done past appointments
         */
        $overdueAppointments = Appointment::where('start_time', '<', now())
            ->where('status', AppointmentStatus::Scheduled)
            ->count();

        if ($overdueAppointments > 0) {
            $pendingOperations[] = [
                'type' => 'danger',
                'icon' => 'calendar-x',
                'count' => $overdueAppointments,
                'message' => $overdueAppointments === 1
                    ? '1 appuntamento passato non ancora gestito'
                    : "{$overdueAppointments} appuntamenti passati non ancora gestiti",
                'route' => route('admin.appointments.index'),
            ];
        }

        /*
         * not done invoices
         */
        $unpaidInvoices = Invoice::where('status', 'issued')->count();

        if ($unpaidInvoices > 0) {
            $pendingOperations[] = [
                'type' => 'warning',
                'icon' => 'receipt',
                'count' => $unpaidInvoices,
                'message' => $unpaidInvoices === 1
                    ? '1 fattura emessa non ancora saldata'
                    : "{$unpaidInvoices} fatture emesse non ancora saldate",
                'route' => route('admin.invoices.index'),
            ];
        }

        /*
         * warehouse
         */
        $minimumStock = 1;

        $lowStockProducts = InventoryProduct::where('units', '<=', $minimumStock)
            ->count();

        if ($lowStockProducts > 0) {
            $pendingOperations[] = [
                'type' => 'danger',
                'icon' => 'box',
                'count' => $lowStockProducts,
                'message' => $lowStockProducts === 1
                    ? '1 prodotto sotto la scorta minima'
                    : "{$lowStockProducts} prodotti sotto la scorta minima",
                'route' => route('admin.inventory-products.index'),
            ];
        }

        $lowStockDrugs = InventoryDrug::where('units', '<=', $minimumStock)
            ->count();

        if ($lowStockDrugs > 0) {
            $pendingOperations[] = [
                'type' => 'danger',
                'icon' => 'capsule',
                'count' => $lowStockDrugs,
                'message' => $lowStockDrugs === 1
                    ? '1 farmaco sotto la scorta minima'
                    : "{$lowStockDrugs} farmaci sotto la scorta minima",
                'route' => route('admin.inventory-drugs.index'),
            ];
        }

        return $pendingOperations;
    }
}
