<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AdminUser;
use App\Services\Audit\AuditLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;
use Spatie\Permission\Models\Role;

/**
 * Admin → Admin Users. Safeguards: an admin cannot deactivate or demote
 * themselves, and the last active Super Admin can never be removed.
 */
class AdminUserController extends Controller
{
    public const ROLE_LABELS = [
        'super-admin' => 'Super Admin (everything)',
        'membership-manager' => 'Membership Manager',
        'content-manager' => 'Content Manager',
        'accounts' => 'Accounts (payments & donations)',
        'matrimonial-moderator' => 'Matrimonial Moderator',
    ];

    public function __construct(private readonly AuditLogger $audit) {}

    public function index(Request $request): View
    {
        $search = trim((string) $request->query('q', ''));

        $admins = AdminUser::with('roles:id,name')
            ->when($search !== '', fn ($q) => $q->where(fn ($q) => $q
                ->where('name', 'like', "%{$search}%")
                ->orWhere('username', 'like', "%{$search}%")
                ->orWhere('email', 'like', "%{$search}%")))
            ->orderByDesc('is_active')->orderBy('name')
            ->paginate(20)->withQueryString();

        return view('admin.admin-users.index', ['admins' => $admins, 'search' => $search, 'roles' => self::ROLE_LABELS]);
    }

    public function create(): View
    {
        return view('admin.admin-users.form', ['admin' => new AdminUser(['is_active' => true]), 'roles' => self::ROLE_LABELS]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate($this->rules(null));

        $admin = DB::transaction(function () use ($data) {
            $admin = AdminUser::create([
                'name' => $data['name'],
                'username' => $data['username'],
                'email' => $data['email'],
                'password' => $data['password'],
                'is_active' => true,
            ]);
            $admin->syncRoles([$data['role']]);

            return $admin;
        });

        $this->audit->record('admin_user.created', $admin, "Created admin {$admin->username} ({$data['role']})");

        return redirect()->route('admin.admin-users.index')->with('success', "Admin {$admin->name} created.");
    }

    public function edit(AdminUser $adminUser): View
    {
        return view('admin.admin-users.form', ['admin' => $adminUser, 'roles' => self::ROLE_LABELS]);
    }

    public function update(Request $request, AdminUser $adminUser): RedirectResponse
    {
        $data = $request->validate($this->rules($adminUser));
        $isSelf = $adminUser->is($request->user('admin'));
        $active = $request->boolean('is_active');
        $oldRole = $adminUser->roles->pluck('name')->first();

        if ($isSelf && (! $active || $data['role'] !== $oldRole)) {
            throw ValidationException::withMessages(['role' => 'You cannot change your own role or deactivate your own account.']);
        }

        if ($oldRole === 'super-admin' && ($data['role'] !== 'super-admin' || ! $active) && $this->activeSuperAdmins() <= 1) {
            throw ValidationException::withMessages(['role' => 'At least one active Super Admin is required.']);
        }

        $old = $adminUser->only(['name', 'email', 'is_active']) + ['role' => $oldRole];

        DB::transaction(function () use ($adminUser, $data, $active) {
            $adminUser->fill([
                'name' => $data['name'],
                'email' => $data['email'],
                'is_active' => $active,
            ]);
            if (filled($data['password'] ?? null)) {
                $adminUser->password = $data['password'];
            }
            $adminUser->save();
            $adminUser->syncRoles([$data['role']]);
        });

        $new = $adminUser->only(['name', 'email', 'is_active']) + ['role' => $data['role']];
        $this->audit->record('admin_user.updated', $adminUser, "Updated admin {$adminUser->username}".(filled($data['password'] ?? null) ? ' (password reset)' : ''), $old, $new);

        return redirect()->route('admin.admin-users.index')->with('success', "Admin {$adminUser->name} updated.");
    }

    private function rules(?AdminUser $admin): array
    {
        return [
            'name' => ['required', 'string', 'max:120'],
            'username' => $admin ? ['prohibited'] : ['required', 'string', 'min:3', 'max:60', 'alpha_dash', 'unique:admin_users,username'],
            'email' => ['required', 'email:rfc', 'max:191', Rule::unique('admin_users', 'email')->ignore($admin?->id)],
            'role' => ['required', Rule::in(array_keys(self::ROLE_LABELS)), Rule::exists(Role::class, 'name')->where('guard_name', 'admin')],
            'password' => [$admin ? 'nullable' : 'required', 'confirmed', Password::min(10)->letters()->mixedCase()->numbers()],
            'is_active' => ['sometimes', 'boolean'],
        ];
    }

    private function activeSuperAdmins(): int
    {
        return AdminUser::role('super-admin', 'admin')->where('is_active', true)->count();
    }
}
