<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Jobs\SendCode;

class Send1C extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'send:code {code}';

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
        $job = (new SendCode($this->argument('code')))->onQueue('codes');
        dispatch($job);
    }
}
