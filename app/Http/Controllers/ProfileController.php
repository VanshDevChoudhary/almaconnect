<?php

namespace App\Http\Controllers;

use App\Http\Requests\UpdateProfileRequest;
use App\Http\Requests\UploadAvatarRequest;
use App\Models\Profile;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\ImageManager;
use Inertia\Inertia;
use Inertia\Response;

class ProfileController extends Controller
{
    public function show(string $slug): Response
    {
        $profile = Profile::with('user')->where('slug', $slug)->firstOrFail();
        $user = $profile->user;
        $viewer = request()->user();

        return Inertia::render('Profile/Show', [
            'profile' => [
                'slug' => $profile->slug,
                'batch' => $profile->batch,
                'branch' => $profile->branch,
                'roll_no' => $profile->roll_no,
                'current_company' => $profile->current_company,
                'current_role' => $profile->current_role,
                'industry' => $profile->industry,
                'city' => $profile->city,
                'country' => $profile->country,
                'bio' => $profile->bio,
                'skills' => $profile->skills ?? [],
                'linkedin_url' => $profile->linkedin_url,
                'website_url' => $profile->website_url,
            ],
            'profileUser' => [
                'id' => $user->id,
                'name' => $user->name,
                'avatar' => $user->avatar,
                'status' => $user->status,
            ],
            'isOwner' => $viewer->id === $user->id,
        ]);
    }

    public function edit(Request $request): Response
    {
        $user = $request->user();
        $profile = $user->profile;

        return Inertia::render('Profile/Edit', [
            'profileUser' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'avatar' => $user->avatar,
                'status' => $user->status,
            ],
            'profile' => [
                'slug' => $profile?->slug,
                'batch' => $profile?->batch,
                'branch' => $profile?->branch,
                'roll_no' => $profile?->roll_no,
                'current_company' => $profile?->current_company,
                'current_role' => $profile?->current_role,
                'industry' => $profile?->industry,
                'city' => $profile?->city,
                'country' => $profile?->country,
                'bio' => $profile?->bio,
                'skills' => $profile?->skills ?? [],
                'linkedin_url' => $profile?->linkedin_url,
                'website_url' => $profile?->website_url,
            ],
        ]);
    }

    public function update(UpdateProfileRequest $request): RedirectResponse
    {
        $data = $request->validated();
        $user = $request->user();

        $user->update(['name' => $data['name']]);

        // Normalise skills: trim, drop blanks, dedupe case-insensitively
        // while preserving the first-seen casing.
        $skills = collect($data['skills'] ?? [])
            ->map(fn ($s) => trim($s))
            ->filter()
            ->unique(fn ($s) => mb_strtolower($s))
            ->values()
            ->all();

        // Slug is intentionally NOT regenerated — the URL is stable.
        $user->profile()->updateOrCreate(
            ['user_id' => $user->id],
            [
                'batch' => $data['batch'] ?? null,
                'branch' => $data['branch'] ?? null,
                'roll_no' => $data['roll_no'] ?? null,
                'current_company' => $data['current_company'] ?? null,
                'current_role' => $data['current_role'] ?? null,
                'industry' => $data['industry'] ?? null,
                'city' => $data['city'] ?? null,
                'country' => $data['country'] ?? null,
                'bio' => $data['bio'] ?? null,
                'skills' => $skills,
                'linkedin_url' => $data['linkedin_url'] ?? null,
                'website_url' => $data['website_url'] ?? null,
            ]
        );

        return Redirect::route('profile.edit')->with('success', 'Profile updated.');
    }

    public function uploadAvatar(UploadAvatarRequest $request): JsonResponse
    {
        $user = $request->user();
        $file = $request->file('avatar');

        $encoded = ImageManager::gd()
            ->read($file->getRealPath())
            ->cover(400, 400)
            ->toJpeg(85);

        $path = 'avatars/'.$user->id.'-'.now()->timestamp.'.jpg';

        if ($user->avatar && Storage::disk('public')->exists($user->avatar)) {
            Storage::disk('public')->delete($user->avatar);
        }

        Storage::disk('public')->put($path, (string) $encoded);
        $user->update(['avatar' => $path]);

        // Avatar lives on the User, but the search index reads it through
        // the Profile — refresh the indexed document.
        $user->profile?->searchable();

        return response()->json([
            'avatar' => $path,
            'avatar_url' => Storage::url($path),
        ]);
    }

    /**
     * Account deletion (Breeze default — kept for capability; no Phase 3 UI).
     */
    public function destroy(Request $request): RedirectResponse
    {
        $request->validate([
            'password' => ['required', 'current_password'],
        ]);

        $user = $request->user();

        Auth::logout();

        $user->delete();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return Redirect::to('/');
    }
}
