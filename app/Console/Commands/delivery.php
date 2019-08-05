<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Http\Controllers\RelogController;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class delivery extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'relog:delivery {ttn}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Send data for cargo delivery to Relog';

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
        $lead=DB::table('ttn_new')
            ->join('waybill_ttn', 'ttn_new.id', '=', 'waybill_ttn.ttn_id')
            ->join('orders_new', 'waybill_ttn.waybill', '=', 'orders_new.waybill')
            ->where('ttn_new.id', $this->argument('ttn'))
            ->get();
        for ($i=0; $i < count($lead); $i++) { 
            if($lead[$i]->waybill != ''){
                $client = new \stdClass();
                $client->name           =   $lead[$i]->recipient;
                $client->phone          =   $lead[$i]->recipient_phone;
                $client->email          =   ' ';
                $client->description    =   $lead[$i]->recipient_contact_person;

                $good = new \stdClass();
                $good->name            = $lead[$i]->waybill;
                $good->code            = $lead[$i]->waybill;
                $good->volume          = 0;
                $good->quantity        = $lead[$i]->places;
                $good->price           = 0;
                $good->weight          = $lead[$i]->weight;
                $goods[0]              = $good;
                $application = new \stdClass();
                $application->additionalDetails             = ' ';
                $application->price                         = 0;
                $application->planDeliveryPeriod = new \stdClass();
                $application->planDeliveryPeriod->startDate = floor(microtime(true) * 1000);
                $application->planDeliveryPeriod->endDate   = floor(microtime(true) * 1000)+1982;
                $application->addressTo = new \stdClass();
                $application->addressTo->address            = $lead[$i]->recipient_address;
                $application->appType                       = 'delivery';
                $application->sender = new \stdClass();
                $application->sender->fullName='Spark Logistics.kz';
                $application->sender->phone='3450075';
                
                print_r($application);
                $out = RelogController::sendToRelog($client, $application, $goods);
                Log::info('Out: '.print_r($out, true));
                DB::table('orders_new')
                    ->where('lead_id', $lead[$i]->lead_id)
                    ->where('order_id', $lead[$i]->order_id)
                    ->update(['relog_id' => $out->_id]);
            }            
        }
    }
}
