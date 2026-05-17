<?php

use App\Models\Donation;
use App\Models\DonationCampaign;
use App\Models\RosterEntry;
use App\Models\SuccessStory;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use League\Csv\Reader;

function panelAdmin(): User
{
    return User::factory()->admin()->create(['email_verified_at' => now()]);
}

test('/admin is forbidden to non-admins and renders dashboard for admin', function () {
    $this->get(route('admin.index'))->assertRedirect(route('login'));

    $alumni = User::factory()->alumni()->create(['email_verified_at' => now(), 'status' => 'approved']);
    $this->actingAs($alumni)->get(route('admin.index'))->assertForbidden();

    $this->actingAs(panelAdmin())->get(route('admin.index'))
        ->assertInertia(fn ($p) => $p->component('Admin/Dashboard')->has('stats')->has('activity'));
});

test('admin counts are injected in shared props', function () {
    $adm = panelAdmin();
    User::factory(3)->create(['status' => 'pending']);
    SuccessStory::factory(2)->pending()->create(['user_id' => $adm->id]);

    $resp = $this->actingAs($adm)->get(route('admin.index'));
    $resp->assertInertia(fn ($p) =>
        $p->where('adminCounts.pending_verification', fn ($n) => $n >= 3)
          ->where('adminCounts.pending_stories', fn ($n) => $n >= 2)
    );
});

test('verification queue lists pending users with roster match annotation', function () {
    $adm = panelAdmin();
    $pending = User::factory()->alumni()->create(['status' => 'pending', 'email_verified_at' => now(), 'name' => 'Roster Match Test']);
    RosterEntry::create(['name' => $pending->name, 'email' => $pending->email, 'batch' => 2019, 'branch' => 'CSE']);

    $this->actingAs($adm)->get(route('admin.verification.index'))
        ->assertInertia(fn ($p) => $p->component('Admin/Verification/Index')->where('pending', fn($arr) => count($arr) >= 1));
});

test('admin can approve and reject pending users', function () {
    $adm = panelAdmin();
    $pending = User::factory()->create(['status' => 'pending', 'email_verified_at' => now()]);

    $this->actingAs($adm)->post(route('admin.users.approve', $pending->id))->assertRedirect();
    expect($pending->fresh()->status)->toBe('approved');

    $pending2 = User::factory()->create(['status' => 'pending', 'email_verified_at' => now()]);
    $this->actingAs($adm)->post(route('admin.users.reject', $pending2->id))->assertRedirect();
    expect($pending2->fresh()->status)->toBe('rejected');
});

test('admin bulk approve changes multiple user statuses', function () {
    $adm = panelAdmin();
    $users = User::factory(3)->create(['status' => 'pending', 'email_verified_at' => now()]);

    $this->actingAs($adm)
        ->post(route('admin.users.bulk-approve'), ['ids' => $users->pluck('id')->all()])
        ->assertRedirect();

    expect(User::whereIn('id', $users->pluck('id'))->where('status', 'approved')->count())->toBe(3);
});

test('admin user edit changes role and status', function () {
    $adm = panelAdmin();
    $user = User::factory()->student()->create(['email_verified_at' => now()]);

    $this->actingAs($adm)->patch(route('admin.users.update', $user->id), [
        'role' => 'alumni',
        'status' => 'approved',
    ])->assertRedirect(route('admin.users.index'));

    $user->refresh();
    expect($user->role)->toBe('alumni');
    expect($user->status)->toBe('approved');
});

test('CSV roster upload imports rows and Replace-all truncates', function () {
    $adm = panelAdmin();
    RosterEntry::factory(5)->create();

    $csv = "name,email,batch,branch,roll_no\nNew Alum 1,n1@x.com,2017,CSE,R1\nNew Alum 2,n2@x.com,2018,ECE,R2";
    $file = UploadedFile::fake()->createWithContent('roster.csv', $csv);

    $this->actingAs($adm)->post(route('admin.roster.upload'), ['csv' => $file])
        ->assertRedirect();

    expect(RosterEntry::count())->toBe(7); // 5 existing + 2 new

    $csv2 = "name,email,batch,branch\nOnly One,o@x.com,2015,ME";
    $file2 = UploadedFile::fake()->createWithContent('r2.csv', $csv2);

    $this->actingAs($adm)->post(route('admin.roster.upload'), [
        'csv' => $file2,
        'replace_all' => '1',
    ])->assertRedirect();

    expect(RosterEntry::count())->toBe(1);
});

test('CSV upload skips rows with invalid batch year', function () {
    $adm = panelAdmin();
    $csv = "name,email,batch,branch\nBad Row,,notayear,CSE\nGood Row,good@x.com,2020,ECE";
    $file = UploadedFile::fake()->createWithContent('r.csv', $csv);

    $this->actingAs($adm)->post(route('admin.roster.upload'), ['csv' => $file])->assertRedirect();

    // Only the valid row imported
    expect(RosterEntry::where('name', 'Good Row')->exists())->toBeTrue();
    expect(RosterEntry::where('name', 'Bad Row')->exists())->toBeFalse();
});

test('donations export returns a CSV file', function () {
    $adm = panelAdmin();
    $campaign = DonationCampaign::factory()->create();
    $donor = User::factory()->alumni()->create();
    Donation::factory()->create(['user_id' => $donor->id, 'campaign_id' => $campaign->id, 'status' => 'success']);

    $resp = $this->actingAs($adm)->get(route('admin.donations.export'));
    $resp->assertOk();
    // Content-Disposition confirms CSV download was initiated
    expect($resp->headers->get('content-disposition'))->toContain('donations.csv');
});

test('admin settings page is accessible', function () {
    $this->actingAs(panelAdmin())->get(route('admin.settings.index'))
        ->assertOk()
        ->assertInertia(fn ($p) => $p->component('Admin/Settings/Index'));
});
