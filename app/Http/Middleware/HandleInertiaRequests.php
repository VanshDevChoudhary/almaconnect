<?php

namespace App\Http\Middleware;

use App\Models\Feedback;
use App\Models\SuccessStory;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Inertia\Middleware;

class HandleInertiaRequests extends Middleware
{
    /**
     * The root template that is loaded on the first page visit.
     *
     * @var string
     */
    protected $rootView = 'app';

    /**
     * Determine the current asset version.
     */
    public function version(Request $request): ?string
    {
        return parent::version($request);
    }

    /**
     * Define the props that are shared by default.
     *
     * @return array<string, mixed>
     */
    public function share(Request $request): array
    {
        return [
            ...parent::share($request),
            'auth' => [
                'user' => $request->user(),
                'profileSlug' => $request->user()?->profile?->slug,
            ],
            'flash' => [
                'success' => fn () => $request->session()->get('success'),
                'error' => fn () => $request->session()->get('error'),
            ],
            'adminCounts' => fn () => $request->user()?->isAdmin()
                ? Cache::remember('admin_counts', 60, fn () => [
                    'pending_verification' => User::where('status', 'pending')->count(),
                    'pending_stories' => SuccessStory::where('status', 'pending')->count(),
                    'unresolved_feedback' => Feedback::where('is_resolved', false)->count(),
                ])
                : null,
        ];
    }
}
