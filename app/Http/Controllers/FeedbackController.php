<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreFeedbackRequest;
use App\Models\Feedback;
use Illuminate\Http\JsonResponse;

class FeedbackController extends Controller
{
    public function store(StoreFeedbackRequest $request): JsonResponse
    {
        $data = $request->validated();

        Feedback::create([
            'user_id' => $request->user()?->id,
            'name' => $data['name'],
            'email' => $data['email'],
            'category' => $data['category'],
            'message' => $data['message'],
            'is_resolved' => false,
        ]);

        return response()->json([
            'success' => true,
            'message' => "Thanks! We've received your feedback.",
        ]);
    }
}
