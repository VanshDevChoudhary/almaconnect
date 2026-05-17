<?php

namespace App\Console\Commands;

use App\Models\Job;
use Illuminate\Console\Command;

class ExpireJobsCommand extends Command
{
    protected $signature = 'jobs:expire';

    protected $description = 'Expire active job listings past their expiry date';

    public function handle(): int
    {
        $count = Job::where('status', 'active')
            ->where('expires_at', '<', now())
            ->update(['status' => 'expired']);

        $this->info("Expired {$count} jobs.");

        return self::SUCCESS;
    }
}
