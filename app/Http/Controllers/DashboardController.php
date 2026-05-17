<?php

namespace App\Http\Controllers;

use App\Models\Survey;
use App\Models\SurveyResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    public function index(Request $request): Response
    {
        $userId = $request->user()->id;

        $respondedIds = SurveyResponse::where('user_id', $userId)
            ->distinct()
            ->pluck('survey_id');

        $pendingSurveys = SurveyController::audienceFilter(
            Survey::where('is_active', true)->whereNotIn('id', $respondedIds),
            $request
        )
            ->latest()
            ->get(['id', 'title'])
            ->map(fn ($s) => ['id' => $s->id, 'title' => $s->title]);

        return Inertia::render('Dashboard', [
            'pendingSurveys' => $pendingSurveys,
        ]);
    }
}
