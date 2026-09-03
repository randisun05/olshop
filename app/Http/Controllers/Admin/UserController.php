<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreUserRequest;
use App\Http\Requests\Admin\UpdateUserRequest;
use App\Models\ActivityLog;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Hash;
use Inertia\Inertia;
use Inertia\Response;

class UserController extends Controller
{
    /**
     * Role staf yang bisa dikelola lewat halaman ini. "Customer" sengaja
     * tidak termasuk — akun pelanggan dibuat lewat alur registrasi publik,
     * bukan dari panel admin.
     */
    private const STAFF_ROLES = ['Super Admin', 'Admin', 'Staff Gudang', 'Staff CS'];

    public function index(): Response
    {
        $users = User::role(self::STAFF_ROLES)
            ->orderBy('name')
            ->get()
            ->map(fn (User $user) => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'role' => $user->getRoleNames()->first(),
                'is_active' => $user->is_active,
                'is_self' => $user->id === auth()->id(),
            ]);

        return Inertia::render('Admin/Users/Index', [
            'users' => $users,
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('Admin/Users/Form', [
            'roles' => self::STAFF_ROLES,
        ]);
    }

    public function store(StoreUserRequest $request): RedirectResponse
    {
        $user = User::create([
            'name' => $request->validated('name'),
            'email' => $request->validated('email'),
            'password' => Hash::make($request->validated('password')),
            'email_verified_at' => now(),
            'is_active' => true,
        ]);

        $user->assignRole($request->validated('role'));

        ActivityLog::record('user.create', "Menambahkan akun staf \"{$user->name}\" ({$request->validated('role')}).", $user);

        return redirect()->route('admin.users.index')->with('success', 'Akun staf berhasil ditambahkan.');
    }

    public function edit(User $user): Response
    {
        abort_unless($user->hasAnyRole(self::STAFF_ROLES), 404);

        return Inertia::render('Admin/Users/Form', [
            'roles' => self::STAFF_ROLES,
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'role' => $user->getRoleNames()->first(),
                'is_active' => $user->is_active,
            ],
        ]);
    }

    public function update(UpdateUserRequest $request, User $user): RedirectResponse
    {
        abort_unless($user->hasAnyRole(self::STAFF_ROLES), 404);

        if ($user->id === auth()->id() && $request->validated('role') !== 'Super Admin') {
            return back()->with('error', 'Tidak dapat mengubah role akun Anda sendiri.');
        }

        if ($this->wouldRemoveLastSuperAdmin($user, $request->validated('role'))) {
            return back()->with('error', 'Tidak dapat mengubah role Super Admin terakhir.');
        }

        $data = [
            'name' => $request->validated('name'),
            'email' => $request->validated('email'),
        ];

        if ($request->filled('password')) {
            $data['password'] = Hash::make($request->validated('password'));
        }

        $user->update($data);
        $user->syncRoles([$request->validated('role')]);

        ActivityLog::record('user.update', "Memperbarui akun staf \"{$user->name}\".", $user);

        return redirect()->route('admin.users.index')->with('success', 'Akun staf berhasil diperbarui.');
    }

    public function toggleActive(User $user): RedirectResponse
    {
        abort_unless($user->hasAnyRole(self::STAFF_ROLES), 404);

        if ($user->id === auth()->id()) {
            return back()->with('error', 'Tidak dapat menonaktifkan akun Anda sendiri.');
        }

        if ($user->is_active && $this->wouldRemoveLastSuperAdmin($user, null)) {
            return back()->with('error', 'Tidak dapat menonaktifkan Super Admin terakhir.');
        }

        $user->update(['is_active' => ! $user->is_active]);

        ActivityLog::record(
            $user->is_active ? 'user.activate' : 'user.deactivate',
            ($user->is_active ? 'Mengaktifkan' : 'Menonaktifkan')." akun staf \"{$user->name}\".",
            $user
        );

        return back()->with('success', 'Status akun staf berhasil diperbarui.');
    }

    /**
     * Cegah tindakan yang akan membuat sistem kehilangan seluruh akun Super
     * Admin aktif (baik lewat penggantian role maupun penonaktifan).
     */
    private function wouldRemoveLastSuperAdmin(User $user, ?string $newRole): bool
    {
        if (! $user->hasRole('Super Admin')) {
            return false;
        }

        $otherActiveSuperAdmins = User::role('Super Admin')
            ->where('id', '!=', $user->id)
            ->where('is_active', true)
            ->exists();

        if ($otherActiveSuperAdmins) {
            return false;
        }

        return $newRole !== 'Super Admin';
    }
}
