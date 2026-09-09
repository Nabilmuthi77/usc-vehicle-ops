<?php

namespace App\Http\Controllers;

use App\Models\Department;
use App\Support\Permissions;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

/** FR-M1-05 — CRUD departemen / cost center. */
class DepartmentController extends Controller implements HasMiddleware
{
    /** @return array<int, Middleware> */
    public static function middleware(): array
    {
        return [
            new Middleware('can:'.Permissions::MASTER_DATA_VIEW, only: ['index']),
            new Middleware(
                'can:'.Permissions::MASTER_DATA_MANAGE,
                only: ['create', 'store', 'edit', 'update', 'destroy'],
            ),
        ];
    }

    public function index(Request $request): View
    {
        $departments = Department::query()
            ->withCount(['vehicles', 'users'])
            ->when($request->filled('search'), function ($q) use ($request) {
                $term = $request->string('search')->toString();
                $q->where(function ($query) use ($term) {
                    $query->where('name', 'like', "%{$term}%")
                        ->orWhere('code', 'like', "%{$term}%")
                        ->orWhere('pic_name', 'like', "%{$term}%")
                        ->orWhere('pic_phone', 'like', "%{$term}%")
                        ->orWhereHas('vehicles', fn($v) => $v->where('plate_number', 'like', "%{$term}%"))
                        ->orWhereHas('users', fn($u) => $u->where('name', 'like', "%{$term}%"));

                    $termLower = strtolower(trim($term));
                    if ($termLower === 'aktif') {
                        $query->orWhere('is_active', true);
                    } elseif ($termLower === 'nonaktif') {
                        $query->orWhere('is_active', false);
                    }
                });
            })
            ->latest()
            ->paginate(10)
            ->withQueryString();

        return view('departments.index', [
            'departments' => $departments,
            'filters' => $request->only('search'),
        ]);
    }

    public function create(): View
    {
        return view('departments.create');
    }

    public function store(Request $request): RedirectResponse
    {
        Department::create($this->validateDepartment($request));

        return redirect()->route('departments.index')->with('status', 'Departemen ditambahkan.');
    }

    public function edit(Department $department): View
    {
        return view('departments.edit', ['department' => $department]);
    }

    public function update(Request $request, Department $department): RedirectResponse
    {
        $department->update($this->validateDepartment($request, $department));

        return redirect()->route('departments.index')->with('status', 'Departemen diperbarui.');
    }

    public function destroy(Department $department): RedirectResponse
    {
        // Departemen yang masih memegang kendaraan tidak boleh dihapus agar
        // alokasi biaya historis tetap dapat ditelusuri.
        if ($department->vehicles()->exists()) {
            return back()->withErrors([
                'department' => 'Departemen masih memegang kendaraan dan tidak dapat dihapus.',
            ]);
        }

        $department->delete();

        return redirect()->route('departments.index')->with('status', 'Departemen dihapus.');
    }

    /** @return array<string, mixed> */
    private function validateDepartment(Request $request, ?Department $department = null): array
    {
        return $request->validate([
            'code' => [
                'required', 'string', 'max:20',
                Rule::unique('departments', 'code')->ignore($department?->getKey())->whereNull('deleted_at'),
            ],
            'name' => ['required', 'string', 'max:255'],
            'pic_name' => ['nullable', 'string', 'max:255'],
            'pic_phone' => ['nullable', 'string', 'max:30'],
            'description' => ['nullable', 'string', 'max:2000'],
            'is_active' => ['required', 'boolean'],
        ], [], [
            'code' => 'kode departemen',
            'name' => 'nama departemen',
            'pic_name' => 'nama PIC',
        ]);
    }
}
