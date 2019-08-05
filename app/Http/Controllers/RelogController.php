<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class RelogController extends Controller
{
	public static function sendToRelog($client, $application, $goods)
	{
		$link='https://app.relog.kz/api/v2/applications';
		$request = new \stdClass();
		$request->api_key='4af59265bd72486aaaa7515c8a67f43c';
		$request->client=$client;
		$request->application=$application;
		$request->goods=$goods;
		$curl=curl_init();
		curl_setopt($curl,CURLOPT_URL,$link);
		curl_setopt($curl,CURLOPT_CUSTOMREQUEST,'POST');
		curl_setopt($curl,CURLOPT_POSTFIELDS,json_encode($request));
		curl_setopt($curl,CURLOPT_RETURNTRANSFER, true);
		curl_setopt($curl,CURLOPT_HTTPHEADER,array('Content-Type: application/json'));
		$out=curl_exec($curl);
		$code=curl_getinfo($curl,CURLINFO_HTTP_CODE);
		curl_close($curl);
		$out=json_decode($out);
		return RelogController::validateResponce($out, $request);
	}

	public static function validateResponce($out, $request)
	{
		$link='https://app.relog.kz/api/v2/applications';
		$result=$out; 
		if(property_exists($out, 'errorResponse')){
			if($out->errorResponse->field=='phone'){
				$request->client->description=$request->client->phone;
				$request->client->phone='77027654321';
				$curl=curl_init();
				curl_setopt($curl,CURLOPT_URL,$link);
				curl_setopt($curl,CURLOPT_CUSTOMREQUEST,'POST');
				curl_setopt($curl,CURLOPT_POSTFIELDS,json_encode($request));
				curl_setopt($curl,CURLOPT_RETURNTRANSFER, true);
				curl_setopt($curl,CURLOPT_HTTPHEADER,array('Content-Type: application/json'));
				$out_phone=curl_exec($curl);
				$code=curl_getinfo($curl,CURLINFO_HTTP_CODE);
				curl_close($curl);
				$out_phone=json_decode($out_phone);
				print_r($out_phone);
				RelogController::validateResponce($out_phone, $request);
				$result=$out_phone;
			}
			if($out->errorResponse->field=='addressFrom'){
				$request->application->addressFrom->details=$request->application->addressFrom->address;
				$request->application->addressFrom->address='Алматы, улица Курмангазы, 72';
				$curl=curl_init();
				curl_setopt($curl,CURLOPT_URL,$link);
				curl_setopt($curl,CURLOPT_CUSTOMREQUEST,'POST');
				curl_setopt($curl,CURLOPT_RETURNTRANSFER, true);
				curl_setopt($curl,CURLOPT_POSTFIELDS,json_encode($request));
				curl_setopt($curl,CURLOPT_HTTPHEADER,array('Content-Type: application/json'));
				$out_addressFrom=curl_exec($curl);
				$code=curl_getinfo($curl,CURLINFO_HTTP_CODE);
				curl_close($curl);
				$out_addressFrom=json_decode($out_addressFrom);
				print_r($out_addressFrom);
				RelogController::validateResponce($out_addressFrom, $request);
				$result=$out_addressFrom;
			}
			if($out->errorResponse->field=='addressTo'){
				$request->application->addressTo->details=$request->application->addressTo->address;
				$request->application->addressTo->address='Алматы, улица Шарипова, 90';
				$curl=curl_init();
				curl_setopt($curl,CURLOPT_URL,$link);
				curl_setopt($curl,CURLOPT_CUSTOMREQUEST,'POST');
				curl_setopt($curl,CURLOPT_RETURNTRANSFER, true);
				curl_setopt($curl,CURLOPT_POSTFIELDS,json_encode($request));
				curl_setopt($curl,CURLOPT_HTTPHEADER,array('Content-Type: application/json'));
				$out_addressTo=curl_exec($curl);
				$code=curl_getinfo($curl,CURLINFO_HTTP_CODE);
				curl_close($curl);
				$out_addressTo=json_decode($out_addressTo);
				print_r($out_addressTo);
				RelogController::validateResponce($out_addressTo, $request);
				$result=$out_addressTo;
			}
		}
		return $result;
	}

	public function statusCheck()
	{
		$req     = file_get_contents('php://input');
		$req 	 = json_decode($req);
		$id 	 = $req->_id;
		$link	 ='https://app.relog.kz/api/v2/applications/?api_key=4af59265bd72486aaaa7515c8a67f43c&_id='.$id;
		$curl=curl_init();
		curl_setopt($curl,CURLOPT_URL,$link);
		curl_setopt($curl,CURLOPT_CUSTOMREQUEST,'GET');
		curl_setopt($curl,CURLOPT_RETURNTRANSFER, true);
		curl_setopt($curl,CURLOPT_HTTPHEADER,array('Content-Type: application/json'));
		$out=curl_exec($curl);
		$code=curl_getinfo($curl,CURLINFO_HTTP_CODE);
		curl_close($curl);
		$out=json_decode($out);
		if($out->applications[0]->appType=='pickUp'){
			if($out->applications[0]->status=='GjGXzcWPR7n4bZ4ui'){
				$lead=DB::table('leads_new')
					->where('relog_id', $id)
					->get();
				if (!$lead->isEmpty()) {
					DB::table('leads_new')
						->where('relog_id', $id)
						->update(['accepted_by_dispatcher'=>1, 'order_number' => '']);
				}
			}
			if($out->applications[0]->status=='h3vasqe4h9TWPm7dd'){
				$lead=DB::table('leads_new')
					->where('relog_id', $id)
					->get();
				if (!$lead->isEmpty()) {
					DB::table('leads_new')
						->where('relog_id', $id)
						->update(['taken_by_driver'=>1, 'order_number' => '']);
				}
			}
			if($out->applications[0]->status=='GDNjj2x348e9M5yr4'){
				$lead=DB::table('leads_new')
					->where('relog_id', $id)
					->get();
				if (!$lead->isEmpty()) {
					DB::table('leads_new')
						->where('relog_id', $id)
						->update(['transferred_to_driver'=>1, 'order_number' => '']);
				}
			}
		}
		if($out->applications[0]->appType=='delivery'){
			if($out->applications[0]->status=='GjGXzcWPR7n4bZ4ui'){
				$lead=DB::table('orders_new')
					->join('leads_new', 'orders_new.lead_id', '=', 'leads_new.lead_id')
					->where('orders_new.relog_id', $id)
					->get();
				if (!$lead->isEmpty()) {
					DB::table('orders_new')
						->where('relog_id', $id)
						->update(['shipping'=>1]);
					DB::table('leads_new')
						->where('lead_id', $lead->lead_id)
						->update(['order_number' => '']);
				}
			}
			if($out->applications[0]->status=='GDNjj2x348e9M5yr4'){
				$lead=DB::table('orders_new')
					->join('leads_new', 'orders_new.lead_id', '=', 'leads_new.lead_id')
					->where('orders_new.relog_id', $id)
					->get();
				if (!$lead->isEmpty()) {
					DB::table('orders_new')
						->where('relog_id', $id)
						->update(['shipped'=>1, 'accepted_by'=>$out->applications[0]->receiverInfo]);
					DB::table('leads_new')
						->where('lead_id', $lead->lead_id)
						->update(['order_number' => '']);
				}
			}
		}
		if($out->applications[0]->appType=='default'){
			if($out->applications[0]->status=='GjGXzcWPR7n4bZ4ui'){
				$lead=DB::table('orders_new')
					->join('leads_new', 'orders_new.lead_id', '=', 'leads_new.lead_id')
					->where('orders_new.relog_id', $id)
					->get();
				if (!$lead->isEmpty()) {
					DB::table('orders_new')
						->where('orders_new.relog_id', $id)
						->update(['accepted_by_dispatcher'=>1, 'taken_by_driver' =>1]);
					DB::table('leads_new')
						->where('lead_id', $lead->lead_id)
						->update(['order_number' => '']);
				}
			}
			if($out->applications[0]->status=='h3vasqe4h9TWPm7dd'){
				$lead=DB::table('orders_new')
					->join('leads_new', 'orders_new.lead_id', '=', 'leads_new.lead_id')
					->where('orders_new.relog_id', $id)
					->get();
				if (!$lead->isEmpty()) {
					DB::table('orders_new')
						->where('orders_new.relog_id', $id)
						->update(['transferred_to_driver' =>1, 'shipping'=>1]);
					DB::table('leads_new')
						->where('lead_id', $lead->lead_id)
						->update(['order_number' => '']);
				}
			}
			if($out->applications[0]->status=='GDNjj2x348e9M5yr4'){
				$lead=DB::table('orders_new')
					->join('leads_new', 'orders_new.lead_id', '=', 'leads_new.lead_id')
					->where('orders_new.relog_id', $id)
					->get();
				if (!$lead->isEmpty()) {
					DB::table('orders_new')
						->where('orders_new.relog_id', $id)
						->update(['shipped'=>1, 'accepted_by'=>$out->applications[0]->receiverInfo]);
					DB::table('leads_new')
						->where('lead_id', $lead->lead_id)
						->update(['order_number' => '']);
				}
			}
		}
	}
}
