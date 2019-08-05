<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Http\Controllers\RelogController;
use Illuminate\Support\Facades\DB;

class pickUp extends Command
{
	/**
	 * The name and signature of the console command.
	 *
	 * @var string
	 */
	protected $signature = 'relog:pickUp {lead}';

	/**
	 * The console command description.
	 *
	 * @var string
	 */
	protected $description = 'Send data for picking cargo up to Relog';

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
		$lead=DB::table('leads_new')
				->where('lead_id', $this->argument('lead'))
				->get();
		if($lead->isEmpty()){
			$lead=DB::table('leads_1C')
				->where('lead_id', $this->argument('lead'))
				->get();
		}
		$client = new \stdClass();
		$client->name 			=	$lead[0]->sender_company_name;
		$client->phone			= 	$lead[0]->sender_mobile_phone;
		$client->email			= 	$lead[0]->sender_phone;
		$client->description 	= 	'';

		$application = new \stdClass();
		$application->additionalDetails				= $lead[0]->sender_comments.', '.$lead[0]->cargo_ready_time;
		$application->price							= 0;
		$application->planDeliveryPeriod = new \stdClass();
		$application->planDeliveryPeriod->startDate	= floor(microtime(true) * 1000);
		$application->planDeliveryPeriod->endDate	= floor(microtime(true) * 1000)+1982;
		$application->addressTo = new \stdClass();
		$application->addressTo->address			= $lead[0]->address_from;
		$application->appType						= 'pickUp';
		$application->sender = new \stdClass();
		$application->sender->fullName='Spark Logistics.kz';
		$application->sender->phone='3450075';
		
		$good = new \stdClass();
        $good->name            = 0;
        $good->code            = 0;
        $good->volume          = 0;
        $good->quantity        = 0;
        $good->price           = 0;
        $good->weight          = 0;
        $goods[0]              = $good;
		$out = RelogController::sendToRelog($client, $application, $goods);
		print_r($out);
		DB::table('leads_new')
            ->where('lead_id', $this->argument('lead'))
            ->update(['relog_id' => $out->_id]);
	}
}