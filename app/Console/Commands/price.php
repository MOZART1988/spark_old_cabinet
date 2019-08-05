<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Jobs\SendPrice;
use App\Http\Controllers\amoController;

class price extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'send:price';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Inserts lead prices into AmoCRM';

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
        amoController::sendPrices();
    }
}
