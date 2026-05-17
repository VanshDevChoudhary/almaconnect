<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Feedback;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class FeedbackController extends Controller
{
    public function index(Request $request): Response
    {
        $category = $request->input('category');
        $resolved = $request->input('resolved');

        $query = Feedback::latest();

        if ($category && in_array($category, ['bug', 'suggestion', 'general'], true)) {
            $query->where('category', $category);
        }
        if ($resolved === 'resolved') {
            $query->where('is_resolved', true);
        } elseif ($resolved === 'unresolved') {
            $query->where('is_resolved', false);
        }

        $paginator = $query->paginate(25)->withQueryString();
        $paginator->through(fn (Feedback $f) => [
            'id' => $f->id,
            'name' => $f->name,
            'email' => $f->email,
            'category' => $f->category,
            'message' => $f->message,
            'is_resolved' => $f->is_resolved,
            'created_at' => $f->created_at->toIso8601String(),
        ]);

        return Inertia::render('Admin/Feedback/Index', [
            'feedback' => $paginator,
            'filters' => ['category' => $category ?: 'all', 'resolved' => $resolved ?: 'all'],
        ]);
    }

    public function toggle(Feedback $feedback): RedirectResponse
    {
        $feedback->update(['is_resolved' => ! $feedback->is_resolved]);

        return back();
    }

    public function destroy(Feedback $feedback): RedirectResponse
    {
        $feedback->delete();

        return back()->with('success', 'Feedback deleted.');
    }
}
