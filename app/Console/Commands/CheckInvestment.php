<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class CheckInvestment extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:check-investment';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command For Check All Investment Every Hour';

    /**
     * Execute the console command.
     */
    public function handle(): void
    {
        info("test");
    }
}
