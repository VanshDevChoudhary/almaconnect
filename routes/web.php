<?php

use App\Http\Controllers\Auth\GoogleController;
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
    Route::get('/profile/{slug}', [ProfileController::class, 'show'])->name('profile.show');
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
