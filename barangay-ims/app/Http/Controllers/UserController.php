<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Traits\LogsAudit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use App\Support\PasswordPolicy;
use Illuminate\Validation\Rule;

class UserController extends Controller
{
    use LogsAudit;
    public function index()
    {
        $this->authorize('viewAny', User::class);
        $users = User::latest()->paginate(10)->withQueryString();
        return view('users.index', compact('users'));
    }

    public function create()
    {
        $this->authorize('create', User::class);
        return view('users.create');
    }

    public function store(Request $request)
    {
        $this->authorize('create', User::class);
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255|unique:users',
            'password' => ['required', 'string', 'confirmed', PasswordPolicy::rule()],
            'role' => ['required', Rule::in(User::ROLES)],
        ]);

        $validated['password'] = Hash::make($validated['password']);
        $user = User::create($validated);

        $this->auditCreated($user, "Created user account {$user->name}");

        return redirect()->route('users.index')->with('success', 'User account created successfully.');
    }

    public function edit(User $user)
    {
        $this->authorize('update', $user);
        return view('users.edit', compact('user'));
    }

    public function update(Request $request, User $user)
    {
        $this->authorize('update', $user);
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255|unique:users,email,' . $user->id,
            'role' => ['required', Rule::in(User::ROLES)],
            'is_active' => 'boolean',
        ]);

        $validated['is_active'] = $request->boolean('is_active');

        if ($user->isAdmin()
            && User::where('role', User::ROLE_ADMIN)->count() <= 1
            && ($validated['role'] !== User::ROLE_ADMIN || !$validated['is_active'])) {
            return back()->with('error', 'The last administrator must remain active and keep the Administrator role.');
        }

        if ($request->filled('password')) {
            $request->validate(['password' => ['string', 'confirmed', PasswordPolicy::rule()]]);
            $validated['password'] = Hash::make($request->password);
        }

        $before = $this->auditSnapshot($user);
        $user->update($validated);

        $changes = $user->getChanges();
        $action = match (true) {
            array_key_exists('role', $changes) => 'role_changed',
            array_key_exists('is_active', $changes) => $user->is_active ? 'activated' : 'deactivated',
            array_key_exists('password', $changes) => 'password_changed',
            default => 'updated',
        };
        $this->auditUpdated($user, $before, "Updated user account {$user->name}", $action);

        return redirect()->route('users.index')->with('success', 'User account updated successfully.');
    }

    public function destroy(User $user)
    {
        $this->authorize('delete', $user);
        if ($user->isAdmin() && User::where('role', User::ROLE_ADMIN)->count() <= 1) {
            return back()->with('error', 'Cannot delete the last admin account.');
        }

        $name = $user->name;
        $before = $this->auditSnapshot($user);
        $user->delete();

        $this->auditDeleted($user, $before, "Deleted user account {$name}");

        return redirect()->route('users.index')->with('success', 'User account deleted successfully.');
    }
}
