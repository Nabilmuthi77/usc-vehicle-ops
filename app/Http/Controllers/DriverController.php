<?php

namespace App\Http\Controllers;

use App\Models\Driver;
use App\Models\User;
use App\Support\Permissions;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

/** FR-M1-04 — CRUD driver beserta pengingat SIM kedaluwarsa. */
class DriverController extends Controller implements HasMiddleware
{
    /**
     * PRD §5.2 — melihat master data terbuka bagi peran ber-permission
     * lihat; perubahan hanya oleh pengelola master data.
     *
     * @return array<int, Middleware>
     */
    public static function middleware(): array
    {
        return [
            new Middleware('can:'.Permissions::MASTER_DATA_VIEW, only: ['index', 'show']),
            new Middleware(
                'can:'.Permissions::MASTER_DATA_MANAGE,
                only: ['create', 'store', 'edit', 'update', 'destroy'],
            ),
        ];
    }

    public function index(Request $request): View
    {
        $drivers = Driver::query()
            ->with('user')
            ->when($request->filled('search'), function ($q) use ($request) {
                $term = $request->string('search')->toString();
                $q->where(function ($query) use ($term) {
                    $query->where('name', 'like', "%{$term}%")
                        ->orWhere('employee_id', 'like', "%{$term}%")
                        ->orWhere('license_number', 'like', "%{$term}%")
                        ->orWhere('license_type', 'like', "%{$term}%")
                        ->orWhere('license_expiry', 'like', "%{$term}%")
                        ->orWhere('phone', 'like', "%{$term}%");
                        
                    $termLower = strtolower(trim($term));
                    if ($termLower === 'aktif') {
                        $query->orWhere('is_active', true);
                    } elseif ($termLower === 'nonaktif') {
                        $query->orWhere('is_active', false);
                    }
                });
            })
            ->when($request->filled('is_active'), fn ($q) => $q->where('is_active', $request->boolean('is_active')))
            ->latest()
            ->paginate(10)
            ->withQueryString();

        return view('drivers.index', [
            'drivers' => $drivers,
            'filters' => $request->only(['search', 'is_active']),
        ]);
    }

    public function create(): View
    {
        return view('drivers.create', ['users' => $this->assignableUsers()]);
    }

    public function store(Request $request): RedirectResponse
    {
        $driver = Driver::create($this->validateDriver($request));

        return redirect()
            ->route('drivers.index')
            ->with('status', "Driver {$driver->name} ditambahkan.");
    }

    public function show(Driver $driver): View
    {
        return view('drivers.show', [
            'driver' => $driver->load('user'),
            'recentBookings' => $driver->bookings()
                ->with(['vehicle', 'requester'])
                ->orderByDesc('booking_date')
                ->limit(10)
                ->get(),
        ]);
    }

    public function edit(Driver $driver): View
    {
        return view('drivers.edit', [
            'driver' => $driver,
            'users' => $this->assignableUsers($driver),
        ]);
    }

    public function update(Request $request, Driver $driver): RedirectResponse
    {
        $driver->update($this->validateDriver($request, $driver));

        return redirect()->route('drivers.index')->with('status', 'Data driver diperbarui.');
    }

    public function destroy(Driver $driver): RedirectResponse
    {
        $driver->delete();

        return redirect()->route('drivers.index')->with('status', 'Driver dinonaktifkan dari daftar.');
    }

    /** @return array<string, mixed> */
    private function validateDriver(Request $request, ?Driver $driver = null): array
    {
        return $request->validate([
            'user_id' => ['nullable', 'exists:users,id'],
            'name' => ['required', 'string', 'max:255'],
            'employee_id' => [
                'nullable', 'string', 'max:40',
                Rule::unique('drivers', 'employee_id')->ignore($driver?->getKey())->whereNull('deleted_at'),
            ],
            'license_number' => ['required', 'string', 'max:40'],
            'license_type' => ['required', 'string', 'max:40'],
            'license_expiry' => ['required', 'date'],
            'phone' => ['nullable', 'string', 'max:30'],
            'notes' => ['nullable', 'string', 'max:2000'],
            'is_active' => ['required', 'boolean'],
        ], [], [
            'name' => 'nama driver',
            'employee_id' => 'NIK / kode driver',
            'license_number' => 'nomor SIM',
            'license_type' => 'jenis SIM',
            'license_expiry' => 'masa berlaku SIM',
        ]);
    }

    /** Akun pengguna berperan Driver yang belum terhubung ke data driver. */
    private function assignableUsers(?Driver $driver = null)
    {
        return User::query()
            ->active()
            ->where(function ($query) use ($driver) {
                $query->whereDoesntHave('driver')
                    ->when($driver?->user_id, fn ($q, $id) => $q->orWhere('id', $id));
            })
            ->latest()
            ->get(['id', 'name', 'email']);
    }
}
