<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreCampaignRequest;
use App\Models\DonationCampaign;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Intervention\Image\ImageManager;
use Inertia\Inertia;
use Inertia\Response;

class CampaignController extends Controller
{
    public function index(): Response
    {
        $campaigns = DonationCampaign::latest()->get()->map(fn ($c) => [
            'slug' => $c->slug,
            'title' => $c->title,
            'target_amount' => (int) $c->target_amount,
            'raised_amount' => (int) $c->raised_amount,
            'is_active' => $c->is_active,
            'ends_at' => $c->ends_at?->toDateString(),
        ]);

        return Inertia::render('Admin/Campaigns/Index', ['campaigns' => $campaigns]);
    }

    public function create(): Response
    {
        return Inertia::render('Admin/Campaigns/Create');
    }

    public function store(StoreCampaignRequest $request): RedirectResponse
    {
        $data = $request->validated();
        $data['is_active'] = $request->boolean('is_active', true);
        $data['raised_amount'] = 0;

        $campaign = DonationCampaign::create(collect($data)->except('cover_image')->all());

        if ($request->hasFile('cover_image')) {
            $campaign->update(['cover_image' => $this->storeCover($request, $campaign)]);
        }

        return redirect()->route('admin.campaigns.index')->with('success', 'Campaign created.');
    }

    public function edit(string $slug): Response
    {
        $campaign = DonationCampaign::where('slug', $slug)->firstOrFail();

        return Inertia::render('Admin/Campaigns/Edit', [
            'campaign' => [
                'slug' => $campaign->slug,
                'title' => $campaign->title,
                'description' => $campaign->description,
                'cover_image' => $campaign->cover_image,
                'target_amount' => $campaign->target_amount ? (int) $campaign->target_amount : null,
                'ends_at' => $campaign->ends_at?->toDateString(),
                'is_active' => $campaign->is_active,
            ],
        ]);
    }

    public function update(StoreCampaignRequest $request, string $slug): RedirectResponse
    {
        $campaign = DonationCampaign::where('slug', $slug)->firstOrFail();
        $data = $request->validated();
        $data['is_active'] = $request->boolean('is_active', true);

        $campaign->update(collect($data)->except('cover_image')->all());

        if ($request->hasFile('cover_image')) {
            if ($campaign->cover_image && Storage::disk('public')->exists($campaign->cover_image)) {
                Storage::disk('public')->delete($campaign->cover_image);
            }
            $campaign->update(['cover_image' => $this->storeCover($request, $campaign)]);
        }

        return redirect()->route('admin.campaigns.index')->with('success', 'Campaign updated.');
    }

    public function destroy(string $slug): RedirectResponse
    {
        $campaign = DonationCampaign::where('slug', $slug)->firstOrFail();

        if ($campaign->cover_image && Storage::disk('public')->exists($campaign->cover_image)) {
            Storage::disk('public')->delete($campaign->cover_image);
        }
        $campaign->delete();

        return redirect()->route('admin.campaigns.index')->with('success', 'Campaign deleted.');
    }

    private function storeCover(Request $request, DonationCampaign $campaign): string
    {
        $encoded = ImageManager::gd()
            ->read($request->file('cover_image')->getRealPath())
            ->scaleDown(width: 1920)
            ->toJpeg(85);

        $path = 'campaigns/'.$campaign->id.'-'.now()->timestamp.'-'.Str::lower(Str::random(6)).'.jpg';
        Storage::disk('public')->put($path, (string) $encoded);

        return $path;
    }
}
