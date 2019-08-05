<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Http\Controllers\amoController;

class getStatistics extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'statistics:get';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Get statistics from 1C';

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
        amoController::getStatistics();
    }
}
