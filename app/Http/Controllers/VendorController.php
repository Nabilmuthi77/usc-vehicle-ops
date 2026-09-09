<?php

namespace App\Http\Controllers;

use App\Enums\VendorType;
use App\Models\RentalContract;
use App\Models\Vendor;
use App\Services\FileUploadService;
use App\Support\Permissions;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

/** FR-M1-06 & FR-M4-22 — vendor bengkel/SPBU dan kontrak sewa. */
class VendorController extends Controller implements HasMiddleware
{
    public function __construct(private readonly FileUploadService $uploads) {}

    /** @return array<int, Middleware> */
    public static function middleware(): array
    {
        return [
            new Middleware('can:'.Permissions::MASTER_DATA_VIEW, only: ['index', 'show']),
            new Middleware('can:'.Permissions::MASTER_DATA_MANAGE, except: ['index', 'show']),
        ];
    }

    public function index(Request $request): View
    {
        $vendors = Vendor::query()
            ->when($request->filled('type'), fn ($q) => $q->where('type', $request->string('type')))
            ->when($request->filled('search'), fn ($q) => $q->where('name', 'like', '%'.$request->string('search').'%'))
            ->latest()
            ->paginate(25)
            ->withQueryString();

        return view('vendors.index', [
            'vendors' => $vendors,
            'types' => VendorType::options(),
            // FR-M4-22 — kontrak sewa beserta sisa masa berlaku.
            'contracts' => RentalContract::with(['vendor', 'vehicles'])
                ->orderBy('end_date')
                ->get(),
            'filters' => $request->only(['type', 'search']),
        ]);
    }

    public function create(): View
    {
        return view('vendors.create', ['types' => VendorType::options()]);
    }

    public function store(Request $request): RedirectResponse
    {
        Vendor::create($this->validateVendor($request));

        return redirect()->route('vendors.index')->with('status', 'Vendor ditambahkan.');
    }

    public function show(Vendor $vendor): View
    {
        return view('vendors.show', [
            'vendor' => $vendor->load(['rentalContracts.vehicles']),
            'serviceRequests' => $vendor->serviceRequests()
                ->with('vehicle')
                ->latest()
                ->limit(20)
                ->get(),
            'serviceRecords' => $vendor->serviceRecords()
                ->with('vehicle')
                ->latest()
                ->limit(20)
                ->get(),
        ]);
    }

    public function edit(Vendor $vendor): View
    {
        return view('vendors.edit', ['vendor' => $vendor, 'types' => VendorType::options()]);
    }

    public function update(Request $request, Vendor $vendor): RedirectResponse
    {
        $vendor->update($this->validateVendor($request, $vendor));

        return redirect()->route('vendors.index')->with('status', 'Data vendor diperbarui.');
    }

    public function destroy(Vendor $vendor): RedirectResponse
    {
        $vendor->delete();

        return redirect()->route('vendors.index')->with('status', 'Vendor dinonaktifkan dari daftar.');
    }

    /** FR-M4-22 — pembuatan kontrak sewa. */
    public function createContract(): View
    {
        $prefix = 'KTR/'.date('Y/m').'/';
        $last = \App\Models\RentalContract::where('contract_number', 'like', $prefix.'%')
            ->orderByDesc('contract_number')
            ->value('contract_number');
            
        $sequence = $last ? ((int) substr($last, -3)) + 1 : 1;
        $newNumber = $prefix.str_pad((string) $sequence, 3, '0', STR_PAD_LEFT);

        return view('vendors.create-contract', [
            'vendors' => Vendor::active()->latest()->get(['id', 'name']),
            'newNumber' => $newNumber,
        ]);
    }

    public function storeContract(Request $request): RedirectResponse
    {
        $maxSize = (int) config('usc_vehicle_ops.uploads.max_size_kb', 5120);
        $mimes = 'mimes:'.implode(',', config('usc_vehicle_ops.uploads.document_mimes', ['jpg', 'png', 'pdf']));

        $validated = $request->validate([
            'contract_number' => ['required', 'string', 'max:60', Rule::unique('rental_contracts', 'contract_number')],
            'vendor_id' => ['required', 'exists:vendors,id'],
            'start_date' => ['required', 'date'],
            'end_date' => ['required', 'date', 'after:start_date'],
            'monthly_cost' => ['required', 'numeric', 'min:0'],
            'coverage' => ['nullable', 'array'],
            'pic_name' => ['nullable', 'string', 'max:255'],
            'pic_phone' => ['nullable', 'string', 'max:30'],
            'pic_email' => ['nullable', 'email'],
            'reminder_days' => ['required', 'integer', 'between:1,365'],
            'document' => ['nullable', 'file', $mimes, 'max:'.$maxSize],
            'notes' => ['nullable', 'string', 'max:2000'],
        ]);

        unset($validated['document']);

        if ($request->hasFile('document')) {
            $validated['document_path'] = $this->uploads->store($request->file('document'), 'contracts');
        }

        RentalContract::create($validated + ['is_active' => true]);

        return redirect()->route('vendors.index')->with('status', 'Kontrak sewa ditambahkan.');
    }

    public function showContract(RentalContract $rentalContract): View
    {
        $this->authorizeView();

        return view('vendors.contract', [
            'contract' => $rentalContract->load(['vendor', 'vehicles']),
        ]);
    }

    private function authorizeView(): void
    {
        abort_unless(request()->user()->can(Permissions::MASTER_DATA_VIEW), 403);
    }

    /** @return array<string, mixed> */
    private function validateVendor(Request $request, ?Vendor $vendor = null): array
    {
        return $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'type' => ['required', VendorType::rule()],
            'pic_name' => ['nullable', 'string', 'max:255'],
            'pic_phone' => ['nullable', 'string', 'max:30'],
            'pic_email' => ['nullable', 'email', 'max:255'],
            'address' => ['nullable', 'string', 'max:2000'],
            'notes' => ['nullable', 'string', 'max:2000'],
            'is_active' => ['required', 'boolean'],
        ], [], [
            'name' => 'nama vendor',
            'type' => 'jenis vendor',
            'pic_email' => 'email PIC',
        ]);
    }
}
