<?php

namespace App\Http\Controllers;

use App\Jobs\SendLeadToStatus;
use App\Jobs\SendOrder;
use App\Jobs\SendLink;
use App\Jobs\SendCode;
use App\Jobs\sendComplete;
use App\Jobs\sendShipped;
use App\Jobs\SendPrice;
use App\Jobs\SendTasks;
use App\Jobs\SendWaybills;
use App\Jobs\GetAmoID;
use App\Waybill;
use Carbon\Carbon;
use DateTime;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class amoController extends Controller
{
	public function link()
	{        
		$link_id=$_POST['leads']['status'][0]['id'];
        Log::info('Providing link: '.$link_id);
		$job = (new SendLink($link_id))->onQueue('links');
        try {
            dispatch($job);
            Log::info('Link '.$link_id.' queued');
        }
        catch(\Exception $e) {
            Log::error('Link was not dispatched for id: '.$link_id);
        }
	}

	public function code($code)
	{
		$job = (new SendCode($code))->onQueue('codes');
		dispatch($job);
	}

    // public function tagContragents()
    // {
    //     $companies['update'] = [];
    //     $ids                 = json_decode(file_get_contents('/var/www/html/API/companyIDs.json'));
    //     $j=0;
    //     for ($i = 0; $i < 6; $i++) {
    //         for ($k=0; $k < 500; $k++) { 
    //             $array = array(
    //                 'id'         => $ids[$j]->id,
    //                 'updated_at' => "1528279050",
    //                 'tags'       => "Удалить",
    //             );
    //             $companies['update'][$j] = $array;
    //             if($j==2426){
    //                 break;
    //             }
    //             else{
    //                $j=$j+1; 
    //             }
    //         }            
    //         $job = (new tagContragents($companies))
    //             ->onQueue('ids');
    //         dispatch($job);
    //         if($j==2426){
    //             break;
    //         }
    //         else{
    //            $j=$j+1; 
    //         }
    //     }
    // }

    public function deleteLead($id)
    {
        try {
            $announcement_update = DB::table('leads_new')
                ->where('lead_id', $id)
                ->update(['is_deleted' => 1]);
            if($announcement_update > 0){
                Log::info('Lead '.$id.' marked for deletion');
            }
            else
            {
                 Log::error('Lead '.$id.' was not marked for deletion or already deleted');
            }
        } 
        catch (\Illuminate\Database\QueryException $ex) {
            Log::error('Lead '.$id.' was aimed to be marked as deleted, QueryException = '.print_r($ex, true));
        }
       
    }

    public function getLeadIDByOrderNumber($ordernumber)
    {        
        $lead_id = DB::table('leads_new')
            ->where('order_number', $ordernumber)
            ->select('lead_id')
            ->get();
        Log::info('Lead '.$lead_id.' is copied by order number');
        return json_encode($lead_id);
    }

    public function postOrdersOneLead($id)
    {
        $req              = file_get_contents('php://input');
        $req              = json_decode($req);
        $lead             = $req[0];
        $orders           = $req[1];
        $orders_finalized = [];
        $order            = [];
        $lead_arr         = [];
        $lead_id_sep;
        $rowsCount;
        for ($i = 0; $i < count($lead); $i++) {
            if ($lead[$i]->key == 'lead_id') {
                $lead_id_sep = $lead[$i]->value;
            }
            if ($lead[$i]->key == 'client') {
                $lead_arr['client_full_name'] = str_replace("&quot;", '"', $lead[$i]->value);
            }
            if ($lead[$i]->key == 'flexdatalist-sender') {
                $lead_arr['sender_company_name'] = str_replace("&quot;", '"', $lead[$i]->value);
            }
            if ($lead[$i]->key == 'filial') {
                $lead_arr['filial'] = $lead[$i]->value;
            }
            if ($lead[$i]->key == 'address') {
                $lead_arr['address_from'] = $lead[$i]->value;
            }
            if ($lead[$i]->key == 'contactPhone') {
                $lead_arr['sender_mobile_phone'] = $lead[$i]->value;
            }
            if ($lead[$i]->key == 'contactPerson') {
                $lead_arr['sender_person'] = $lead[$i]->value;
            }
            if ($lead[$i]->key == 'date') {
                $lead_arr['date_from'] = $lead[$i]->value;
            }
            if ($lead[$i]->key == 'cargo_ready_time') {
                $lead_arr['cargo_ready_time'] = $lead[$i]->value;
            }
            if ($lead[$i]->key == 'deliveryType_lead') {
                $lead_arr['delivery_type'] = $lead[$i]->value;
            }
            if ($lead[$i]->key == 'special') {
                $lead_arr['sender_comments'] = $lead[$i]->value;
            }
            if ($lead[$i]->key == 'rowsCount') {
                $rowsCount = $lead[$i]->value;
            }
            if ($lead[$i]->key == 'responsible') {
                $lead_arr['responsible'] = $lead[$i]->value;
            }
            if ($lead[$i]->key == 'orderNumber') {
                $lead_arr['order_number'] = '';
            }
            if ($lead[$i]->key == 'sender_cabinet') {
                $lead_arr['sender_cabinet'] = $lead[$i]->value;
            }
            if ($lead[$i]->key == 'author') {
                switch ($lead[$i]->value) {
                    case '2147611':
                        $lead_arr['author'] = 'Дюсенова Зарина';
                        break;
                    case '2180614':
                        $lead_arr['author'] = 'Нурканова Эльмира';
                        break;
                    case '2180617':
                        $lead_arr['author'] = 'Дворецкий Виталий';
                        break;
                    case '2180620':
                        $lead_arr['author'] = 'Жимбаева Эльмира';
                        break;
                    case '2180626':
                        $lead_arr['author'] = 'Краева Азалия';
                        break;
                    case '2180629':
                        $lead_arr['author'] = 'Аликулова Ляззат';
                        break;
                    case '2180632':
                        $lead_arr['author'] = 'Азамат Нурдаулетов';
                        break;
                    case '2180647':
                        $lead_arr['author'] = 'Ахметов Мейрулан';
                        break;
                    case '2180650':
                        $lead_arr['author'] = 'Шахиджанов Максим';
                        break;
                    case '2180650':
                        $lead_arr['author'] = 'Шаров Алексей';
                        break;
                    default:
                        $lead_arr['author'] = 'Сидиров Сергей';
                        break;
                }
            }
        }
        // $orderTemp=null;
        for ($j = 0; $j < $rowsCount; $j++) {
            for ($i = 0; $i < count($orders); $i++) {
                if ($orders[$i]->row == $j) {
                    if ($orders[$i]->key == 'direction' || $orders[$i]->key == 'currentStatus' || $orders[$i]->key == 'recipient') {

                    } else {
                        if ($orders[$i]->key == 'flexdatalist-direction') {
                            $d = DB::table('directions')
                                ->where('direction', $orders[$i]->value)
                                ->select('code')
                                ->get();
                            $order['direction'] = $d[0]->code;
                        } else {
                            if ($orders[$i]->key == 'flexdatalist-recipient') {
                                $order['recipient'] = $orders[$i]->value;
                            } else {
                                if ($orders[$i]->key == 'application_date') {
                                    $order[$orders[$i]->key] = date("Y-m-d", strtotime($orders[$i]->value));
                                } else {
                                    if ($orders[$i]->key == 'date_to') {
                                        $order[$orders[$i]->key] = date("Y-m-d", strtotime($orders[$i]->value));
                                    } else {
                                        if ($orders[$i]->key == 'oversized') {
                                            if ($orders[$i]->value == 1) {
                                                $order[$orders[$i]->key] = 1;
                                            } else {
                                                $order[$orders[$i]->key] = 0;
                                            }
                                        } else {
                                            // if($orders[$i]->key=='cubic_capacity'){
                                            //         $order['cubic_capacity']=$orders[$i]->value;
                                            //     }
                                            $order[$orders[$i]->key] = $orders[$i]->value;
                                        }
                                    }
                                }
                            }
                        }
                    }
                }
                $order['lead_id']  = $id;
                $order['order_id'] = $j + 1;
            }
            $orders_finalized[$j] = $order;
        }
        $orderToUpdate    = [];
        $orderToInsert    = [];
        $update_order_ids = [];
        // print_r($orders_finalized);
        $j = 0;
        $k = 0;
        for ($i = 0; $i < count($orders_finalized); $i++) {
            $or = DB::table('orders_new')
                ->where('lead_id', $orders_finalized[$i]['lead_id'])
                ->where('order_id', $orders_finalized[$i]['order_id'])
                ->get();
            if ($or->isEmpty()) {
                $ch = true;
            } else {
                $ch = false;
            }
            if ($ch) {
                // unset($orders_finalized[$i]['order_id']);
                $orderToInsert[$j]           = $orders_finalized[$i];
                $orderToInsert[$j]['weight'] = (double) $orderToInsert[$j]['weight'];
                $j                           = $j + 1;
            } else {
                $update_order_ids[$k] = $orders_finalized[$i]['order_id'];
                // unset($orders_finalized[$i]['order_id']);
                // unset($orders_finalized[$i]['lead_id']);
                $orderToUpdate[$k] = $orders_finalized[$i];
                $k                 = $k + 1;
            }
        }
        DB::table('orders_new')
            ->insert($orderToInsert);
        for ($i = 0; $i < count($update_order_ids); $i++) {
            DB::table('orders_new')
                ->where('order_id', $orderToUpdate[$i]['order_id'])
                ->where('lead_id', $id)
                ->update($orderToUpdate[$i]);
            // DB::table('orders_new')
            //         ->where('order_id', $update_order_ids[$i])
            //         ->update(['order_number'=>null]);
        }
        DB::table('leads_new')
            ->where('lead_id', $lead_id_sep)
            ->update($lead_arr);
        // DB::table('leads_new')
        //     ->where('lead_id', $lead_id_sep)
        //     ->update(['order_number'=>null]);

        $job = (new SendLeadToStatus($lead_id_sep, 'work'))->onQueue('amoSendToStatus');
        dispatch($job);
    }

    public function getOrdersByLead($id)
    {
        $orders = DB::table("orders_new")
            ->join("leads_new", 'leads_new.lead_id', '=', 'orders_new.lead_id')
            ->join("directions", 'orders_new.direction', '=', 'directions.code')
        // ->join("orders_statistics", "orders_new.order_id", "=", "orders_statistics.order_id")
            ->where('orders_new.lead_id', '=', $id)
            ->select('orders_new.order_id',
                'orders_new.order_creation_time',
                'orders_new.lead_id',
                'orders_new.waybill',
                'directions.direction',
                'orders_new.delivery_type_big',
                'orders_new.application_date',
                'orders_new.application_time',
                'orders_new.places',
                'orders_new.weight',
                'orders_new.volume_weight',
                'orders_new.paid_weight',
                'orders_new.cubic_capacity',
                'orders_new.oversized',
                'orders_new.lifting_capacity',
                'orders_new.quantity',
                'orders_new.hours',
                'orders_new.cargo_value',
                'orders_new.payment_type',
                'orders_new.cash_payment',
                'orders_new.payer',
                'orders_new.overcharged_payment',
                'orders_new.recipient',
                'orders_new.recipient_contact_person',
                'orders_new.recipient_address',
                'orders_new.recipient_phone',
                'orders_new.additional_services',
                'orders_new.date_to',
                'orders_new.accepted_by',
                'leads_new.accepted_by_dispatcher',
                'leads_new.sender_comments as special',
                'leads_new.taken_by_driver',
                'leads_new.transferred_to_driver',
                'leads_new.delivered_to_warehouse',
                'orders_new.ready_to_send',
                'orders_new.on_the_way',
                'orders_new.in_region',
                'orders_new.shipping',
                'orders_new.shipped',
                'orders_new.complete',
                'orders_new.order_number'
                // ,
                // 'orders_statistics.order_creation_time as statistics_order_creation_time',
                // 'orders_statistics.lead_creation_date as statistics_lead_creation_date',
                // 'orders_statistics.waybill as statistics_waybill',
                // 'orders_statistics.accepted_by_dispatcher as statistics_accepted_by_dispatcher',
                // 'orders_statistics.transferred_to_driver as statistics_transferred_to_driver',
                // 'orders_statistics.taken_by_driver as statistics_taken_by_driver',
                // 'orders_statistics.delivered_to_warehouse as statistics_delivered_to_warehouse',
                // 'orders_statistics.ready_to_send as statistics_ready_to_send',
                // 'orders_statistics.on_the_way as statistics_on_the_way',
                // 'orders_statistics.in_region as statistics_in_region',
                // 'orders_statistics.shipping as statistics_shipping',
                // 'orders_statistics.shipped as statistics_shipped',
                // 'orders_statistics.complete as statistics_complete'
            )
            ->get();
        return $orders;
    }

    public function getLead($id)
    {
        $lead = DB::table("leads_new")
            ->leftJoin("orders_new", 'leads_new.lead_id', '=', 'orders_new.lead_id')
            ->where('leads_new.lead_id', '=', $id)
            ->select('leads_new.lead_id', 'address_from as address', 'sender_mobile_phone as contactPhone', 'sender_company_name', 'filial', 'sender_person as contactPerson', 'sender_comments as special', 'courier', 'leads_new.order_number', 'order_creation_time', 'date_from', 'delivery_type', 'is_deleted', 'client_full_name', 'cargo_ready_time')
            ->get();
        return $lead;
    }

    public function getDirections()
    {
        $directions = DB::table('directions')
            ->select('direction', 'code')
            ->get();
        return $directions;
    }

    public function getContragents()
    {
        $names = DB::table('contragents')
            ->select('full_name')
            ->get();
        return $names;
    }

    public function getContragentsAsArray()
    {
        $names = DB::table('contragents')
            ->select('full_name')
            ->get();
        $names_array = [];
        for ($i=0; $i < count($names); $i++) { 
            $names_array[$i]=$names[$i]->full_name;
        }
        return json_encode($names_array);
    }

    public function getDataForFill($id)
    {
        $data = DB::table('orderTemplates')
            ->select('places', 'weight', 'volume as cubic_capacity', 'recipient as cabinet_recipient', 'paymentType')
            ->where('id', $id)
            ->get();
        return $data;
    }

    public static function closeLeads()
    {
        $leads = DB::table('leads_new')
            ->whereNotIn('lead_id',function($query){
               $query->select('lead_id')->from('orders_new')->where('accepted_by', '');
            })
            ->where('complete', 0)
            ->select('lead_id')
            ->get();
        $leads_chunked = 
            $leads
                ->chunk(500)
                ->map(function ($chunk) {
                    $chunk = $chunk->values();   // This "resets" the indices of all chunks
                    return $chunk;
                });
        for ($i=0; $i < count($leads_chunked); $i++) {
            $job = (new sendShipped($leads_chunked[$i]))->onQueue('shipped');
            dispatch($job);
        }
        $leads_array=$leads->toArray();
        for ($i=0; $i < count($leads_array); $i++) { 
            $leadsID_array[$i]=($leads_array[$i]->lead_id);
        }
        DB::table('leads_new')
            ->whereIn('lead_id', $leadsID_array)
            ->update(['complete' => 1]);
    }

    public static function sendPrices()
    {
        $leads = DB::table('orders_new')
            // ->whereNotIn('lead_id',function($query){
            //    $query->select('lead_id')->from('orders_new')->where('order_price', 0);
            // })
            // ->whereNotIn('lead_id',function($query){
            //    $query->select('lead_id')->from('leads_new')->where('price_updated', 1);
            // })
            ->select('lead_id', DB::raw('SUM(order_price) AS price'))
            ->groupBy('lead_id')
            ->get();
        $leads_chunked = 
            $leads
                ->chunk(500)
                ->map(function ($chunk) {
                    $chunk = $chunk->values();   // This "resets" the indices of all chunks
                    return $chunk;
                });
        for ($i=0; $i < count($leads_chunked); $i++) {
            $job = (new sendPrice($leads_chunked[$i]))->onQueue('prices');
            dispatch($job);
        }
        $leads_array=$leads->toArray();
        for ($i=0; $i < count($leads_array); $i++) { 
            $leadsID_array[$i]=($leads_array[$i]->lead_id);
        }
        // DB::table('leads_new')
        //     ->whereIn('lead_id', $leadsID_array)
        //     ->update(['price_updated' => 1]);
    }

    public static function sendComplete()
    {
        $leads = DB::table('leads_new')
            ->where('complete', 1)
            ->where('price_updated', 1)
            ->select('lead_id')
            ->get();
        $leads_chunked = 
            $leads
                ->chunk(500)
                ->map(function ($chunk) {
                    $chunk = $chunk->values();   // This "resets" the indices of all chunks
                    return $chunk;
                });
        for ($i=0; $i < count($leads_chunked); $i++) {
            $job = (new sendComplete($leads_chunked[$i]))->onQueue('complete');
            dispatch($job);
        }
    }

    public function setWaybills()
    {
        $leads = DB::table('orders_new')
            ->select(DB::raw('lead_id, GROUP_CONCAT(`waybill` SEPARATOR \'\n\') as waybills'))
            ->groupBy('lead_id')
            ->get();
        $leads_chunked = 
            $leads
                ->chunk(500)
                ->map(function ($chunk) {
                    $chunk = $chunk->values();   // This "resets" the indices of all chunks
                    return $chunk;
                });
        for ($i=0; $i < count($leads_chunked); $i++) {
            $job = (new SendWaybills($leads_chunked[$i]))->onQueue('waybills');
            dispatch($job);
        }
    }

    public function tasksForCalculation()
    {
        $lead_id=$_POST['leads']['status'][0]['id'];
        $tasks['add'] = array(
            #Привязываем к сделке
            array(
                'element_id'          => $lead_id, #ID сделки
                'element_type'        => 2, #Показываем, что это - сделка, а не контакт
                'task_type'           => 605278,
                'text'                => 'Если это не Ваш регион, либо нет ставки, поставьте прочерк.',
                'responsible_user_id' => 2180668,
                'complete_till_at'    => strtotime('tomorrow 12:00'),
            ),
            array(
                'element_id'          => $lead_id, #ID сделки
                'element_type'        => 2, #Показываем, что это - сделка, а не контакт
                'task_type'           => 605278,
                'text'                => 'Если это не Ваш регион, либо нет ставки, поставьте прочерк.',
                'responsible_user_id' => 2180665,
                'complete_till_at'    => strtotime('tomorrow 12:00'),
            ),
        );
        $job = (new SendTasks($tasks))->onQueue('tasks');
        try {
            dispatch($job);
            Log::info('Tasks '.$lead_id.' queued');
        }
        catch(\Exception $e) {
            Log::error('Tasks were not dispatched for id: '.$lead_id);
        }
    }

    public function setAmoIds()
    {
        $job = (new GetAmoID())->onQueue('contragentIDs');
        dispatch($job);
    }

   public function getLeadByID($id)
   {
       $lead = DB::table("leads_new")
            ->where('leads_new.lead_id', '=', $id)
            ->select('leads_new.lead_id', 'address_from', 'sender_mobile_phone', 'sender_company_name', 'filial', 'sender_person', 'sender_comments', 'courier', 'order_number', 'date_from', 'delivery_type', 'is_deleted', 'client_full_name', 'cargo_ready_time', 'responsible', 'author')
            ->get();
        $orders = DB::table("orders_new")
            ->join("leads_new", 'leads_new.lead_id', '=', 'orders_new.lead_id')
            ->join("directions", 'orders_new.direction', '=', 'directions.code')
            ->where('orders_new.lead_id', '=', $id)
            ->select('orders_new.order_id',
                'orders_new.order_creation_time',
                'orders_new.lead_id',
                'orders_new.waybill',
                'directions.direction',
                'orders_new.delivery_type_big',
                'orders_new.application_date',
                'orders_new.application_time',
                'orders_new.places',
                'orders_new.weight',
                'orders_new.volume_weight',
                'orders_new.paid_weight',
                'orders_new.cubic_capacity',
                'orders_new.oversized',
                'orders_new.lifting_capacity',
                'orders_new.quantity',
                'orders_new.hours',
                'orders_new.cargo_value',
                'orders_new.payment_type',
                'orders_new.cash_payment',
                'orders_new.payer',
                'orders_new.overcharged_payment',
                'orders_new.recipient',
                'orders_new.recipient_contact_person',
                'orders_new.recipient_address',
                'orders_new.recipient_phone',
                'orders_new.additional_services',
                'orders_new.date_to',
                'orders_new.accepted_by',
                'leads_new.accepted_by_dispatcher',
                'leads_new.sender_comments as special',
                'leads_new.taken_by_driver',
                'leads_new.transferred_to_driver',
                'leads_new.delivered_to_warehouse',
                'orders_new.ready_to_send',
                'orders_new.on_the_way',
                'orders_new.in_region',
                'orders_new.shipping',
                'orders_new.shipped',
                'orders_new.complete',
                'orders_new.order_number'
            )
            ->get();
        return json_encode([$lead, $orders]);
   }

   public function getStatistics()
   {
        $out=file_get_contents('http://spark-logistics.kz/1c_import/statistics.php');
        $waybills = json_decode($out);
        for ($i=0; $i < count($waybills); $i++) { 
            $waybills[$i]->order_creation_time=Carbon::createFromTimestamp( $waybills[$i]->order_creation_time)->toDateTimeString(); 
            $waybills[$i]->accepted_by_dispatcher=Carbon::createFromTimestamp( $waybills[$i]->accepted_by_dispatcher)->toDateTimeString(); 
            $waybills[$i]->transferred_to_driver=Carbon::createFromTimestamp( $waybills[$i]->transferred_to_driver)->toDateTimeString(); 
            $waybills[$i]->taken_by_driver=Carbon::createFromTimestamp( $waybills[$i]->taken_by_driver)->toDateTimeString(); 
            $waybills[$i]->delivered_to_warehouse=Carbon::createFromTimestamp( $waybills[$i]->delivered_to_warehouse)->toDateTimeString(); 
            $waybills[$i]->ready_to_send=Carbon::createFromTimestamp( $waybills[$i]->ready_to_send)->toDateTimeString(); 
            $waybills[$i]->on_the_way=Carbon::createFromTimestamp( $waybills[$i]->on_the_way)->toDateTimeString(); 
            $waybills[$i]->in_region=Carbon::createFromTimestamp( $waybills[$i]->in_region)->toDateTimeString(); 
            $waybills[$i]->shipping=Carbon::createFromTimestamp( $waybills[$i]->shipping)->toDateTimeString(); 
            $waybills[$i]->shipped=Carbon::createFromTimestamp( $waybills[$i]->shipped)->toDateTimeString(); 
            if (is_null($waybills[$i]->lead_id)) {
                $waybills[$i]->lead_id=0;
            }
        }
        for ($i=0; $i < count($waybills); $i++) {
            print_r('<pre>');
            $waybill=Waybill::where('waybill', $waybills[$i]->waybill)->first();
            if(is_null($waybill)){
                $waybill = new Waybill;
                $waybill->waybill=$waybills[$i]->waybill;
                $waybill->order_creation_time=$waybills[$i]->order_creation_time;
                $waybill->accepted_by_dispatcher=$waybills[$i]->accepted_by_dispatcher;
                $waybill->transferred_to_driver=$waybills[$i]->transferred_to_driver;
                $waybill->taken_by_driver=$waybills[$i]->taken_by_driver;
                $waybill->delivered_to_warehouse=$waybills[$i]->delivered_to_warehouse;
                $waybill->ready_to_send=$waybills[$i]->ready_to_send;
                $waybill->on_the_way=$waybills[$i]->on_the_way;
                $waybill->in_region=$waybills[$i]->in_region;
                $waybill->shipping=$waybills[$i]->shipping;
                $waybill->shipped=$waybills[$i]->shipped;
                $waybill->save();
            }
            else{
                $waybill->order_creation_time=$waybills[$i]->order_creation_time;
                $waybill->accepted_by_dispatcher=$waybills[$i]->accepted_by_dispatcher;
                $waybill->transferred_to_driver=$waybills[$i]->transferred_to_driver;
                $waybill->taken_by_driver=$waybills[$i]->taken_by_driver;
                $waybill->delivered_to_warehouse=$waybills[$i]->delivered_to_warehouse;
                $waybill->ready_to_send=$waybills[$i]->ready_to_send;
                $waybill->on_the_way=$waybills[$i]->on_the_way;
                $waybill->in_region=$waybills[$i]->in_region;
                $waybill->shipping=$waybills[$i]->shipping;
                $waybill->shipped=$waybills[$i]->shipped;
                $waybill->save();
            }
        }
        
        // print_r((array) $waybills[100]);
   }

   public function addCompany()
   {
              // $ContragentTemp = new ContragentTemp;
       // $ContragentTemp->
        DB::table('contragents_temp')
            ->where('amo_id', 33150261)
            ->update(['fact_address'=>$POST]);
   }
} 