<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Jobs\SendLeadToStatus;
use App\Http\Controllers\amoController;

class LeadShipped extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'send:shipped';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Send leads to shipped column into AmoCRM';

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
        amoController::closeLeads();
    }
}
