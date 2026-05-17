<?php

namespace App\Jobs;

use App\Mail\EventReminderMail;
use App\Models\Event;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class SendEventReminders implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function handle(): void
    {
        $events = Event::query()
            ->whereBetween('starts_at', [now()->addHours(24), now()->addHours(25)])
            ->get();

        foreach ($events as $event) {
            $recipients = $event->rsvps()
                ->where('status', 'going')
                ->with('user')
                ->get()
                ->pluck('user')
                ->filter(fn ($u) => $u && $u->email_verified_at !== null);

            foreach ($recipients as $user) {
                Mail::to($user->email)->queue(new EventReminderMail($event));
            }

            Log::info('Event reminders queued', [
                'event' => $event->slug,
                'recipients' => $recipients->count(),
            ]);
        }
    }
}
