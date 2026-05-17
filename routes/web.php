<?php

use App\Http\Controllers\Admin\EventController as AdminEventController;
use App\Http\Controllers\Auth\GoogleController;
use App\Http\Controllers\CommentController;
use App\Http\Controllers\DirectoryController;
use App\Http\Controllers\EventController;
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

Route::get('/dashboard', function () {
    return Inertia::render('Dashboard');
})->middleware(['auth', 'verified', 'alumni.approved'])->name('dashboard');

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

    Route::get('/profile/{slug}', [ProfileController::class, 'show'])->name('profile.show');
});

Route::middleware(['auth', 'verified', 'admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/events', [AdminEventController::class, 'index'])->name('events.index');
    Route::get('/events/create', [AdminEventController::class, 'create'])->name('events.create');
    Route::post('/events', [AdminEventController::class, 'store'])->name('events.store');
    Route::get('/events/{slug}/edit', [AdminEventController::class, 'edit'])->name('events.edit');
    Route::patch('/events/{slug}', [AdminEventController::class, 'update'])->name('events.update');
    Route::delete('/events/{slug}', [AdminEventController::class, 'destroy'])->name('events.destroy');
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
