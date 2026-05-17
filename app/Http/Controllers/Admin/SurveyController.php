<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreSurveyRequest;
use App\Models\Survey;
use App\Models\SurveyQuestion;
use App\Models\SurveyResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;

class SurveyController extends Controller
{
    public function index(): Response
    {
        $surveys = Survey::withCount(['questions', 'responses'])
            ->latest()
            ->get()
            ->map(fn (Survey $s) => [
                'id' => $s->id,
                'title' => $s->title,
                'target_audience' => $s->target_audience,
                'is_active' => $s->is_active,
                'question_count' => $s->questions_count,
                'respondent_count' => SurveyResponse::where('survey_id', $s->id)
                    ->distinct('user_id')->count('user_id'),
            ]);

        return Inertia::render('Admin/Surveys/Index', ['surveys' => $surveys]);
    }

    public function create(): Response
    {
        return Inertia::render('Admin/Surveys/Create');
    }

    public function store(StoreSurveyRequest $request): RedirectResponse
    {
        $data = $request->validated();

        DB::transaction(function () use ($data, $request) {
            $survey = Survey::create([
                'title' => $data['title'],
                'description' => $data['description'] ?? null,
                'target_audience' => $data['target_audience'],
                'is_active' => $request->boolean('is_active', true),
                'created_by' => $request->user()->id,
            ]);

            foreach ($data['questions'] as $i => $q) {
                SurveyQuestion::create([
                    'survey_id' => $survey->id,
                    'question' => $q['question'],
                    'type' => $q['type'],
                    'options' => $q['type'] === 'text' ? null : array_values(array_filter(
                        (array) ($q['options'] ?? []),
                        fn ($o) => trim((string) $o) !== ''
                    )),
                    'order' => $i,
                ]);
            }
        });

        return redirect()->route('admin.surveys.index')->with('success', 'Survey created.');
    }

    public function edit(Survey $survey): Response
    {
        $survey->load('questions');

        return Inertia::render('Admin/Surveys/Edit', [
            'survey' => [
                'id' => $survey->id,
                'title' => $survey->title,
                'description' => $survey->description,
                'target_audience' => $survey->target_audience,
                'is_active' => $survey->is_active,
                'has_responses' => $survey->responses()->exists(),
                'questions' => $survey->questions->map(fn ($q) => [
                    'question' => $q->question,
                    'type' => $q->type,
                    'options' => $q->options ?? [],
                ]),
            ],
        ]);
    }

    public function update(StoreSurveyRequest $request, Survey $survey): RedirectResponse
    {
        $data = $request->validated();

        DB::transaction(function () use ($survey, $data, $request) {
            $survey->update([
                'title' => $data['title'],
                'description' => $data['description'] ?? null,
                'target_audience' => $data['target_audience'],
                'is_active' => $request->boolean('is_active', true),
            ]);

            // Editing questions is blocked once responses exist (avoids
            // orphaning aggregations against changed option text).
            if (! $survey->responses()->exists()) {
                $survey->questions()->delete();
                foreach ($data['questions'] as $i => $q) {
                    SurveyQuestion::create([
                        'survey_id' => $survey->id,
                        'question' => $q['question'],
                        'type' => $q['type'],
                        'options' => $q['type'] === 'text' ? null : array_values(array_filter(
                            (array) ($q['options'] ?? []),
                            fn ($o) => trim((string) $o) !== ''
                        )),
                        'order' => $i,
                    ]);
                }
            }
        });

        return redirect()->route('admin.surveys.index')->with('success', 'Survey updated.');
    }

    public function destroy(Survey $survey): RedirectResponse
    {
        $survey->delete();

        return redirect()->route('admin.surveys.index')->with('success', 'Survey deleted.');
    }

    public function responses(Survey $survey): Response
    {
        $survey->load('questions');

        $questions = $survey->questions->map(function ($q) {
            $base = [
                'id' => $q->id,
                'question' => $q->question,
                'type' => $q->type,
            ];

            if ($q->type === 'text') {
                $base['answers'] = SurveyResponse::where('question_id', $q->id)
                    ->with('user:id,name')
                    ->latest('created_at')
                    ->limit(50)
                    ->get()
                    ->map(fn ($r) => [
                        'answer' => $r->answer,
                        'user' => $r->user?->name ?? 'Unknown',
                        'date' => $r->created_at?->toIso8601String(),
                    ]);
            } else {
                $counts = SurveyResponse::where('question_id', $q->id)
                    ->select('answer', DB::raw('COUNT(*) as count'))
                    ->groupBy('answer')
                    ->pluck('count', 'answer');
                $base['distribution'] = collect($q->options ?? [])->map(fn ($opt) => [
                    'option' => $opt,
                    'count' => (int) ($counts[$opt] ?? 0),
                ]);
            }

            return $base;
        });

        return Inertia::render('Admin/Surveys/Responses', [
            'survey' => ['id' => $survey->id, 'title' => $survey->title],
            'respondents' => SurveyResponse::where('survey_id', $survey->id)
                ->distinct('user_id')->count('user_id'),
            'questions' => $questions,
        ]);
    }
}
