<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class UserController extends Controller
{
    public function index(Request $request): Response
    {
        $role = $request->input('role');
        $status = $request->input('status');
        $q = trim((string) $request->input('q', ''));

        $query = User::with('profile')->latest();

        if ($role && in_array($role, ['alumni', 'student', 'admin'], true)) {
            $query->where('role', $role);
        }
        if ($status && in_array($status, ['pending', 'approved', 'rejected', 'banned'], true)) {
            $query->where('status', $status);
        }
        if ($q !== '') {
            $query->where(fn ($w) => $w->where('name', 'like', "%{$q}%")->orWhere('email', 'like', "%{$q}%"));
        }

        $paginator = $query->paginate(25)->withQueryString();
        $paginator->through(fn (User $u) => [
            'id' => $u->id,
            'name' => $u->name,
            'email' => $u->email,
            'role' => $u->role,
            'status' => $u->status,
            'avatar' => $u->avatar,
            'batch' => $u->profile?->batch,
            'branch' => $u->profile?->branch,
            'profile_slug' => $u->profile?->slug,
            'created_at' => $u->created_at->toIso8601String(),
        ]);

        return Inertia::render('Admin/Users/Index', [
            'users' => $paginator,
            'filters' => ['role' => $role ?: 'all', 'status' => $status ?: 'all', 'q' => $q],
        ]);
    }

    public function approve(Request $request, User $user): RedirectResponse
    {
        $user->update(['status' => 'approved']);
        return back();
    }

    public function reject(Request $request, User $user): RedirectResponse
    {
        $user->update(['status' => 'rejected']);
        return back();
    }

    public function bulkApprove(Request $request): RedirectResponse
    {
        User::whereIn('id', (array) $request->input('ids', []))->update(['status' => 'approved']);
        return back()->with('success', 'Users approved.');
    }

    public function bulkReject(Request $request): RedirectResponse
    {
        User::whereIn('id', (array) $request->input('ids', []))->update(['status' => 'rejected']);
        return back()->with('success', 'Users rejected.');
    }

    public function bulkBan(Request $request): RedirectResponse
    {
        User::whereIn('id', (array) $request->input('ids', []))->update(['status' => 'banned']);
        return back()->with('success', 'Users banned.');
    }

    public function edit(User $user): Response
    {
        return Inertia::render('Admin/Users/Edit', [
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'role' => $user->role,
                'status' => $user->status,
            ],
        ]);
    }

    public function update(Request $request, User $user): RedirectResponse
    {
        $data = $request->validate([
            'role' => ['required', 'in:alumni,student,admin'],
            'status' => ['required', 'in:pending,approved,rejected,banned'],
        ]);

        $user->update($data);

        return redirect()->route('admin.users.index')->with('success', 'User updated.');
    }

    public function destroy(User $user): RedirectResponse
    {
        if ($user->id === request()->user()->id) {
            return back()->with('error', 'Cannot delete your own account.');
        }
        $user->delete();

        return back()->with('success', 'User deleted.');
    }
}
