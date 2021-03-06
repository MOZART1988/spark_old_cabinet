<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Jobs\SendLeadToStatus;
use App\Http\Controllers\amoController;

class LeadComplete extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'send:complete';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Complete lead';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        amoController::sendComplete();
    }
}
