<?php

use App\Http\Controllers\Admin\CampaignController as AdminCampaignController;
use App\Http\Controllers\Admin\DonationController as AdminDonationController;
use App\Http\Controllers\Admin\EventController as AdminEventController;
use App\Http\Controllers\Admin\FeedbackController as AdminFeedbackController;
use App\Http\Controllers\Admin\StoryController as AdminStoryController;
use App\Http\Controllers\Admin\SurveyController as AdminSurveyController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\FeedbackController;
use App\Http\Controllers\StoryController;
use App\Http\Controllers\SurveyController;
use App\Http\Controllers\Auth\GoogleController;
use App\Http\Controllers\DonationController;
use App\Http\Controllers\CommentController;
use App\Http\Controllers\DirectoryController;
use App\Http\Controllers\EventController;
use App\Http\Controllers\JobController;
use App\Http\Controllers\GroupController;
use App\Http\Controllers\LikeController;
use App\Http\Controllers\PostController;
use App\Http\Controllers\ProfileController;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', function () {
    return Inertia::render('Welcome', [
        'canLogin' => Route::has('login'),
        'canRegister' => Route::has('register'),
        'laravelVersion' => Application::VERSION,
        'phpVersion' => PHP_VERSION,
    ]);
});

Route::get('/dashboard', [DashboardController::class, 'index'])
    ->middleware(['auth', 'verified', 'alumni.approved'])->name('dashboard');

Route::post('/feedback', [FeedbackController::class, 'store'])
    ->middleware('throttle:10,1')->name('feedback.store');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/pending-review', function () {
        if (request()->user()->isApproved()) {
            return redirect()->route('dashboard');
        }

        return view('pending-review');
    })->name('pending-review');
});

Route::middleware('auth')->group(function () {
    Route::get('/access-denied', function () {
        $reason = request()->query('reason') === 'banned' ? 'banned' : 'rejected';

        return view('access-denied', ['reason' => $reason]);
    })->name('access-denied');

    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

// Profile editing is available to pending users (no alumni.approved) so they
// can complete their profile while awaiting approval.
Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/profile/edit', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::post('/profile/avatar', [ProfileController::class, 'uploadAvatar'])->name('profile.avatar');
});

Route::middleware(['auth', 'verified', 'alumni.approved'])->group(function () {
    Route::get('/directory', [DirectoryController::class, 'index'])->name('directory');

    Route::get('/groups', [GroupController::class, 'index'])->name('groups.index');
    Route::get('/groups/{slug}', [GroupController::class, 'show'])->name('groups.show');
    Route::post('/groups/{slug}/join', [GroupController::class, 'join'])->name('groups.join');
    Route::post('/groups/{slug}/leave', [GroupController::class, 'leave'])->name('groups.leave');

    Route::post('/groups/{slug}/posts', [PostController::class, 'store'])
        ->middleware('throttle:30,60')->name('posts.store');
    Route::delete('/posts/{post}', [PostController::class, 'destroy'])->name('posts.destroy');
    Route::post('/posts/{post}/pin', [PostController::class, 'pin'])->name('posts.pin');
    Route::post('/posts/{post}/unpin', [PostController::class, 'unpin'])->name('posts.unpin');

    Route::post('/posts/{post}/comments', [CommentController::class, 'store'])
        ->middleware('throttle:60,60')->name('comments.store');
    Route::delete('/comments/{comment}', [CommentController::class, 'destroy'])->name('comments.destroy');

    Route::post('/api/posts/{post}/like', [LikeController::class, 'toggle'])->name('posts.like');

    Route::get('/events', [EventController::class, 'index'])->name('events.index');
    Route::get('/events/{slug}', [EventController::class, 'show'])->name('events.show');
    Route::post('/events/{slug}/rsvp', [EventController::class, 'rsvp'])->name('events.rsvp');

    Route::get('/jobs', [JobController::class, 'index'])->name('jobs.index');
    Route::get('/jobs/create', [JobController::class, 'create'])
        ->middleware('can-post-job')->name('jobs.create');
    Route::post('/jobs', [JobController::class, 'store'])
        ->middleware(['can-post-job', 'throttle:5,60'])->name('jobs.store');
    Route::get('/jobs/mine', [JobController::class, 'mine'])->name('jobs.mine');
    Route::get('/jobs/{job}', [JobController::class, 'show'])->name('jobs.show');
    Route::get('/jobs/{job}/edit', [JobController::class, 'edit'])->name('jobs.edit');
    Route::patch('/jobs/{job}', [JobController::class, 'update'])->name('jobs.update');
    Route::delete('/jobs/{job}', [JobController::class, 'destroy'])->name('jobs.destroy');
    Route::post('/jobs/{job}/mark-filled', [JobController::class, 'markFilled'])->name('jobs.mark-filled');

    Route::get('/profile/{slug}', [ProfileController::class, 'show'])->name('profile.show');
});

// Donations: available to any verified user (including pending) — financial
// support shouldn't be gated behind alumni approval.
Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/donate', [DonationController::class, 'index'])->name('donate.index');
    Route::post('/donate/create-order', [DonationController::class, 'createOrder'])
        ->middleware('throttle:10,1')->name('donate.create-order');
    Route::post('/donate/verify', [DonationController::class, 'verify'])->name('donate.verify');
    Route::get('/donate/success/{donation}', [DonationController::class, 'success'])->name('donate.success');
    Route::get('/donate/{donation}/receipt', [DonationController::class, 'downloadReceipt'])->name('donate.receipt');
    Route::get('/donate/{slug}', [DonationController::class, 'show'])->name('donate.show');

    Route::get('/stories', [StoryController::class, 'index'])->name('stories.index');
    Route::get('/stories/submit', [StoryController::class, 'createSubmission'])
        ->middleware('alumni-or-admin')->name('stories.submit');
    Route::post('/stories', [StoryController::class, 'store'])
        ->middleware('alumni-or-admin')->name('stories.store');
    Route::get('/stories/mine', [StoryController::class, 'mine'])
        ->middleware('alumni-or-admin')->name('stories.mine');
    Route::get('/stories/{slug}', [StoryController::class, 'show'])->name('stories.show');

    Route::get('/surveys', [SurveyController::class, 'index'])->name('surveys.index');
    Route::get('/surveys/{survey}', [SurveyController::class, 'show'])->name('surveys.show');
    Route::post('/surveys/{survey}/respond', [SurveyController::class, 'respond'])->name('surveys.respond');
});

Route::post('/webhooks/razorpay', [\App\Http\Controllers\Webhooks\RazorpayController::class, 'handle'])
    ->name('webhooks.razorpay');

Route::middleware(['auth', 'verified', 'admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/events', [AdminEventController::class, 'index'])->name('events.index');
    Route::get('/events/create', [AdminEventController::class, 'create'])->name('events.create');
    Route::post('/events', [AdminEventController::class, 'store'])->name('events.store');
    Route::get('/events/{slug}/edit', [AdminEventController::class, 'edit'])->name('events.edit');
    Route::patch('/events/{slug}', [AdminEventController::class, 'update'])->name('events.update');
    Route::delete('/events/{slug}', [AdminEventController::class, 'destroy'])->name('events.destroy');

    Route::get('/campaigns', [AdminCampaignController::class, 'index'])->name('campaigns.index');
    Route::get('/campaigns/create', [AdminCampaignController::class, 'create'])->name('campaigns.create');
    Route::post('/campaigns', [AdminCampaignController::class, 'store'])->name('campaigns.store');
    Route::get('/campaigns/{slug}/edit', [AdminCampaignController::class, 'edit'])->name('campaigns.edit');
    Route::patch('/campaigns/{slug}', [AdminCampaignController::class, 'update'])->name('campaigns.update');
    Route::delete('/campaigns/{slug}', [AdminCampaignController::class, 'destroy'])->name('campaigns.destroy');

    Route::get('/donations', [AdminDonationController::class, 'index'])->name('donations.index');

    Route::get('/stories', [AdminStoryController::class, 'index'])->name('stories.index');
    Route::get('/stories/create', [AdminStoryController::class, 'create'])->name('stories.create');
    Route::post('/stories', [AdminStoryController::class, 'store'])->name('stories.store');
    Route::get('/stories/{story}/edit', [AdminStoryController::class, 'edit'])->name('stories.edit');
    Route::patch('/stories/{story}', [AdminStoryController::class, 'update'])->name('stories.update');
    Route::delete('/stories/{story}', [AdminStoryController::class, 'destroy'])->name('stories.destroy');
    Route::post('/stories/{story}/approve', [AdminStoryController::class, 'approve'])->name('stories.approve');
    Route::post('/stories/{story}/reject', [AdminStoryController::class, 'reject'])->name('stories.reject');

    Route::get('/surveys', [AdminSurveyController::class, 'index'])->name('surveys.index');
    Route::get('/surveys/create', [AdminSurveyController::class, 'create'])->name('surveys.create');
    Route::post('/surveys', [AdminSurveyController::class, 'store'])->name('surveys.store');
    Route::get('/surveys/{survey}/edit', [AdminSurveyController::class, 'edit'])->name('surveys.edit');
    Route::patch('/surveys/{survey}', [AdminSurveyController::class, 'update'])->name('surveys.update');
    Route::delete('/surveys/{survey}', [AdminSurveyController::class, 'destroy'])->name('surveys.destroy');
    Route::get('/surveys/{survey}/responses', [AdminSurveyController::class, 'responses'])->name('surveys.responses');

    Route::get('/feedback', [AdminFeedbackController::class, 'index'])->name('feedback.index');
    Route::post('/feedback/{feedback}/toggle', [AdminFeedbackController::class, 'toggle'])->name('feedback.toggle');
    Route::delete('/feedback/{feedback}', [AdminFeedbackController::class, 'destroy'])->name('feedback.destroy');
});

Route::middleware(['auth', 'verified', 'admin'])->group(function () {
    Route::get('/admin', function () {
        return Inertia::render('Admin/Index');
    })->name('admin.index');
});

Route::middleware('guest')->group(function () {
    Route::get('/auth/google/redirect', [GoogleController::class, 'redirect'])->name('google.redirect');
    Route::get('/auth/google/callback', [GoogleController::class, 'callback'])->name('google.callback');
});

require __DIR__.'/auth.php';
