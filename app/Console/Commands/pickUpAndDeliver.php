<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Http\Controllers\RelogController;
use Illuminate\Support\Facades\DB;

class pickUpAndDeliver extends Command
{
	/**
	 * The name and signature of the console command.
	 *
	 * @var string
	 */
	protected $signature = 'relog:pickUpAndDeliver {lead} {order}';

	/**
	 * The console command description.
	 *
	 * @var string
	 */
	protected $description = 'Send data for picking cargo up and deliver it to Relog';

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
		$lead=DB::table('orders_new')
			->join('leads_new', 'orders_new.lead_id', '=', 'leads_new.lead_id')
			->where('orders_new.lead_id', $this->argument('lead'))
			->where('order_id', $this->argument('order'))
			->get();


		$client = new \stdClass();
		$client->name		   	=   $lead[0]->sender_company_name;
		$client->phone		  	=   $lead[0]->sender_mobile_phone;
		$client->email		  	=   $lead[0]->sender_phone;
		$client->description	=   $lead[0]->recipient_contact_person.$lead[0]->recipient_phone;

		$application = new \stdClass();
		$application->planDeliveryPeriod = new \stdClass();
		$application->additionalDetails			 	= $lead[0]->sender_comments;
		$application->price						 	= 0;
		$application->planDeliveryPeriod->startDate	= floor(microtime(true) * 1000);
		$application->planDeliveryPeriod->endDate	= floor(microtime(true) * 1000)+1982;
		$application->addressTo = new \stdClass();
		$application->addressFrom = new \stdClass();
		$application->addressFrom->address			= $lead[0]->address_from;
		$application->addressTo->address			= $lead[0]->recipient_address;
		$application->appType						= 'default';
		$application->appType						= 'default';
		$application->sender = new \stdClass();
		$application->sender->fullName='Spark Logistics.kz';
		$application->sender->phone='3450075';

		$goods = new \stdClass();
		$goods->name			= 'Внутригородская доставка';
		$goods->code			= ' ';
		$goods->volume		 	= $lead[0]->volume_weight/200;
		$goods->quantity		= $lead[0]->places;
		$goods->price			= 0;
		$goods->weight			= $lead[0]->weight;
		$out = RelogController::sendToRelog($client, $application, $goods);
		DB::table('orders_new')
			->where('lead_id', $this->argument('lead'))
			->where('order_id', $this->argument('order'))
			->update(['relog_id' => $out->_id]);
	}
}