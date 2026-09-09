<?php

namespace App\Http\Controllers;

use App\Models\Department;
use App\Models\User;
use App\Support\Permissions;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;
use Illuminate\View\View;
use Spatie\Permission\Models\Role;

/** FR-M1-07 — CRUD user dan penetapan peran. */
class UserController extends Controller
{
    public function index(Request $request): View
    {
        $this->authorize('viewAny', User::class);

        $users = User::query()
            ->with(['department', 'roles'])
            ->when($request->filled('search'), function ($q) use ($request) {
                $term = $request->string('search')->toString();
                $q->where(function ($query) use ($term) {
                    $query->where('name', 'like', "%{$term}%")
                        ->orWhere('email', 'like', "%{$term}%")
                        ->orWhere('username', 'like', "%{$term}%");
                });
            })
            ->when($request->filled('role'), fn ($q) => $q->role($request->string('role')->toString()))
            ->latest()
            ->paginate(25)
            ->withQueryString();

        return view('users.index', [
            'users' => $users,
            'roles' => Role::withCount('users')->latest()->get(),
            'filters' => $request->only(['search', 'role']),
        ]);
    }

    public function create(): View
    {
        $this->authorize('create', User::class);

        return view('users.create', $this->formOptions());
    }

    public function store(Request $request): RedirectResponse
    {
        $this->authorize('create', User::class);

        $validated = $this->validateUser($request);

        $user = User::create([
            ...collect($validated)->except(['role', 'password'])->all(),
            'password' => Hash::make($validated['password']),
            'email_verified_at' => now(),
        ]);

        $user->syncRoles([$validated['role']]);

        return redirect()
            ->route('users.index')
            ->with('status', "Pengguna {$user->name} ditambahkan dengan peran {$validated['role']}.");
    }

    public function edit(User $user): View
    {
        $this->authorize('update', $user);

        return view('users.edit', $this->formOptions() + ['user' => $user]);
    }

    public function update(Request $request, User $user): RedirectResponse
    {
        $this->authorize('update', $user);

        $validated = $this->validateUser($request, $user);

        $user->update(collect($validated)->except(['role', 'password'])->all());

        if (filled($validated['password'] ?? null)) {
            $user->forceFill(['password' => Hash::make($validated['password'])])->save();
        }

        $user->syncRoles([$validated['role']]);

        return redirect()->route('users.index')->with('status', 'Data pengguna diperbarui.');
    }

    public function destroy(User $user): RedirectResponse
    {
        $this->authorize('delete', $user);

        $user->delete();

        return redirect()->route('users.index')->with('status', 'Pengguna dinonaktifkan.');
    }

    /** FR-M1-10 — audit log seluruh aksi create/update/delete. */
    public function auditLog(Request $request): View
    {
        abort_unless($request->user()->can(Permissions::AUDIT_LOG_VIEW), 403);

        $activities = \Spatie\Activitylog\Models\Activity::query()
            ->with('causer')
            ->when($request->filled('log_name'), fn ($q) => $q->where('log_name', $request->string('log_name')))
            ->when($request->filled('causer_id'), fn ($q) => $q->where('causer_id', $request->integer('causer_id')))
            ->when($request->filled('search'), function ($q) use ($request) {
                $search = $request->string('search');
                $q->where(function ($query) use ($search) {
                    $query->where('created_at', 'like', "%{$search}%")
                          ->orWhere('log_name', 'like', "%{$search}%")
                          ->orWhere('description', 'like', "%{$search}%")
                          ->orWhereHas('causer', fn ($causerQuery) => $causerQuery->where('name', 'like', "%{$search}%"));
                });
            })
            ->latest()
            ->paginate(50)
            ->withQueryString();

        return view('users.audit-log', [
            'activities' => $activities,
            'logNames' => \Spatie\Activitylog\Models\Activity::query()
                ->distinct()
                ->orderBy('log_name')
                ->pluck('log_name'),
            'filters' => $request->only(['search', 'log_name', 'causer_id']),
        ]);
    }

    /** @return array<string, mixed> */
    private function validateUser(Request $request, ?User $user = null): array
    {
        return $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'username' => [
                'required', 'string', 'max:60',
                Rule::unique('users', 'username')->ignore($user?->getKey())->whereNull('deleted_at'),
            ],
            'email' => [
                'required', 'email', 'max:255',
                Rule::unique('users', 'email')->ignore($user?->getKey())->whereNull('deleted_at'),
            ],
            'phone' => ['nullable', 'string', 'max:30'],
            'department_id' => ['nullable', 'exists:departments,id'],
            'role' => ['required', Rule::exists('roles', 'name')],
            // NFR Keamanan — kata sandi wajib saat pembuatan akun baru.
            'password' => [
                $user ? 'nullable' : 'required',
                'confirmed',
                Password::min(8)->letters()->numbers(),
            ],
            'is_active' => ['required', 'boolean'],
        ], [], [
            'name' => 'nama',
            'username' => 'nama pengguna',
            'email' => 'email',
            'department_id' => 'departemen',
            'role' => 'peran',
            'password' => 'kata sandi',
        ]);
    }

    /** @return array<string, mixed> */
    private function formOptions(): array
    {
        return [
            'departments' => Department::active()->latest()->get(),
            'roles' => Role::orderBy('name')->get(),
        ];
    }
}
