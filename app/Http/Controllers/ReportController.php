<?php

namespace App\Http\Controllers;

use App\Exports\ArrayReportExport;
use App\Models\Department;
use App\Services\CostReportService;
use App\Services\ReimbursementService;
use App\Support\Permissions;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\View\View;
use Maatwebsite\Excel\Facades\Excel;

/** Modul M6 — laporan & export (PRD §7.6). */
class ReportController extends Controller
{
    /** Definisi kolom tiap jenis laporan untuk tampilan maupun export. */
    private const COLUMNS = [
        'cost' => [
            'plate_number' => 'Nomor Polisi',
            'vehicle' => 'Kendaraan',
            'department' => 'Departemen',
            'fuel_cost' => 'Biaya BBM',
            'toll_cost' => 'Biaya Tol',
            'service_cost' => 'Biaya Servis',
            'total_cost' => 'Total Biaya',
            'distance_km' => 'Jarak (km)',
            'cost_per_km' => 'Biaya per km',
            'vendor_borne_cost' => 'Biaya Ditanggung Vendor',
        ],
        'utilization' => [
            'vehicle' => 'Kendaraan',
            'days_used' => 'Hari Terpakai',
            'total_days' => 'Total Hari',
            'utilization_percent' => 'Utilisasi (%)',
            'trips' => 'Jumlah Perjalanan',
            'distance_km' => 'Jarak (km)',
        ],
        'driver' => [
            'driver' => 'Driver',
            'trips' => 'Jumlah Perjalanan',
            'distance_km' => 'Jarak (km)',
            'fuel_claims' => 'Jumlah Klaim BBM',
            'avg_consumption' => 'Rata-rata Konsumsi (km/L)',
        ],
        'outstanding' => [
            'claimant' => 'Pengaju',
            'item_count' => 'Jumlah Klaim',
            'total_amount' => 'Total Nominal',
            'age_days' => 'Umur Klaim (hari)',
        ],
    ];

    public function __construct(
        private readonly CostReportService $costs,
        private readonly ReimbursementService $reimbursements,
    ) {}

    /** FR-M6-03 s.d. FR-M6-05 — laporan dengan filter periode & departemen. */
    public function index(Request $request): View
    {
        abort_unless($request->user()->can(Permissions::REPORT_VIEW), 403);

        $costFrom = $request->input('cost_from', Carbon::today()->startOfMonth()->toDateString());
        $costTo = $request->input('cost_to', Carbon::today()->endOfMonth()->toDateString());
        $costDepartmentId = $request->integer('cost_department_id') ?: null;

        $activityFrom = $request->input('activity_from', Carbon::today()->startOfMonth()->toDateString());
        $activityTo = $request->input('activity_to', Carbon::today()->endOfMonth()->toDateString());
        $activityDepartmentId = $request->integer('activity_department_id') ?: null;

        $costByVehicle = collect($this->costs->costByVehicle(
            $costFrom,
            $costTo,
            $costDepartmentId,
        ));

        return view('reports.index', [
            'costByVehicle' => $costByVehicle,
            'utilization' => $this->costs->utilization($activityFrom, $activityTo, $activityDepartmentId),
            'driverActivity' => $this->costs->driverActivity($activityFrom, $activityTo, $activityDepartmentId),
            'outstanding' => $this->reimbursements->outstandingReport($activityDepartmentId),
            'totals' => [
                'bbm' => $costByVehicle->sum('fuel_cost'),
                'tol' => $costByVehicle->sum('toll_cost'),
                'servis' => $costByVehicle->sum('service_cost'),
                'total' => $costByVehicle->sum('total_cost'),
                'km' => $costByVehicle->sum('distance_km'),
            ],
            'departments' => Department::active()->orderBy('name')->get(['id', 'name']),
            'costFilters' => ['from' => $costFrom, 'to' => $costTo, 'department_id' => $costDepartmentId],
            'activityFilters' => ['from' => $activityFrom, 'to' => $activityTo, 'department_id' => $activityDepartmentId],
        ]);
    }

    /** FR-M6-05 — export Excel untuk seluruh jenis laporan. */
    public function exportExcel(Request $request)
    {
        abort_unless($request->user()->can(Permissions::REPORT_EXPORT), 403);

        [$from, $to] = $this->period($request);
        $type = $request->string('type')->toString() ?: 'cost';

        abort_unless(isset(self::COLUMNS[$type]), 404);

        $filename = sprintf('laporan-%s-%s-sd-%s.xlsx', $type, $from, $to);

        $rows = $this->dataFor($type, $from, $to, $request->integer('department_id') ?: null);
        
        $totals = null;
        if ($type === 'cost') {
            $col = collect($rows);
            $totals = [
                'fuel_cost' => $col->sum('fuel_cost'),
                'toll_cost' => $col->sum('toll_cost'),
                'service_cost' => $col->sum('service_cost'),
                'total_cost' => $col->sum('total_cost'),
                'distance_km' => $col->sum('distance_km'),
            ];
        }

        return Excel::download(
            new ArrayReportExport(
                $rows,
                self::COLUMNS[$type],
                $this->titleFor($type),
                $totals,
            ),
            $filename,
        );
    }

    /** FR-M6-05 — export PDF untuk seluruh jenis laporan. */
    public function exportPdf(Request $request)
    {
        abort_unless($request->user()->can(Permissions::REPORT_EXPORT), 403);

        [$from, $to] = $this->period($request);
        $type = $request->string('type')->toString() ?: 'cost';

        abort_unless(isset(self::COLUMNS[$type]), 404);

        $rows = $this->dataFor($type, $from, $to, $request->integer('department_id') ?: null);
        
        $totals = null;
        if ($type === 'cost') {
            $col = collect($rows);
            $totals = [
                'fuel_cost' => $col->sum('fuel_cost'),
                'toll_cost' => $col->sum('toll_cost'),
                'service_cost' => $col->sum('service_cost'),
                'total_cost' => $col->sum('total_cost'),
                'distance_km' => $col->sum('distance_km'),
            ];
        }

        $pdf = Pdf::loadView('pdf.report', [
            'title' => $this->titleFor($type),
            'columns' => self::COLUMNS[$type],
            'rows' => $rows,
            'period' => ['from' => $from, 'to' => $to],
            'company' => config('usc_vehicle_ops.company'),
            'totals' => $totals,
        ])->setPaper('a4', 'landscape');

        return $pdf->stream(sprintf('laporan-%s-%s-sd-%s.pdf', $type, $from, $to));
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function dataFor(string $type, string $from, string $to, ?int $departmentId): array
    {
        return match ($type) {
            'utilization' => $this->costs->utilization($from, $to, $departmentId),
            'driver' => $this->costs->driverActivity($from, $to, $departmentId),
            'outstanding' => $this->reimbursements->outstandingReport($departmentId),
            default => $this->costs->costByVehicle($from, $to, $departmentId),
        };
    }

    private function titleFor(string $type): string
    {
        return match ($type) {
            'utilization' => 'Laporan Utilisasi Kendaraan',
            'driver' => 'Laporan Aktivitas Driver',
            'outstanding' => 'Laporan Outstanding Reimbursement',
            default => 'Laporan Biaya Operasional Kendaraan',
        };
    }

    /**
     * Periode laporan; default bulan berjalan.
     *
     * @return array{0: string, 1: string}
     */
    private function period(Request $request): array
    {
        return [
            $request->input('from', Carbon::today()->startOfMonth()->toDateString()),
            $request->input('to', Carbon::today()->endOfMonth()->toDateString()),
        ];
    }
}
