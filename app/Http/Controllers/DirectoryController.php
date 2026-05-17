<?php

namespace App\Http\Controllers;

use App\Models\Profile;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class DirectoryController extends Controller
{
    private const PER_PAGE = 24;

    private const FACETS = ['batch', 'branch', 'industry', 'city', 'country'];

    public function index(Request $request): Response
    {
        $q = $this->cleanQuery($request->input('q'));

        $filters = [];
        foreach (self::FACETS as $facet) {
            $values = $this->cleanFilter($request->input($facet, []));
            if ($facet === 'batch') {
                $values = array_values(array_map('intval', $values));
            }
            if (! empty($values)) {
                $filters[$facet] = $values;
            }
        }

        // Eager-load the user and hard-constrain to approved accounts —
        // defence in depth against index lag and engine differences.
        $search = Profile::search($q)->query(
            fn ($query) => $query->with('user')
                ->whereHas('user', fn ($u) => $u->where('status', 'approved'))
        );

        // Default ordering: newest graduates first. Requires `batch` in
        // Meilisearch sortableAttributes (configured in config/scout.php).
        $search->orderBy('batch', 'desc');

        foreach ($filters as $column => $values) {
            $search->whereIn($column, $values);
        }

        $paginator = $search->paginate(self::PER_PAGE)->withQueryString();

        $paginator->through(fn (Profile $p) => [
            'slug' => $p->slug,
            'user_id' => $p->user_id,
            'name' => $p->user?->name,
            'avatar' => $p->user?->avatar,
            'verified' => $p->user?->status === 'approved',
            'batch' => $p->batch,
            'branch' => $p->branch,
            'current_role' => $p->current_role,
            'current_company' => $p->current_company,
            'city' => $p->city,
            'country' => $p->country,
            'skills' => $p->skills ?? [],
        ]);

        return Inertia::render('Directory/Index', [
            'alumni' => $paginator,
            'availableFilters' => $this->facetDistribution($q),
            'appliedFilters' => (object) $filters,
            'searchQuery' => $q,
            'total' => $paginator->total(),
        ]);
    }

    private function cleanQuery(mixed $q): string
    {
        if (! is_string($q)) {
            return '';
        }

        $q = preg_replace('/[\x00-\x1F\x7F]/u', '', $q);

        return mb_substr(trim($q), 0, 200);
    }

    /**
     * @return array<int, string>
     */
    private function cleanFilter(mixed $values): array
    {
        if (! is_array($values)) {
            $values = [$values];
        }

        return collect($values)
            ->filter(fn ($v) => is_scalar($v) && $v !== '')
            ->map(fn ($v) => mb_substr((string) $v, 0, 100))
            ->unique()
            ->values()
            ->all();
    }

    /**
     * Facet counts from Meilisearch. Reflects the text query but not the
     * applied facet filters (standard faceted-search behaviour). Degrades
     * to an empty set when Meilisearch is not the active driver.
     *
     * @return array<string, array<string, int>>
     */
    private function facetDistribution(string $q): array
    {
        if (config('scout.driver') !== 'meilisearch') {
            return [];
        }

        try {
            $client = app(\Meilisearch\Client::class);
            $result = $client->index('profiles_index')->search($q, [
                'facets' => self::FACETS,
                'limit' => 0,
            ]);

            return $result->getFacetDistribution() ?? [];
        } catch (\Throwable $e) {
            return [];
        }
    }
}
