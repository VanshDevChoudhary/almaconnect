<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreJobRequest;
use App\Http\Requests\UpdateJobRequest;
use App\Models\Job;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use Mews\Purifier\Facades\Purifier;

class JobController extends Controller
{
    private const PER_PAGE = 20;

    private function cardData(Job $job): array
    {
        return [
            'id' => $job->id,
            'title' => $job->title,
            'company' => $job->company,
            'location' => $job->location,
            'type' => $job->type,
            'description' => $job->description,
            'skills' => $job->skills ?? [],
            'salary_min' => $job->salary_min,
            'salary_max' => $job->salary_max,
            'salary_currency' => $job->salary_currency,
            'status' => $job->status,
            'expires_at' => $job->expires_at->toIso8601String(),
            'created_at' => $job->created_at->toIso8601String(),
            'poster' => [
                'id' => $job->poster?->id,
                'name' => $job->poster?->name,
                'avatar' => $job->poster?->avatar,
                'batch' => $job->poster?->profile?->batch,
                'branch' => $job->poster?->profile?->branch,
            ],
        ];
    }

    public function index(Request $request): Response
    {
        $types = array_values(array_filter((array) $request->input('type', [])));
        $location = trim((string) $request->input('location', ''));
        $q = trim((string) $request->input('q', ''));
        $sort = $request->input('sort', 'latest');

        $query = Job::query()
            ->with('poster.profile')
            ->where('status', 'active')
            ->where('expires_at', '>', now());

        if ($types) {
            $query->whereIn('type', $types);
        }
        if ($location !== '') {
            $query->where('location', 'LIKE', '%'.$location.'%');
        }
        if ($q !== '') {
            $query->where(function ($w) use ($q) {
                $w->where('title', 'LIKE', "%{$q}%")
                    ->orWhere('company', 'LIKE', "%{$q}%")
                    ->orWhere('description', 'LIKE', "%{$q}%");
            });
        }

        match ($sort) {
            'salary_high' => $query->orderByRaw('salary_max IS NULL, salary_max DESC'),
            'salary_low' => $query->orderByRaw('salary_min IS NULL, salary_min ASC'),
            default => $query->latest(),
        };

        $paginator = $query->paginate(self::PER_PAGE)->withQueryString();
        $paginator->through(fn (Job $j) => $this->cardData($j));

        return Inertia::render('Jobs/Index', [
            'jobs' => $paginator,
            'filters' => [
                'type' => $types,
                'location' => $location,
                'q' => $q,
                'sort' => $sort,
            ],
            'canPost' => in_array($request->user()->role, ['alumni', 'admin'], true),
        ]);
    }

    public function mine(Request $request): Response
    {
        $jobs = Job::with('poster.profile')
            ->where('posted_by', $request->user()->id)
            ->latest()
            ->get()
            ->map(fn (Job $j) => $this->cardData($j));

        return Inertia::render('Jobs/Mine', ['jobs' => $jobs]);
    }

    public function create(): Response
    {
        return Inertia::render('Jobs/Create', [
            'defaultExpiry' => now()->addDays(30)->toDateString(),
        ]);
    }

    public function store(StoreJobRequest $request): RedirectResponse
    {
        $data = $request->validated();
        $data['posted_by'] = $request->user()->id;
        $data['status'] = 'active';
        $data['description'] = Purifier::clean($data['description'], 'alumni_post');

        $job = Job::create($data);

        return redirect()
            ->route('jobs.show', $job->id)
            ->with('success', 'Job posted.');
    }

    public function show(Request $request, Job $job): Response
    {
        $job->load('poster.profile');
        $user = $request->user();

        $card = $this->cardData($job);
        $card['apply_url'] = $job->apply_url;
        $card['apply_email'] = $job->apply_email;
        $card['poster']['slug'] = $job->poster?->profile?->slug;
        $card['is_expired'] = $job->status === 'expired' || $job->expires_at->isPast();

        return Inertia::render('Jobs/Show', [
            'job' => $card,
            'canManage' => $user->can('update', $job),
        ]);
    }

    public function edit(Request $request, Job $job): Response
    {
        $this->authorize('update', $job);

        return Inertia::render('Jobs/Edit', [
            'job' => [
                'id' => $job->id,
                'title' => $job->title,
                'company' => $job->company,
                'location' => $job->location,
                'type' => $job->type,
                'description' => $job->description,
                'skills' => $job->skills ?? [],
                'salary_min' => $job->salary_min,
                'salary_max' => $job->salary_max,
                'salary_currency' => $job->salary_currency,
                'apply_url' => $job->apply_url,
                'apply_email' => $job->apply_email,
                'expires_at' => $job->expires_at->toDateString(),
            ],
        ]);
    }

    public function update(UpdateJobRequest $request, Job $job): RedirectResponse
    {
        $this->authorize('update', $job);

        $data = $request->validated();
        $data['description'] = Purifier::clean($data['description'], 'alumni_post');

        $job->update($data);

        return redirect()
            ->route('jobs.show', $job->id)
            ->with('success', 'Job updated.');
    }

    public function destroy(Request $request, Job $job): RedirectResponse
    {
        $this->authorize('delete', $job);
        $job->delete();

        return redirect()
            ->route('jobs.index')
            ->with('success', 'Job deleted.');
    }

    public function markFilled(Request $request, Job $job): RedirectResponse
    {
        $this->authorize('markFilled', $job);
        $job->update(['status' => 'filled']);

        return back()->with('success', 'Marked as filled.');
    }
}
