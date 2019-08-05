<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Jobs\task;

class addtask extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'send:task {lead_id} {text} {manager_name} {manager_id}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Add task to CRM';

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
        $job = (new task($this->argument('lead_id'), $this->argument('text'), $this->argument('manager_name'), $this->argument('manager_id')))->onQueue('amoTasks');
        dispatch($job);
    }
}
