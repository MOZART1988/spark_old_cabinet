<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Jobs\SendCodeTest;

class Send1CTest extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'send:code_test {code}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Sending lead code to API.';

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
        $job = (new SendCodeTest($this->argument('code')))->onQueue('codes');
        dispatch($job);
    }
}
