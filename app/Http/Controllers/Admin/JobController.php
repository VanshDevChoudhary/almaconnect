<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Job;
use Inertia\Inertia;
use Inertia\Response;

class JobController extends Controller
{
    public function index(): Response
    {
        $jobs = Job::with('poster.profile')->latest()->paginate(25);
        $jobs->through(fn (Job $j) => [
            'id' => $j->id,
            'title' => $j->title,
            'company' => $j->company,
            'type' => $j->type,
            'status' => $j->status,
            'poster' => $j->poster?->name,
            'created_at' => $j->created_at->toIso8601String(),
        ]);

        return Inertia::render('Admin/Jobs/Index', ['jobs' => $jobs]);
    }
}
