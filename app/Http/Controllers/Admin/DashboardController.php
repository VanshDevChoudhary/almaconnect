<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Donation;
use App\Models\DonationCampaign;
use App\Models\Event;
use App\Models\Job;
use App\Models\SuccessStory;
use App\Models\User;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    public function index(): Response
    {
        $stats = Cache::remember('admin_stats', 30, function () {
            return [
                'approved_alumni' => User::where('role', 'alumni')->where('status', 'approved')->count(),
                'pending_verification' => User::where('status', 'pending')->count(),
                'donations_this_month' => (int) Donation::where('status', 'success')
                    ->where('created_at', '>=', Carbon::now()->startOfMonth())
                    ->sum('amount'),
                'active_campaigns' => DonationCampaign::where('is_active', true)->count(),
                'upcoming_events' => Event::upcoming()->count(),
                'active_jobs' => Job::active()->count(),
            ];
        });

        // Recent activity — union of key tables.
        $activity = collect([
            User::latest()->take(5)->get()->map(fn ($u) => [
                'type' => 'signup',
                'label' => "{$u->name} joined",
                'created_at' => $u->created_at,
            ]),
            Donation::where('status', 'success')->with('user', 'campaign')->latest()->take(5)->get()->map(fn ($d) => [
                'type' => 'donation',
                'label' => '₹'.number_format((int) $d->amount).' donation by '.($d->user?->name ?? 'Anonymous').($d->campaign ? ' to '.$d->campaign->title : ''),
                'created_at' => $d->created_at,
            ]),
            SuccessStory::latest()->take(4)->get()->map(fn ($s) => [
                'type' => 'story',
                'label' => "Story submitted: {$s->headline}",
                'created_at' => $s->created_at,
            ]),
        ])
            ->flatten(1)
            ->sortByDesc('created_at')
            ->take(20)
            ->values()
            ->map(fn ($item) => [
                'type' => $item['type'],
                'label' => $item['label'],
                'time' => $item['created_at']?->diffForHumans(),
            ]);

        return Inertia::render('Admin/Dashboard', [
            'stats' => $stats,
            'activity' => $activity,
        ]);
    }
}
