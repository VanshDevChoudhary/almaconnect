<?php

namespace App\Http\Controllers;

use App\Http\Requests\SurveyResponseRequest;
use App\Models\Survey;
use App\Models\SurveyResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;

class SurveyController extends Controller
{
    /**
     * Surveys a given user is eligible for. Admins see everything.
     */
    public static function audienceFilter($query, Request $request)
    {
        $role = $request->user()->role;
        if ($role === 'admin') {
            return $query;
        }
        $audiences = ['all'];
        if ($role === 'alumni') {
            $audiences[] = 'alumni';
        }
        if ($role === 'student') {
            $audiences[] = 'students';
        }

        return $query->whereIn('target_audience', $audiences);
    }

    public function index(Request $request): Response
    {
        $userId = $request->user()->id;

        $surveys = self::audienceFilter(
            Survey::where('is_active', true)->withCount('questions'),
            $request
        )
            ->latest()
            ->get()
            ->map(fn (Survey $s) => [
                'id' => $s->id,
                'title' => $s->title,
                'description' => $s->description,
                'question_count' => $s->questions_count,
                'completed' => SurveyResponse::where('survey_id', $s->id)
                    ->where('user_id', $userId)
                    ->exists(),
            ]);

        return Inertia::render('Surveys/Index', ['surveys' => $surveys]);
    }

    public function show(Request $request, Survey $survey): Response|RedirectResponse
    {
        abort_unless($survey->is_active, 404);

        $eligible = self::audienceFilter(
            Survey::whereKey($survey->id),
            $request
        )->exists();
        abort_unless($eligible, 403);

        $alreadyResponded = SurveyResponse::where('survey_id', $survey->id)
            ->where('user_id', $request->user()->id)
            ->exists();

        if ($alreadyResponded) {
            return redirect()->route('surveys.index')
                ->with('success', "You've already responded to this survey.");
        }

        $survey->load('questions');

        return Inertia::render('Surveys/Show', [
            'survey' => [
                'id' => $survey->id,
                'title' => $survey->title,
                'description' => $survey->description,
                'questions' => $survey->questions->map(fn ($q) => [
                    'id' => $q->id,
                    'question' => $q->question,
                    'type' => $q->type,
                    'options' => $q->options ?? [],
                ]),
            ],
        ]);
    }

    public function respond(SurveyResponseRequest $request, Survey $survey): RedirectResponse
    {
        $user = $request->user();
        $answers = $request->validated()['answers'] ?? [];

        $eligible = self::audienceFilter(
            Survey::whereKey($survey->id)->where('is_active', true),
            $request
        )->exists();
        abort_unless($eligible, 403);

        $survey->load('questions');

        DB::transaction(function () use ($survey, $user, $answers) {
            $exists = SurveyResponse::where('survey_id', $survey->id)
                ->where('user_id', $user->id)
                ->exists();
            abort_if($exists, 409, 'You have already responded to this survey.');

            foreach ($survey->questions as $q) {
                $answer = $answers[$q->id] ?? null;
                if ($q->type === 'multi_choice') {
                    foreach ((array) $answer as $choice) {
                        SurveyResponse::create([
                            'survey_id' => $survey->id,
                            'question_id' => $q->id,
                            'user_id' => $user->id,
                            'answer' => $choice,
                        ]);
                    }
                } else {
                    SurveyResponse::create([
                        'survey_id' => $survey->id,
                        'question_id' => $q->id,
                        'user_id' => $user->id,
                        'answer' => $answer,
                    ]);
                }
            }
        });

        return redirect()->route('surveys.index')
            ->with('success', 'Thank you for your response.');
    }
}
