<?php

namespace Database\Seeders;

use App\Models\Comment;
use App\Models\Donation;
use App\Models\DonationCampaign;
use App\Models\Event;
use App\Models\EventRsvp;
use App\Models\Group;
use App\Models\Job;
use App\Models\Like;
use App\Models\Post;
use App\Models\Profile;
use App\Models\RosterEntry;
use App\Models\SuccessStory;
use App\Models\Survey;
use App\Models\SurveyQuestion;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Admin
        $admin = User::factory()->admin()->create([
            'name' => 'Alumni Cell Admin',
            'email' => 'admin@institute.edu',
            'password' => Hash::make('password'),
        ]);
        Profile::factory()->create(['user_id' => $admin->id]);

        // 2. Approved alumni (50)
        User::factory(50)->alumni()->create()->each(
            fn ($u) => Profile::factory()->create(['user_id' => $u->id])
        );

        // 3. Students (20)
        User::factory(20)->student()->create()->each(
            fn ($u) => Profile::factory()->create([
                'user_id' => $u->id,
                'batch' => fake()->numberBetween(2024, 2026),
            ])
        );

        // 4. Pending alumni (5)
        User::factory(5)->alumni()->pending()->create()->each(
            fn ($u) => Profile::factory()->create(['user_id' => $u->id])
        );

        // 5. Roster entries — matched (from approved alumni) + unmatched (variety)
        $approvedAlumni = User::where('role', 'alumni')
            ->where('status', 'approved')
            ->with('profile')
            ->get();

        foreach ($approvedAlumni as $alumnus) {
            RosterEntry::create([
                'name' => $alumnus->name,
                'email' => $alumnus->email,
                'batch' => $alumnus->profile->batch,
                'branch' => $alumnus->profile->branch,
                'roll_no' => 'R' . rand(10000, 99999),
            ]);
        }
        RosterEntry::factory(100)->create();

        // 6. Groups (10)
        $groups = collect([
            ['name' => 'Bay Area Alumni', 'type' => 'regional'],
            ['name' => 'Bangalore Chapter', 'type' => 'regional'],
            ['name' => 'Delhi NCR Network', 'type' => 'regional'],
            ['name' => 'Class of 2018', 'type' => 'batch'],
            ['name' => 'Class of 2020', 'type' => 'batch'],
            ['name' => 'Class of 2022', 'type' => 'batch'],
            ['name' => 'Founders Circle', 'type' => 'professional'],
            ['name' => 'ML/AI Practitioners', 'type' => 'interest'],
            ['name' => 'Higher Studies Aspirants', 'type' => 'interest'],
            ['name' => 'Career Switchers', 'type' => 'interest'],
        ])->map(fn ($g) => Group::factory()->create(array_merge($g, ['created_by' => $admin->id])));

        // 7. Group memberships — each approved user joins 2-4 groups
        User::where('status', 'approved')->get()->each(function ($user) use ($groups) {
            $groups->random(rand(2, 4))->each(
                fn ($g) => $g->members()->attach($user->id, [
                    'joined_at' => now()->subDays(rand(1, 365)),
                ])
            );
        });

        // 8. Posts (~30) distributed across groups
        Group::with('members')->get()->each(function ($g) use ($admin) {
            $authorId = $g->members->isEmpty() ? $admin->id : $g->members->random()->id;
            Post::factory(rand(2, 5))->create([
                'group_id' => $g->id,
                'user_id' => $authorId,
            ]);
        });

        // 9. Comments & likes
        $approvedIds = User::where('status', 'approved')->pluck('id');
        Post::all()->each(function ($p) use ($approvedIds) {
            $commentCount = rand(0, 5);
            for ($i = 0; $i < $commentCount; $i++) {
                Comment::factory()->create([
                    'post_id' => $p->id,
                    'user_id' => $approvedIds->random(),
                ]);
            }
            $approvedIds->shuffle()->take(rand(0, 10))->each(
                fn ($uid) => Like::create(['post_id' => $p->id, 'user_id' => $uid])
            );
        });

        // 10. Events (8: 5 upcoming, 3 past)
        Event::factory(5)->upcoming()->create(['created_by' => $admin->id]);
        Event::factory(3)->past()->create(['created_by' => $admin->id]);

        // 11. RSVPs for upcoming events
        Event::upcoming()->get()->each(function ($e) use ($approvedIds) {
            $approvedIds->shuffle()->take(rand(5, 15))->each(
                fn ($uid) => EventRsvp::create([
                    'event_id' => $e->id,
                    'user_id' => $uid,
                    'status' => fake()->randomElement(['going', 'interested', 'not_going']),
                ])
            );
        });

        // 12. Jobs (15: 10 active, 3 filled, 2 expired) — posted by existing alumni
        $alumniIds = User::where('role', 'alumni')->where('status', 'approved')->pluck('id');
        Job::factory(10)->active()->create(['posted_by' => fn () => $alumniIds->random()]);
        Job::factory(3)->filled()->create(['posted_by' => fn () => $alumniIds->random()]);
        Job::factory(2)->expired()->create(['posted_by' => fn () => $alumniIds->random()]);

        // 13. Donation campaigns (3)
        $campaigns = DonationCampaign::factory(3)->create();

        // 14. Donations (5-10 success per campaign) + raised_amount rollup
        $campaigns->each(function ($c) use ($alumniIds) {
            Donation::factory(rand(5, 10))->success()->create([
                'campaign_id' => $c->id,
                'user_id' => fn () => $alumniIds->random(),
            ]);
            $c->update([
                'raised_amount' => Donation::where('campaign_id', $c->id)
                    ->where('status', 'success')
                    ->sum('amount'),
            ]);
        });

        // 15. Success stories (10: 8 published, 2 pending) — featured/submitted by existing users
        SuccessStory::factory(8)->published()->create([
            'user_id' => fn () => $alumniIds->random(),
            'submitted_by' => $admin->id,
            'reviewed_by' => $admin->id,
        ]);
        SuccessStory::factory(2)->pending()->create([
            'user_id' => fn () => $alumniIds->random(),
            'submitted_by' => fn () => $alumniIds->random(),
        ]);

        // 16. Surveys (2 active) with questions
        $survey1 = Survey::factory()->create([
            'title' => 'Annual Alumni Engagement Survey 2026',
            'target_audience' => 'alumni',
            'created_by' => $admin->id,
        ]);
        SurveyQuestion::factory(3)->create(['survey_id' => $survey1->id]);

        $survey2 = Survey::factory()->create([
            'title' => 'Mentorship Program Interest',
            'target_audience' => 'all',
            'created_by' => $admin->id,
        ]);
        SurveyQuestion::factory(2)->create(['survey_id' => $survey2->id]);
    }
}
