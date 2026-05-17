<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\RosterEntry;
use App\Models\User;
use Inertia\Inertia;
use Inertia\Response;

class VerificationController extends Controller
{
    public function index(): Response
    {
        $pending = User::where('status', 'pending')
            ->with('profile')
            ->latest()
            ->get();

        $matched = $pending->map(function (User $user) {
            $match = null;

            if ($user->email) {
                $match = RosterEntry::where('email', $user->email)->first();
            }

            if (! $match && $user->profile) {
                $match = RosterEntry::where('name', $user->name)
                    ->where('batch', $user->profile->batch)
                    ->first();
            }

            return [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'role' => $user->role,
                'avatar' => $user->avatar,
                'batch' => $user->profile?->batch,
                'branch' => $user->profile?->branch,
                'roll_no' => $user->profile?->roll_no,
                'submitted_at' => $user->created_at->toIso8601String(),
                'roster_match' => $match ? [
                    'name' => $match->name,
                    'email' => $match->email,
                    'batch' => $match->batch,
                    'branch' => $match->branch,
                ] : null,
            ];
        });

        return Inertia::render('Admin/Verification/Index', ['pending' => $matched]);
    }
}
