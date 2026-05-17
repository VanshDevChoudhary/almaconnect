<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\RosterEntry;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Inertia\Inertia;
use Inertia\Response;
use League\Csv\Reader;

class RosterController extends Controller
{
    public function index(Request $request): Response
    {
        $q = trim((string) $request->input('q', ''));

        $query = RosterEntry::latest();
        if ($q !== '') {
            $query->where(fn ($w) =>
                $w->where('name', 'like', "%{$q}%")
                    ->orWhere('email', 'like', "%{$q}%")
                    ->orWhere('batch', 'like', "%{$q}%")
            );
        }

        $entries = $query->paginate(50)->withQueryString();

        return Inertia::render('Admin/Roster/Index', [
            'entries' => $entries,
            'total' => RosterEntry::count(),
            'filters' => ['q' => $q],
        ]);
    }

    public function upload(Request $request): RedirectResponse
    {
        $request->validate([
            'csv' => ['required', 'file', 'mimes:csv,txt', 'max:5120'],
            'replace_all' => ['boolean'],
        ]);

        $path = $request->file('csv')->getRealPath();
        $csv = Reader::createFromPath($path, 'r');
        $csv->setHeaderOffset(0);

        $replaceAll = $request->boolean('replace_all', false);

        if ($replaceAll) {
            RosterEntry::truncate();
        }

        $imported = 0;
        $skipped = 0;
        $chunk = [];
        $now = Carbon::now()->toDateTimeString();

        foreach ($csv->getRecords() as $record) {
            $name = trim((string) ($record['name'] ?? ''));
            $batch = (int) trim((string) ($record['batch'] ?? '0'));
            $branch = trim((string) ($record['branch'] ?? ''));

            if (! $name || ! $batch || $batch < 1950 || $batch > 2100 || ! $branch) {
                $skipped++;
                continue;
            }

            $chunk[] = [
                'name' => $name,
                'email' => trim((string) ($record['email'] ?? '')) ?: null,
                'batch' => $batch,
                'branch' => $branch,
                'roll_no' => trim((string) ($record['roll_no'] ?? '')) ?: null,
                'created_at' => $now,
                'updated_at' => $now,
            ];
            $imported++;

            if (count($chunk) >= 500) {
                RosterEntry::insert($chunk);
                $chunk = [];
            }
        }

        if ($chunk) {
            RosterEntry::insert($chunk);
        }

        return back()->with('success', "{$imported} entries imported. {$skipped} skipped.");
    }

    public function destroy(RosterEntry $entry): RedirectResponse
    {
        $entry->delete();

        return back();
    }
}
