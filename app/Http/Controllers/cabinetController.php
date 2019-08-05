<?php

namespace App\Http\Controllers;

use App\Jobs\SendCourier;
use App\Jobs\SendLeadToStatus;
use App\Jobs\SendOrder;
use App\Jobs\SendMassOrder;
use App\Jobs\Callback;
use Carbon\Carbon;
use DateTime;
use Exception;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use App\Mail\newOrder;

class cabinetController extends Controller
{
    public function introComplete($id)
    {
        $headers = apache_request_headers();
        if (!isset($headers['token'])) {
            print_r("unauthorized");
        } else {
            $token = $headers['token'];
            if ($id == 1) {
                DB::table('cabinet_users')
                    ->join('contragents', 'contragents.id', '=', 'cabinet_users.id')
                    ->join('tokens', 'contragents.id', '=', 'tokens.id')
                    ->where('token', $token)
                    ->update(['intro_main' => 1]);
            } else {
                if ($id == 2) {
                    DB::table('cabinet_users')
                        ->join('contragents', 'contragents.id', '=', 'cabinet_users.id')
                        ->join('tokens', 'contragents.id', '=', 'tokens.id')
                        ->where('token', $token)
                        ->update(['intro_order' => 1]);
                } else {
                    if ($id == 3) {
                        DB::table('cabinet_users')
                            ->join('contragents', 'contragents.id', '=', 'cabinet_users.id')
                            ->join('tokens', 'contragents.id', '=', 'tokens.id')
                            ->where('token', $token)
                            ->update(['intro_tracking' => 1]);
                    } else {
                        if ($id == 4) {
                            DB::table('cabinet_users')
                                ->join('contragents', 'contragents.id', '=', 'cabinet_users.id')
                                ->join('tokens', 'contragents.id', '=', 'tokens.id')
                                ->where('token', $token)
                                ->update(['intro_reports' => 1]);
                        }
                    }
                }
            }

        }
    }

    public function addOrderFast()
    {
        $req     = file_get_contents('php://input');
        $req     = json_decode($req);
        $headers = apache_request_headers();
        if (!isset($headers['token'])) {
            print_r("unauthorized");
        } else {
            $token        = $headers['token'];
            $sender_array = DB::table('tokens')
                ->join('contragents', 'contragents.id', '=', 'tokens.id')
                ->select('contragents.amo_id', 'contragents.id', 'contragents.manager')
                ->where('token', $token)
                ->get();
            $leads = null;
            if ($sender_array[0]->manager != '') {
                $manager = \Config::get('managers.manager_names.' . $sender_array[0]->manager);
                if (!$manager) {
                    $manager = \Config::get('managers.manager_names.' . 'Дюсенова Зарина');
                }
            } else {
                $manager = \Config::get('managers.manager_names.' . 'Дюсенова Зарина');
            }
            $leads['add'] = array(
                array(
                    'name'                => 'Вызов курьера от #' . time(),
                    'company_id'          => $sender_array[0]->amo_id,
                    'responsible_user_id' => $manager,
                    'custom_fields'       => array(
                        array(
                            'id'     => 452897,
                            'values' => array(
                                array(
                                    'value' => $req->person,
                                ),
                            ),
                        ),
                        array(
                            'id'     => 452899,
                            'values' => array(
                                array(
                                    'value' => $req->phone,
                                ),
                            ),
                        ),
                    ),
                ),
            );
            $job = (new SendCourier($sender_array[0]->id, $sender_array[0]->amo_id, $req->person, $req->phone, $leads))->onQueue('couriers');
            dispatch($job);
        }
    }

    public function getHotDirections()
    {
        $headers = apache_request_headers();
        if (!isset($headers['token'])) {
            print_r("unauthorized");
        } else {
            $token         = $headers['token'];
            $client=DB::table('tokens')
                ->join('contragents', 'contragents.id', '=', 'tokens.id')
                ->where('token', $token)
                ->select('contragents.id')
                ->get();
            if($client[0]->id=='100440007349'){
                $allDirections = DB::table('orders_new')
                    ->join('directions', 'orders_new.direction', '=', 'directions.code')
                    ->join('leads_new', 'orders_new.lead_id', '=', 'leads_new.lead_id')
                    ->where('leads_new.sender_cabinet', 'WellDis LLP')
                    ->groupBy('directions.direction')
                    ->select('directions.direction', DB::raw('COUNT(directions.direction) as number'))
                    ->orderBy('number', 'desc')
                    ->get();
            }
            else{
                $allDirections = DB::table('orders_new')
                    ->join('directions', 'orders_new.direction', '=', 'directions.code')
                    ->join('leads_new', 'orders_new.lead_id', '=', 'leads_new.lead_id')
                    ->join('contragents', 'leads_new.client_full_name', '=', 'contragents.full_name')
                    ->join('tokens', 'contragents.id', '=', 'tokens.id')
                    ->where('tokens.token', $token)
                    ->groupBy('directions.direction')
                    ->select('directions.direction', DB::raw('COUNT(directions.direction) as number'))
                    ->orderBy('number', 'desc')
                    ->get();
            }            
            $directions = [];
            for ($i = 0; $i < count($allDirections); $i++) {
                $allDirections[$i]->from  = (explode(" - ", $allDirections[$i]->direction))[0];
                $allDirections[$i]->to    = (explode(" - ", $allDirections[$i]->direction))[1];
                $directions[$i][0]        = new \stdClass();
                $directions[$i][1]        = new \stdClass();
                $directions[$i][0]->name  = $allDirections[$i]->from;
                $directions[$i][0]->value = 95;
                $directions[$i][1]->name  = $allDirections[$i]->to;
                $directions[$i][1]->value = 95;
            }
            $grouped          = $allDirections->groupBy('from');
            $destinationsList = $allDirections->groupBy('to');
            $groupCount       = $grouped->map(function ($item, $key) {
                return collect($item)->count();
            });
            $destinationsCount = $destinationsList->map(function ($item, $key) {
                return collect($item)->count();
            });
            $coordinates = DB::table('city_coordinates')
                ->whereIn('city', array_merge(array_keys($destinationsCount->toArray()), array_keys($groupCount->toArray())))
                ->get();
            $coords = new \stdClass();
            for ($i = 0; $i < count($coordinates); $i++) {
                $coords->{$coordinates[$i]->city} = [$coordinates[$i]->longitude, $coordinates[$i]->latitude];
            }
            print_r(json_encode([$coords, $directions]));
        }
    }

    public function getOrdersData()
    {
        $headers = apache_request_headers();
        if (!isset($headers['token'])) {
            print_r("unauthorized");
        } else {
            $token         = $headers['token'];
            $client=DB::table('tokens')
                ->join('contragents', 'contragents.id', '=', 'tokens.id')
                ->where('token', $token)
                ->select('contragents.id')
                ->get();
            if($client[0]->id=='100440007349'){
                $orderdata = DB::table('orders_new')
                    ->join('leads_new', 'orders_new.lead_id', '=', 'leads_new.lead_id')
                    ->where('leads_new.sender_cabinet', 'WellDis LLP')
                    ->whereBetween('application_date', [Carbon::now()->subMonth(), Carbon::now()])
                    ->select('application_date', 'weight', 'cubic_capacity')
                    ->get();
            }
            else{
                $orderdata = DB::table('orders_new')
                    ->join('leads_new', 'orders_new.lead_id', '=', 'leads_new.lead_id')
                    ->join('contragents', 'leads_new.client_full_name', '=', 'contragents.full_name')
                    ->join('tokens', 'contragents.id', '=', 'tokens.id')
                    ->whereBetween('application_date', [Carbon::now()->subMonth(), Carbon::now()])
                    ->where('tokens.token', $headers['token'])
                    ->select('application_date', 'weight', 'cubic_capacity')
                    ->get();
            }
            
            for ($i = 0; $i < count($orderdata); $i++) {
                $format                          = "Y-m-d";
                $dateobj                         = DateTime::createFromFormat($format, $orderdata[$i]->application_date);
                $orderdata[$i]->application_date = $dateobj->getTimestamp();
            }
            $timestamps = array_keys($orderdata->groupBy('application_date')->toArray());
            $orders     = [];
            $data       = [];
            for ($i = 0; $i < count($timestamps); $i++) {
                $data[$i] = $orderdata->groupBy('application_date')[$timestamps[$i]]->sum('weight');
            }
            if (count($data) == 0) {
                $timestamps[0] = 0;
                $data[0]       = 0;
            }
            // print_r('<pre>');
            print_r(json_encode([$timestamps, $data]));
        }
    }

    public function getNewestNotifications()
    {
        $headers = apache_request_headers();
        if (!isset($headers['token'])) {
            print_r("unauthorized");
        } else {
            $token         = $headers['token'];
            $notifications = DB::table('notifications')
                ->join('tokens', 'notifications.userId', '=', 'tokens.id')
                ->where('token', $token)
                ->orderBy('id', 'desc')
                ->select('notifications.id', 'notifications.text', 'notifications.readByUser', 'notifications.date')
                ->take(8)
                ->get();
            header('Content-Type: application/json');
            $json = json_encode($notifications, JSON_PRETTY_PRINT);
            print_r($json);
        }
    }

    public function getUserProfile()
    {
        $headers = apache_request_headers();
        if (!isset($headers['token'])) {
            print_r("unauthorized");
        } else {
            $token = $headers['token'];
            $data  = DB::table('cabinet_users')
                ->join('tokens', 'cabinet_users.id', '=', 'tokens.id')
                ->where('tokens.token', $token)
                ->select('cabinet_users.id', 'full_name', 'email', 'work_phone', 'fact_address', 'name', 'bank_account', 'skype', 'telegram', 'whatsapp', 'work_phone_int_code', 'mobile_phone')
                ->get();
            print_r(json_encode($data));
        }
    }

    public function addOrder()
    {
        $req     = file_get_contents('php://input');
        $req     = json_decode($req);
        $headers = apache_request_headers();
        if (!isset($headers['token'])) {
            print_r("unauthorized");
        } else {
            $token        = $headers['token'];
            $sender_array = DB::table('tokens')
                ->join('contragents', 'contragents.id', '=', 'tokens.id')
                ->where('token', $token)
                ->get();

            // print_r($sender_array[0]->amo_id);
            // Type 0 - Without saving any template
            // Type 1 - With saving order template
            // Type 2 - With saving sender template
            // Type 3 - With saving recipient template
            // Type 4 - With saving sender and recipient template
            // Type 5 - With saving order and recipient template
            // Type 6 - With saving order and sender template
            // Type 7 - With saving order, sender and recipient template

            $req->delivery = new \stdClass();
            switch ($req->deliveryType) {
                case '1':
                    $req->delivery->type = "Авто";
                    break;
                case '2':
                    $req->delivery->type = "Авиа";
                    break;
                case '3':
                    $req->delivery->type = "ЖД";
                    break;
                default:
                    break;
            }            

            if ($req->jurstatus == 0) {
                $req->jurstatus = 'Физическое лицо';
            } else {
                $req->jurstatus = 'Юридическое лицо';
            }

            if ($req->type == 0) {
                $this->addOrderToTemplates($req, $sender_array[0]->id);
            } elseif ($req->type == 1) {
                $this->addOrderTemplate($req, $sender_array[0]->id);
            } elseif ($req->type == 2) {
                $this->addOrderToTemplates($req, $sender_array[0]->id);
                $this->addSenderTemplate($req, $sender_array[0]->id);
            } elseif ($req->type == 3) {
                $this->addOrderToTemplates($req, $sender_array[0]->id);
                $this->addRecipientTemplate($req, $sender_array[0]->id);
            } elseif ($req->type == 4) {
                $this->addOrderToTemplates($req, $sender_array[0]->id);
                $this->addSenderTemplate($req, $sender_array[0]->id);
                $this->addRecipientTemplate($req, $sender_array[0]->id);
            } elseif ($req->type == 5) {
                $this->addOrderTemplate($req, $sender_array[0]->id);
                $this->addRecipientTemplate($req, $sender_array[0]->id);
            } elseif ($req->type == 6) {
                $this->addSenderTemplate($req, $sender_array[0]->id);
                $this->addOrderTemplate($req, $sender_array[0]->id);
            } elseif ($req->type == 7) {
                $this->addSenderTemplate($req, $sender_array[0]->id);
                $this->addOrderTemplate($req, $sender_array[0]->id);
                $this->addRecipientTemplate($req, $sender_array[0]->id);
            }

            switch ($req->payment) {
                case 'senderPayContract':
                    $req->payment = "Оплата получателем по договору";
                    break;
                case 'recipientPayContract':
                    $req->payment = "Оплата отправителем по договору";
                    break;
                case 'senderPayCash':
                    $req->payment = "Оплата наличными при отправлении";
                    break;
                case 'recipientPayCash':
                    $req->payment = "Оплата наличными при получении";
                    break;
                default:
                    break;
            }

            //Creation of lead with general information
            $leads = null;
            if ($sender_array[0]->manager != '') {
                $manager = \Config::get('managers.manager_names.' . $sender_array[0]->manager);
                if (!$manager) {
                    $manager = \Config::get('managers.manager_names.' . 'Дюсенова Зарина');
                }
            } else {
                $manager = \Config::get('managers.manager_names.' . 'Дюсенова Зарина');
            }
            $id       = DB::table('orderTemplates')
                ->max('id');
            $leads['add'] = [
                [
                    'name'                => 'Сделка #' . time(),
                    'company_id'          => $sender_array[0]->amo_id,
                    'tags'                => 'ЛичныйКабинет',
                    'responsible_user_id' => $manager,
                    'custom_fields'       => [
                        [
                            'id'     => \Config::get('amoids.fields.address'),
                            'values' => [
                                [
                                    'value' => $req->senderCountryText . ', ' . $req->senderCity . ', ' . $req->senderStreet . ', ' . $req->senderBuilding . ', ' . $req->senderApartments,
                                ],
                            ],
                        ],
                        [
                            'id'     => \Config::get('amoids.fields.templateID'),
                            'values' => [
                                [
                                    'value' => $id,
                                ],
                            ],
                        ],
                        [
                            'id'     => \Config::get('amoids.fields.direction'),
                            'values' => [
                                [
                                    'value' => $req->senderCity . ' - ' . $req->recipientCity,
                                ],
                            ],
                        ],
                        [
                            'id'     => \Config::get('amoids.fields.deliiveryType'),
                            'values' => [
                                [
                                    'value' => $req->delivery->type,
                                ],
                            ],
                        ],
                        [
                            'id'     => \Config::get('amoids.fields.senderComments'),
                            'values' => [
                                [
                                    'value' => $req->special,
                                ],
                            ],
                        ],
                        [
                            'id'     => \Config::get('amoids.fields.senderCountry'),
                            'values' => [
                                [
                                    'value' => $req->senderCountryText,
                                ],
                            ],
                        ],
                        [
                            'id'     => \Config::get('amoids.fields.senderCity'),
                            'values' => [
                                [
                                    'value' => $req->senderCity,
                                ],
                            ],
                        ],
                        [
                            'id'     => \Config::get('amoids.fields.senderStreet'),
                            'values' => [
                                [
                                    'value' => $req->senderStreet,
                                ],
                            ],
                        ],
                        [
                            'id'     => \Config::get('amoids.fields.senderHouse'),
                            'values' => [
                                [
                                    'value' => $req->senderBuilding,
                                ],
                            ],
                        ],
                        [
                            'id'     => \Config::get('amoids.fields.senderApartments'),
                            'values' => [
                                [
                                    'value' => $req->senderApartments,
                                ],
                            ],
                        ],
                        [
                            'id'     => \Config::get('amoids.fields.senderContactPerson'),
                            'values' => [
                                [
                                    'value' => $req->senderContactPerson,
                                ],
                            ],
                        ],
                        [
                            'id'     => \Config::get('amoids.fields.senderContactPhone'),
                            'values' => [
                                [
                                    'value' => $req->senderContactPhone,
                                ],
                            ],
                        ],
                        [
                            'id'     => \Config::get('amoids.fields.recipientName'),
                            'values' => [
                                [
                                    'value' => $req->recipient,
                                ],
                            ],
                        ],
                        [
                            'id'     => \Config::get('amoids.fields.recipientJurStatus'),
                            'values' => [
                                [
                                    'value' => $req->jurstatus,
                                ],
                            ],
                        ],
                        [
                            'id'     => \Config::get('amoids.fields.recipientBin'),
                            'values' => [
                                [
                                    'value' => $req->bin,
                                ],
                            ],
                        ],
                        [
                            'id'     => \Config::get('amoids.fields.recipientCountry'),
                            'values' => [
                                [
                                    'value' => $req->recipientCountryText,
                                ],
                            ],
                        ],
                        [
                            'id'     => \Config::get('amoids.fields.recipientCity'),
                            'values' => [
                                [
                                    'value' => $req->recipientCity,
                                ],
                            ],
                        ],
                        [
                            'id'     => \Config::get('amoids.fields.recipientStreet'),
                            'values' => [
                                [
                                    'value' => $req->recipientStreet,
                                ],
                            ],
                        ],
                        [
                            'id'     => \Config::get('amoids.fields.recipientHouse'),
                            'values' => [
                                [
                                    'value' => $req->recipientBuilding,
                                ],
                            ],
                        ],
                        [
                            'id'     => \Config::get('amoids.fields.recipientApartments'),
                            'values' => [
                                [
                                    'value' => $req->recipientApartments,
                                ],
                            ],
                        ],
                        [
                            'id'     => \Config::get('amoids.fields.recipientContactPerson'),
                            'values' => [
                                [
                                    'value' => $req->recipientContactPerson,
                                ],
                            ],
                        ],
                        [
                            'id'     => \Config::get('amoids.fields.recipientPhone'),
                            'values' => [
                                [
                                    'value' => $req->recipientContactPhone,
                                ],
                            ],
                        ],
                        [
                            'id'     => \Config::get('amoids.fields.places'),
                            'values' => [
                                [
                                    'value' => $req->placesAmount,
                                ],
                            ],
                        ],
                        [
                            'id'     => \Config::get('amoids.fields.weight'),
                            'values' => [
                                [
                                    'value' => $req->weight,
                                ],
                            ],
                        ],
                        [
                            'id'     => \Config::get('amoids.fields.volume'),
                            'values' => [
                                [
                                    'value' => $req->volume,
                                ],
                            ],
                        ],
                        [
                            'id'     => \Config::get('amoids.fields.payment'),
                            'values' => [
                                [
                                    'value' => $req->payment,
                                ],
                            ],
                        ],

                    ],
                ],
            ];

            // $noteText = (!empty($sender->phone) ? 'Звонить по телефону: ' . $sender->phone . "\n" : '') . 'Количество мест к отправлению: ' . $characteristics->placesAmount . "\n" . 'Вес груза: ' . $characteristics->weight . "\n" . 'объём груза: ' . $characteristics->volume . ' м³' . "\n" . 'Тип доставки: ' . $delivery->type . "\n" . 'Тип оплаты: ' . $payment . "\n" . 'Платит: ' . $payer . "\n" . 'Примечания клиента: ' . $sender->comments;
            // print_r('<pre>');
            $noteText = "Прикреплённые документы и фотографии\n";
                if($req->image0 != 'nodata'){
                    $noteText = $noteText.$req->image0."\n";
                }
                if($req->image1 != 'nodata'){
                    $noteText = $noteText.$req->image1."\n";
                }
                if($req->image2 != 'nodata'){
                    $noteText = $noteText.$req->image2."\n";
                }
                if($req->image3 != 'nodata'){
                    $noteText = $noteText.$req->image3."\n";
                }
                if($req->image4 != 'nodata'){
                    $noteText = $noteText.$req->image4."\n";
                }
                if($req->image5 != 'nodata'){
                    $noteText = $noteText.$req->image5."\n";
                }
                if($req->image6 != 'nodata'){
                    $noteText = $noteText.$req->image6."\n";
                }
                if($req->image7 != 'nodata'){
                    $noteText = $noteText.$req->image7."\n";
                }
                if($req->image8 != 'nodata'){
                    $noteText = $noteText.$req->image8."\n";
                }
                if($req->image9 != 'nodata'){
                    $noteText = $noteText.$req->image9."\n";
                }                
            
            $job = (new SendOrder($leads, $sender_array[0]->id, $noteText, $id))
                ->onQueue('orders');
            dispatch($job);

            print_r($id);
        }
    }

    public function getInfoForQR($id)
    {
        $info = DB::table('orderTemplates')
            ->where('id', $id)
            ->select('waybill', 'places')
            ->get();
        print_r(json_encode($info));
    }

    public function addOrderToTemplates($data, $userid)
    {
        $id = DB::table('orderTemplates')
            ->max('id') + 1;

        if (!is_null($data->bin)) {
            DB::table('orderTemplates')
                ->insert([
                    'userId'                 => $userid,
                    'waybill'                => strtoupper($data->senderCountry) . $id . strtoupper($data->recipientCountry),
                    'senderCountry'          => $data->senderCountry,
                    'senderCity'             => $data->senderCity,
                    'senderStreet'           => $data->senderStreet,
                    'senderBuilding'         => $data->senderBuilding,
                    'senderApartments'       => $data->senderApartments,
                    'senderContactPerson'    => $data->senderContactPerson,
                    'senderContactPhone'     => $data->senderContactPhone,
                    'bin'                    => $data->bin,
                    'places'                 => str_replace(',', '.', $data->placesAmount),
                    'weight'                 => str_replace(',', '.', $data->weight),
                    'volume'                 => str_replace(',', '.', $data->volume),
                    'paymentType'            => $data->payment,
                    'deliveryType'           => $data->deliveryType,
                    'special'                => $data->special,
                    'recipient'              => $data->recipient,
                    'recipientCountry'       => $data->recipientCountry,
                    'recipientCity'          => $data->recipientCity,
                    'recipientStreet'        => $data->recipientStreet,
                    'recipientBuilding'      => $data->recipientBuilding,
                    'recipientApartments'    => $data->recipientApartments,
                    'recipientContactPerson' => $data->recipientContactPerson,
                    'recipientContactPhone'  => $data->recipientContactPhone,
                ]);
        } else {
            DB::table('orderTemplates')
                ->insert([
                    'userId'                 => $userid,
                    'waybill'                => strtoupper($data->senderCountry) . $id . strtoupper($data->recipientCountry),
                    'senderCountry'          => $data->senderCountry,
                    'senderCity'             => $data->senderCity,
                    'senderStreet'           => $data->senderStreet,
                    'senderBuilding'         => $data->senderBuilding,
                    'senderApartments'       => $data->senderApartments,
                    'senderContactPerson'    => $data->senderContactPerson,
                    'senderContactPhone'     => $data->senderContactPhone,
                    'places'                 => str_replace(',', '.', $data->placesAmount),
                    'weight'                 => str_replace(',', '.', $data->weight),
                    'volume'                 => str_replace(',', '.', $data->volume),
                    'paymentType'            => $data->payment,
                    'deliveryType'           => $data->deliveryType,
                    'special'                => $data->special,
                    'recipient'              => $data->recipient,
                    'recipientCountry'       => $data->recipientCountry,
                    'recipientCity'          => $data->recipientCity,
                    'recipientStreet'        => $data->recipientStreet,
                    'recipientBuilding'      => $data->recipientBuilding,
                    'recipientApartments'    => $data->recipientApartments,
                    'recipientContactPerson' => $data->recipientContactPerson,
                    'recipientContactPhone'  => $data->recipientContactPhone,
                ]);
        }
    }

    public function updateUserProfile()
    {
        $headers = apache_request_headers();
        if (!isset($headers['token'])) {
            print_r("unauthorized");
        } else {
            $token = $headers['token'];
            $req   = file_get_contents('php://input');
            $req   = json_decode($req);
            $data  = DB::table('cabinet_users')
                ->join('tokens', 'cabinet_users.id', '=', 'tokens.id')
                ->where('tokens.token', $token)
                ->update((array) $req);
            // print_r();
        }
    }

    public function getOrderById($waybill)
    {
        $headers = apache_request_headers();
        if (!isset($headers['token'])) {
            print_r("unauthorized");
        } else {
            $token  = $headers['token'];
            $client=DB::table('tokens')
                ->join('contragents', 'contragents.id', '=', 'tokens.id')
                ->where('token', $token)
                ->select('contragents.id')
                ->get();
            if($client[0]->id=='100440007349'){
                $orders = DB::table('orders_new')
                    ->join('leads_new', 'orders_new.lead_id', '=', 'leads_new.lead_id')
                    ->join('orders_statistics', 'orders_new.waybill', '=', 'orders_statistics.waybill')
                    ->join('directions', 'orders_new.direction', '=', 'directions.code')
                    ->where('orders_new.waybill', $waybill)
                    ->where('leads_new.sender_cabinet', 'WellDis LLP')
                    ->whereNotNull('orders_new.waybill')
                    ->select(
                        'orders_new.order_id', 
                        'application_date', 
                        'leads_new.sender_cabinet as sender',  
                        'orders_new.cabinet_recipient as recipient', 
                        'orders_new.waybill', 
                        'directions.direction', 
                        'delivery_type_big', 
                        'orders_new.shipped', 
                        'orders_new.shipping', 
                        'orders_new.in_region', 
                        'orders_new.on_the_way', 
                        'orders_new.ready_to_send', 
                        'leads_new.delivered_to_warehouse', 
                        'leads_new.taken_by_driver', 
                        'leads_new.transferred_to_driver', 
                        'accepted_by', 
                        'paid_weight', 
                        'leads_new.accepted_by_dispatcher',
                        'orders_statistics.accepted_by_dispatcher AS dispatcher_time',
                        'orders_statistics.taken_by_driver AS driver_time',
                        'orders_statistics.delivered_to_warehouse AS warehouse_time',
                        'orders_statistics.ready_to_send AS ready_time',
                        'orders_statistics.on_the_way AS way_time',
                        'orders_new.agent_receive_date AS region_time',
                        'orders_statistics.shipping AS shipping_time',
                        'orders_statistics.shipped AS shipped_time'
                    )
                    ->get();
            }
            else{
                $orders = DB::table('orders_new')
                    ->join('leads_new', 'orders_new.lead_id', '=', 'leads_new.lead_id')
                    ->join('contragents', 'sender_company_name', '=', 'contragents.full_name')
                    ->join('orders_statistics', 'orders_new.waybill', '=', 'orders_statistics.waybill')
                    ->join('directions', 'orders_new.direction', '=', 'directions.code')
                    ->join('tokens', function ($join) {
                        $join->on('contragents.id', '=', 'tokens.id');
                        // ->orOn('sender_person_iin',  '=', 'tokens.id');
                    })
                    ->where('orders_new.waybill', $waybill)
                    ->where('token', $token)
                    ->whereNotNull('orders_new.waybill')
                    ->select(
                        'orders_new.order_id', 
                        'application_date', 
                        'contragents.name as sender', 
                        'orders_new.cabinet_recipient as recipient', 
                        'orders_new.waybill', 
                        'directions.direction', 
                        'delivery_type_big', 
                        'orders_new.shipped', 
                        'orders_new.shipping', 
                        'orders_new.in_region', 
                        'orders_new.on_the_way', 
                        'orders_new.ready_to_send', 
                        'leads_new.delivered_to_warehouse', 
                        'leads_new.taken_by_driver', 
                        'leads_new.transferred_to_driver', 
                        'accepted_by', 
                        'paid_weight', 
                        'leads_new.accepted_by_dispatcher',
                        'orders_statistics.accepted_by_dispatcher AS dispatcher_time',
                        'orders_statistics.taken_by_driver AS driver_time',
                        'orders_statistics.delivered_to_warehouse AS warehouse_time',
                        'orders_statistics.ready_to_send AS ready_time',
                        'orders_statistics.on_the_way AS way_time',
                        'orders_statistics.in_region AS region_time',
                        'orders_statistics.shipping AS shipping_time',
                        'orders_statistics.shipped AS shipped_time'
                    )
                    ->get();
            }
            
            for ($i = 0; $i < count($orders); $i++) {
                if ($orders[$i]->shipped == 1) {
                    $orders[$i]->currentStatus = "Доставлен";
                } elseif ($orders[$i]->shipping == 1) {
                    $orders[$i]->currentStatus = "На доставке";
                } elseif ($orders[$i]->in_region == 1) {
                    $orders[$i]->currentStatus = "Прибыл в регион";
                } elseif ($orders[$i]->on_the_way == 1) {
                    $orders[$i]->currentStatus = "В пути";
                } elseif ($orders[$i]->ready_to_send == 1) {
                    $orders[$i]->currentStatus = "Готов к отправке";
                } elseif ($orders[$i]->delivered_to_warehouse == 1) {
                    $orders[$i]->currentStatus = "Прибыл на склад";
                } elseif ($orders[$i]->taken_by_driver == 1) {
                    $orders[$i]->currentStatus = "Забран курьером";
                } elseif ($orders[$i]->transferred_to_driver == 1) {
                    $orders[$i]->currentStatus = "Передан курьеру";
                } elseif ($orders[$i]->accepted_by_dispatcher == 1) {
                    $orders[$i]->currentStatus = "Принято диспетчером";
                }
            }
            header('Content-Type: application/json');
            $json = json_encode($orders, JSON_PRETTY_PRINT);
            print_r($json);
        }
    }

    public function getAllOrders()
    {
        // $headers = apache_request_headers();
        // if(!isset($headers['token'])){
        //     print_r("unauthorized");
        // }
        if ($_GET['token'] == '' || is_null($_GET['token'])) {
            print_r("unauthorized");
        } else {
            $token  = $_GET['token'];
            $client=DB::table('tokens')
                ->join('contragents', 'contragents.id', '=', 'tokens.id')
                ->where('token', $token)
                ->select('contragents.id')
                ->get();
            if($client[0]->id=='100440007349'){
                $orders = DB::table('orders_new')
                    ->join('leads_new', 'orders_new.lead_id', '=', 'leads_new.lead_id')
                    ->join('contragents', 'sender_company_name', '=', 'contragents.full_name')
                    ->join('directions', 'orders_new.direction', '=', 'directions.code')
                    ->whereNotNull('waybill')
                    ->where('waybill', '!=', '')
                    ->where('leads_new.sender_cabinet', 'WellDis LLP')
                    ->select('application_date', 'leads_new.order_number', 'waybill', 'directions.direction', 'delivery_type_big', 'shipped', 'shipping', 'in_region', 'on_the_way', 'ready_to_send', 'delivered_to_warehouse', 'taken_by_driver', 'transferred_to_driver', 'paid_weight', 'places', 'accepted_by_dispatcher', 'order_price')
                    ->get();
            }
            else{
                $orders = DB::table('orders_new')
                    ->join('leads_new', 'orders_new.lead_id', '=', 'leads_new.lead_id')
                    ->join('contragents', 'sender_company_name', '=', 'contragents.full_name')
                    ->join('directions', 'orders_new.direction', '=', 'directions.code')
                    ->join('tokens', function ($join) {
                        $join->on('contragents.id', '=', 'tokens.id');
                        // ->orOn('sender_person_iin',  '=', 'tokens.id');
                    })
                    ->where('token', $token)
                    ->whereNotNull('waybill')
                    ->where('waybill', '!=', '')
                    ->select('application_date', 'leads_new.order_number', 'waybill', 'directions.direction', 'delivery_type_big', 'shipped', 'shipping', 'in_region', 'on_the_way', 'ready_to_send', 'delivered_to_warehouse', 'taken_by_driver', 'transferred_to_driver', 'volume_weight', 'weight', 'paid_weight', 'places', 'accepted_by_dispatcher', 'order_price')
                    ->get();
            }           

            for ($i = 0; $i < count($orders); $i++) {
                $time = new DateTime;
                $time->createFromFormat('Y-m-d H:i:s', $orders[$i]->application_date);
                $orders[$i]->order_creation_time = $time->getTimestamp();

                if ($orders[$i]->shipped == 1) {
                    $orders[$i]->currentStatus = "Доставлен";
                } elseif ($orders[$i]->shipping == 1) {
                    $orders[$i]->currentStatus = "На доставке";
                } elseif ($orders[$i]->in_region == 1) {
                    $orders[$i]->currentStatus = "Прибыл в регион";
                } elseif ($orders[$i]->on_the_way == 1) {
                    $orders[$i]->currentStatus = "В пути";
                } elseif ($orders[$i]->ready_to_send == 1) {
                    $orders[$i]->currentStatus = "Готов к отправке";
                } elseif ($orders[$i]->delivered_to_warehouse == 1) {
                    $orders[$i]->currentStatus = "Прибыл на склад";
                } elseif ($orders[$i]->taken_by_driver == 1) {
                    $orders[$i]->currentStatus = "Забран курьером";
                } elseif ($orders[$i]->transferred_to_driver == 1) {
                    $orders[$i]->currentStatus = "Передан курьеру";
                } elseif ($orders[$i]->accepted_by_dispatcher == 1) {
                    $orders[$i]->currentStatus = "Принято диспетчером";
                }
            }
            header('Content-Type: application/json');
            $json = json_encode($orders, JSON_PRETTY_PRINT);
            print_r($json);
        }
    }

    public function getOrderTemplateList()
    {
        $headers = apache_request_headers();
        if (!isset($headers['token'])) {
            print_r("unauthorized");
        } else {
            $token     = $headers['token'];
            $templates = DB::table('orderTemplates')
                ->join('tokens', 'orderTemplates.userid', '=', 'tokens.id')
                ->where('token', $token)
                ->whereNotNull('template_name')
                ->select('template_name', 'orderTemplates.id')
                ->get();
            return $templates;
        }
    }

    public function getOrderTemplatesShort()
    {
        $headers = apache_request_headers();
        if (!isset($headers['token'])) {
            print_r("unauthorized");
        } else {
            $token = $headers['token'];
            // print_r($token);
            $templates = DB::table('orderTemplates')
                ->join('tokens', 'orderTemplates.userid', '=', 'tokens.id')
                ->orderBy('orderTemplates.id', 'desc')
                ->where('token', $token)
                ->whereNotNull('template_name')
                ->select('orderTemplates.id', 'template_name', 'recipient', 'senderCity', 'senderStreet', 'recipientStreet', 'recipientCity')
                ->get();
            return $templates;
        }
    }

    public function getOrderTemplate($id)
    {
        $headers = apache_request_headers();
        if (!isset($headers['token'])) {
            print_r("unauthorized");
        } else {
            $token     = $headers['token'];
            $templates = DB::table('orderTemplates')
                ->join('tokens', 'orderTemplates.userid', '=', 'tokens.id')
                ->where('token', $token)
                ->where('orderTemplates.id', $id)
                ->get();
            return $templates;
        }
    }

    public function deleteOrderTemplate($id)
    {
        $headers = apache_request_headers();
        if (!isset($headers['token'])) {
            print_r("unauthorized");
        } else {
            $token  = $headers['token'];
            $userid = DB::table('tokens')
                ->where('token', $token)
                ->select('id')
                ->first();
            DB::table('orderTemplates')
                ->join('tokens', 'orderTemplates.userid', '=', 'tokens.id')
                ->where('token', $token)
                ->where('orderTemplates.id', $id)
                ->update(['template_name' => null]);
            DB::table('notifications')
                ->insert([
                    'userid' => $userid->id,
                    'text'   => 'Шаблон ' . $_GET['name'] . ' был удалён',
                ]);
        }
    }

    public function addOrderTemplate($data, $userid)
    {
        $id = DB::table('orderTemplates')
            ->max('id') + 1;
        if (!is_null($data->bin)) {
            DB::table('orderTemplates')
                ->insert([
                    'userId'                 => $userid,
                    'template_name'          => $data->orderTemplateName,
                    'waybill'                => strtoupper($data->senderCountry) . $id . strtoupper($data->recipientCountry),
                    'senderCountry'          => $data->senderCountry,
                    'senderCity'             => $data->senderCity,
                    'senderStreet'           => $data->senderStreet,
                    'senderBuilding'         => $data->senderBuilding,
                    'senderApartments'       => $data->senderApartments,
                    'senderContactPerson'    => $data->senderContactPerson,
                    'senderContactPhone'     => $data->senderContactPhone,
                    'bin'                    => $data->bin,
                    'places'                 => str_replace(',', '.', $data->placesAmount),
                    'weight'                 => str_replace(',', '.', $data->weight),
                    'volume'                 => str_replace(',', '.', $data->volume),
                    'paymentType'            => $data->payment,
                    'deliveryType'           => $data->deliveryType,
                    'special'                => $data->special,
                    'recipient'              => $data->recipient,
                    'recipientCountry'       => $data->recipientCountry,
                    'recipientCity'          => $data->recipientCity,
                    'recipientStreet'        => $data->recipientStreet,
                    'recipientBuilding'      => $data->recipientBuilding,
                    'recipientApartments'    => $data->recipientApartments,
                    'recipientContactPerson' => $data->recipientContactPerson,
                    'recipientContactPhone'  => $data->recipientContactPhone,
                ]);
        } else {
            DB::table('orderTemplates')
                ->insert([
                    'userId'                 => $userid,
                    'template_name'          => $data->orderTemplateName,
                    'waybill'                => strtoupper($data->senderCountry) . $id . strtoupper($data->recipientCountry),
                    'senderCountry'          => $data->senderCountry,
                    'senderCity'             => $data->senderCity,
                    'senderStreet'           => $data->senderStreet,
                    'senderBuilding'         => $data->senderBuilding,
                    'senderApartments'       => $data->senderApartments,
                    'senderContactPerson'    => $data->senderContactPerson,
                    'senderContactPhone'     => $data->senderContactPhone,
                    'places'                 => str_replace(',', '.', $data->placesAmount),
                    'weight'                 => str_replace(',', '.', $data->weight),
                    'volume'                 => str_replace(',', '.', $data->volume),
                    'paymentType'            => $data->payment,
                    'deliveryType'           => $data->deliveryType,
                    'special'                => $data->special,
                    'recipient'              => $data->recipient,
                    'recipientCountry'       => $data->recipientCountry,
                    'recipientCity'          => $data->recipientCity,
                    'recipientStreet'        => $data->recipientStreet,
                    'recipientBuilding'      => $data->recipientBuilding,
                    'recipientApartments'    => $data->recipientApartments,
                    'recipientContactPerson' => $data->recipientContactPerson,
                    'recipientContactPhone'  => $data->recipientContactPhone,
                ]);
        }
    }

    public function updateOrderTemplate($id)
    {
        $data     = file_get_contents('php://input');
        $data     = json_decode($data);
        DB::table('orderTemplates')
        ->where('id', $id)
        ->update([
            'waybill'                => strtoupper($data->senderCountry) . $id . strtoupper($data->recipientCountry),
            'senderCountry'          => $data->senderCountry,
            'senderCity'             => $data->senderCity,
            'senderStreet'           => $data->senderStreet,
            'senderBuilding'         => $data->senderBuilding,
            'senderApartments'       => $data->senderApartments,
            'senderContactPerson'    => $data->senderContactPerson,
            'senderContactPhone'     => $data->senderContactPhone,
            'bin'                    => $data->bin,
            'places'                 => str_replace(',', '.', $data->placesAmount),
            'weight'                 => str_replace(',', '.', $data->weight),
            'volume'                 => str_replace(',', '.', $data->volume),
            'paymentType'            => $data->payment,
            'deliveryType'           => $data->deliveryType,
            'special'                => $data->special,
            'recipient'              => $data->recipient,
            'recipientCountry'       => $data->recipientCountry,
            'recipientCity'          => $data->recipientCity,
            'recipientStreet'        => $data->recipientStreet,
            'recipientBuilding'      => $data->recipientBuilding,
            'recipientApartments'    => $data->recipientApartments,
            'recipientContactPerson' => $data->recipientContactPerson,
            'recipientContactPhone'  => $data->recipientContactPhone,
        ]);
    }

    public function getSenderTemplateList()
    {
        $headers = apache_request_headers();
        if (!isset($headers['token'])) {
            print_r("unauthorized");
        } else {
            $token     = $headers['token'];
            $templates = DB::table('contactTemplates')
                ->join('tokens', 'contactTemplates.userid', '=', 'tokens.id')
                ->select('template_name', 'contactTemplates.id')
                ->where('token', $token)
                ->whereNull('name')
                ->whereNull('contact_person')
                ->get();
            return $templates;
        }
    }

    public function getSenderTemplatesShort()
    {
        $headers = apache_request_headers();
        if (!isset($headers['token'])) {
            print_r("unauthorized");
        } else {
            $token     = $headers['token'];
            $templates = DB::table('contactTemplates')
                ->join('tokens', 'contactTemplates.userid', '=', 'tokens.id')
                ->where('token', $token)
                ->whereNull('name')
                ->select('contactTemplates.id', 'contactTemplates.phone', 'contactTemplates.template_name', 'contactTemplates.city')
                ->get();
            return $templates;
        }
    }

    public function getSenderTemplate($id)
    {
        $headers = apache_request_headers();
        if (!isset($headers['token'])) {
            print_r("unauthorized");
        } else {
            $token     = $headers['token'];
            $templates = DB::table('contactTemplates')
                ->join('tokens', 'contactTemplates.userid', '=', 'tokens.id')
                ->where('token', $token)
                ->where('contactTemplates.id', $id)
                ->whereNull('name')
                ->get();
            return $templates;
        }
    }

    public function addSenderTemplate($data, $userid)
    {
        DB::table('contactTemplates')
            ->insert([
                'userId'                => $userid,
                'template_name'         => $data->senderTemplateName,
                'country'               => $data->senderCountry,
                'sender_contact_person' => $data->senderContactPerson,
                'city'                  => $data->senderCity,
                'street'                => $data->senderStreet,
                'building'              => $data->senderBuilding,
                'apartments'            => $data->senderApartments,
                'phone'                 => $data->senderContactPhone,
            ]);
    }

    public function updateSenderTemplate($id)
    {
        $data     = file_get_contents('php://input');
        $data     = json_decode($data);
        $headers = apache_request_headers();
        if (!isset($headers['token'])) {
            print_r("unauthorized");
        } else {
            $data = file_get_contents('php://input');
            $data = json_decode($data);
            DB::table('contactTemplates')
                ->join('tokens', 'contactTemplates.userid', '=', 'tokens.id')
                ->where('token', $headers['token'])
                ->where('contactTemplates.id', $id)
                ->update([
                    'template_name'  => $data->template_name,
                    'country'        => $data->senderCountry,
                    'city'           => $data->senderCity,
                    'street'         => $data->senderStreet,
                    'building'       => $data->senderBuilding,
                    'apartments'     => $data->senderApartments,
                    'phone'          => $data->senderContactPhone,
                    'contact_person' => $data->senderContactPerson,
                ]);
        }
    }

    public function getRecipientTemplateList()
    {
        $headers = apache_request_headers();
        if (!isset($headers['token'])) {
            print_r("unauthorized");
        } else {
            $token     = $headers['token'];
            $templates = DB::table('contactTemplates')
                ->join('tokens', 'contactTemplates.userid', '=', 'tokens.id')
                ->select('template_name', 'contactTemplates.id')
                ->where('token', $token)
                ->whereNotNull('name')
                ->whereNotNull('contact_person')
                ->get();
            return $templates;
        }
    }

    public function getRecipientTemplatesShort()
    {
        $headers = apache_request_headers();
        if (!isset($headers['token'])) {
            print_r("unauthorized");
        } else {
            $token     = $headers['token'];
            $templates = DB::table('contactTemplates')
                ->join('tokens', 'contactTemplates.userid', '=', 'tokens.id')
                ->where('token', $token)
                ->whereNotNull('name')
                ->select('contactTemplates.id', 'contactTemplates.phone', 'contactTemplates.template_name', 'contactTemplates.city')
                ->get();
            return $templates;
        }
    }

    public function getRecipientTemplate($id)
    {
        $headers = apache_request_headers();
        if (!isset($headers['token'])) {
            print_r("unauthorized");
        } else {
            $token     = $headers['token'];
            $templates = DB::table('contactTemplates')
                ->join('tokens', 'contactTemplates.userid', '=', 'tokens.id')
                ->where('token', $token)
                ->where('contactTemplates.id', $id)
                ->whereNotNull('name')
                ->get();
            return $templates;
        }
    }

    public function addRecipientTemplate($data, $userid)
    {
        DB::table('contactTemplates')
            ->insert([
                'userId'         => $userid,
                'template_name'  => $data->recipientTemplateName,
                'name'           => $data->recipient,
                'recipient_bin'  => $data->bin,
                'country'        => $data->recipientCountry,
                'city'           => $data->recipientCity,
                'street'         => $data->recipientStreet,
                'building'       => $data->recipientBuilding,
                'apartments'     => $data->recipientApartments,
                'phone'          => $data->recipientContactPhone,
                'contact_person' => $data->recipientContactPerson,
            ]);
    }

    public function updateRecipientTemplate($id)
    {
        $data     = file_get_contents('php://input');
        $data     = json_decode($data);
        $headers = apache_request_headers();
        if (!isset($headers['token'])) {
            print_r("unauthorized");
        } else {
            $data = file_get_contents('php://input');
            $data = json_decode($data);
            DB::table('contactTemplates')
                ->join('tokens', 'contactTemplates.userid', '=', 'tokens.id')
                ->where('token', $headers['token'])
                ->where('contactTemplates.id', $id)
                ->update([
                    'template_name'  => $data->template_name,
                    'name'           => $data->recipient,
                    'recipient_bin'  => $data->bin,
                    'country'        => $data->recipientCountry,
                    'city'           => $data->recipientCity,
                    'street'         => $data->recipientStreet,
                    'building'       => $data->recipientBuilding,
                    'apartments'     => $data->recipientApartments,
                    'phone'          => $data->recipientContactPhone,
                    'contact_person' => $data->recipientContactPerson,
                ]);
        }
    }

    public function deleteContactTemplate($id)
    {
        $headers = apache_request_headers();
        if (!isset($headers['token'])) {
            print_r("unauthorized");
        } else {
            $token  = $headers['token'];
            $userid = DB::table('tokens')
                ->where('token', $token)
                ->select('id')
                ->first();
            DB::table('contactTemplates')
                ->join('tokens', 'contactTemplates.userid', '=', 'tokens.id')
                ->where('token', $token)
                ->where('contactTemplates.id', $id)
                ->delete();
            DB::table('notifications')
                ->insert([
                    'userid' => $userid->id,
                    'text'   => 'Шаблон ' . $_GET['name'] . ' был удалён',
                ]);
        }
    }

    public function getAllNotifications()
    {
        $headers = apache_request_headers();
        if (!isset($headers['token'])) {
            print_r("unauthorized");
        } else {
            $token         = $headers['token'];
            $notifications = DB::table('notifications')
                ->join('tokens', 'notifications.userId', '=', 'tokens.id')
                ->where('token', $token)
                ->select('notifications.id', 'notifications.text', 'notifications.readByUser', 'notifications.date')
                ->get();
            header('Content-Type: application/json');
            $json = json_encode($notifications, JSON_PRETTY_PRINT);
            print_r($json);
        }
    }

    public function getAllNotificationsAmount()
    {
        $headers = apache_request_headers();
        if (!isset($headers['token'])) {
            print_r("unauthorized");
        } else {
            $token         = $headers['token'];
            $notifications = DB::table('notifications')
                ->join('tokens', 'notifications.userId', '=', 'tokens.id')
                ->where('token', $token)
                ->where('readByUser', 0)
                ->count();
            header('Content-Type: application/json');
            $json = json_encode($notifications, JSON_PRETTY_PRINT);
            print_r($json);
        }
    }

    public function notificationReadByUser($id)
    {
        $headers = apache_request_headers();
        if (!isset($headers['token'])) {
            print_r("unauthorized");
        } else {
            $token = $headers['token'];
            if ($id == 0) {
                DB::table('notifications')
                    ->join('tokens', 'notifications.userId', '=', 'tokens.id')
                    ->where('token', $token)
                    ->update(['notifications.readByUser' => true]);
            } else {
                DB::table('notifications')
                    ->join('tokens', 'notifications.userId', '=', 'tokens.id')
                    ->where('token', $token)
                    ->where('notifications.id', $id)
                    ->update(['notifications.readByUser' => true]);
            }
        }
    }

    public function siteSearch($waybill)
    {
        $link='http://188.0.151.36/spark_copy/hs/integration/invoicestatus?номернакладной='.$waybill;
        /* Нам необходимо инициировать запрос к серверу. Воспользуемся библиотекой cURL (поставляется в составе PHP). Вы также
        можете
        использовать и кроссплатформенную программу cURL, если вы не программируете на PHP. */
        $curl=curl_init(); #Сохраняем дескриптор сеанса cURL
        #Устанавливаем необходимые опции для сеанса cURL
        curl_setopt($curl,CURLOPT_URL,$link);
        $out=curl_exec($curl); #Инициируем запрос к API и сохраняем ответ в переменную
        $code=curl_getinfo($curl,CURLINFO_HTTP_CODE); #Получим HTTP-код ответа сервера
        curl_close($curl);
        $waybills = json_decode($out);
        return $waybills;
        header('Content-Type: application/json');
        $json = json_encode($orders, JSON_PRETTY_PRINT);
        print_r($json);
    }

    public function landing()
    {
        $req     = file_get_contents('php://input');
        $req     = json_decode($req);
        $leads   = null;
        $tag     = 'Переезд';

        if($req->type == "Перезвонить по поводу закупки в РФ"){
            $tag='ВЭД';
        }
        if($req->type == "Консолидация"){
            $tag='Консолидация';
        }

        $leads['add'] = array(
            array(
                'name'                => 'Запрос перезвонить от #' . time(),
                'tags'                => $tag,
                'company_id'          => '31590569',
                'responsible_user_id' => \Config::get('managers.manager_names.' . 'Дюсенова Зарина'),
            ),
        );

        $job = (new Callback($req->type, $req->name, $req->email, $req->phone, $req->message, $leads))->onQueue('couriers');
        dispatch($job);
    }

    public function massOrder()
    {
        $req     = file_get_contents('php://input');
        $req     = json_decode($req);
        $headers = apache_request_headers();
        if (!isset($headers['token'])) {
            print_r("unauthorized");
        } else {
            $token        = $headers['token'];
            $sender_array = DB::table('tokens')
                ->join('contragents', 'contragents.id', '=', 'tokens.id')
                ->where('token', $token)
                ->get();

            $leads['add']=[];
            $id ="";
            $noteText = "";
            $order = new \stdClass();
            
            $order->senderAddress = $req[1][2].", ".$req[2][2].", ".$req[3][2].", ".$req[4][2];
            $order->country = $req[1][2];
            $order->region = $req[2][2];
            $order->city = $req[3][2];
            $order->street = $req[4][2];
            $order->building = $req[5][2];
            $order->office = $req[6][2];
            $order->senderContactPerson = $req[7][2];
            $order->senderContactPhone = $req[8][2];
            $order->date = Carbon::parse($req[9][2]);
            $order->time=$req[10][2];
            print_r("<pre>");
            // print_r($req);
            for ($i=13; $i < count($req); $i++) {
                $order->waybills[$i-13]["recipient_code"] = $req[$i][0];          
                $order->waybills[$i-13]["recipientAddress"] = $req[$i][1].", ".$req[$i][2].", ".$req[$i][3].", ".$req[$i][4].", ".$req[$i][5];
                $order->waybills[$i-13]["country"] = $req[$i][1];
                $order->waybills[$i-13]["city"] = $req[$i][2];
                $order->waybills[$i-13]["region"]  = $req[$i][3];
                $order->waybills[$i-13]["street"] = $req[$i][4];
                $order->waybills[$i-13]["building"] = $req[$i][5];
                $order->waybills[$i-13]["office"] = $req[$i][6];
                $order->waybills[$i-13]["recipientContactPerson"] = $req[$i][7];
                $order->waybills[$i-13]["recipientContactPhone"] = $req[$i][8];
                $order->waybills[$i-13]["deliveryType"] = $req[$i][9];
                $order->waybills[$i-13]["placesAmount"] = $req[$i][10];
                $order->waybills[$i-13]["weight"] = $req[$i][11];
                $order->waybills[$i-13]["volume"] = $req[$i][12];
                $order->waybills[$i-13]["payment"] = $req[$i][13];
                $order->waybills[$i-13]["payer"] = $req[$i][14];
                $order->waybills[$i-13]["overPayment"] = $req[$i][15];
                $order->waybills[$i-13]["sum"] = $req[$i][16];
                $order->waybills[$i-13]["comment"] = $req[$i][17];
                print_r($order->waybills[$i-13]);
            }

            $id=DB::table('leads_new')
                ->where('lead_id', '<', '11621501')
                ->max('lead_id');

            DB::table("leads_new")
                ->insert([
                    "lead_id"               => $id+1,
                    "address_from"          => $order->senderAddress,
                    "country"               => $order->country,
                    "region"                => $order->region,    
                    "city"                  => $order->city,
                    "street"                => $order->street,
                    "building"              => $order->building,
                    "office"                => $order->office,
                    "sender_person"         => $order->senderContactPerson,           
                    "sender_phone"          => $order->senderContactPhone,            
                    "date_from"             => $order->date,
                    "time_from"             => $order->time,
                    "sender_company_name"   => $sender_array[0]->full_name,
                    "sender_company_bin"    => $sender_array[0]->id,
                    "client_full_name"      => $sender_array[0]->full_name,
                    "responsible"           => $sender_array[0]->manager,
                    "delivery_type"         => 'B2C',
                    "author"                => 'Интернет-заказ'
                ]);
            for ($i=0; $i < count($order->waybills); $i++) { 
                DB::table("orders_new")
                    ->insert([
                        "order_id"              =>  $i,
                        "lead_id"               =>  $id+1,
                        "direction"             =>  'Интернет - Интернет',
                        "code"                  =>  $order->waybills[$i]["recipient_code"],
                        "recipient_address"     =>  $order->waybills[$i]["recipientAddress"],
                        "city"                  =>  $order->waybills[$i]["city"],
                        "region"                =>  $order->waybills[$i]["region"],
                        "street"                =>  $order->waybills[$i]["street"],
                        "building"              =>  $order->waybills[$i]["building"],
                        "office"                =>  $order->waybills[$i]["office"],
                        "recipient_contact_person"=>$order->waybills[$i]["recipientContactPerson"],
                        "recipient_phone"       =>  $order->waybills[$i]["recipientContactPhone"],
                        "delivery_type_big"     =>  $order->waybills[$i]["deliveryType"],
                        "places"                =>  $order->waybills[$i]["placesAmount"],
                        "weight"                =>  $order->waybills[$i]["weight"],
                        "volume_weight"         =>  $order->waybills[$i]["volume"],
                        "payment_type"          =>  $order->waybills[$i]["payment"],
                        "payer"                 =>  $order->waybills[$i]["payer"],
                        "overcharged_payment"   =>  $order->waybills[$i]["overPayment"],
                        "cargo_value"           =>  $order->waybills[$i]["sum"],
                        "comment"               =>  $order->waybills[$i]["comment"],
                    ]);
            }
            Mail::to('voichenko.j@spark-logistics.com')
                ->send(new newOrder());
            
            print_r($order);
            // print_r($sender_array[0]);

            // $job = (new SendMassOrder($leads, $order, $sender_array[0], $id))
                // ->onQueue('orders');
            // dispatch($job);
        }
    }

    public function ecar()
    {
        $req     = file_get_contents('php://input');
        $req     = json_decode($req);
        if($req->token != "B9668530AFC5D34B457DC82249A1A65C75980B3454B4B615B08DAA206CCDCB6F"){
            return response()->json([
                'error' => 'Invalid token'
            ]);
        }
        // // $headers = apache_request_headers();
        // // if (!isset($headers['token'])) {
        //     // print_r("unauthorized");
        // // } else {
        //     // $token        = $headers['token'];
        $sender_array = DB::table('contragents')
            // ->join('contragents', 'contragents.id', '=', 'tokens.id')
            ->where('code', "000001509")
            ->get();

        $leads['add']=[];
        $id ="";
        $noteText = "";
        $order = new \stdClass();
        
        $order->senderAddress = $req->country.', '.$req->region.' '.$req->city.', '.$req->street.', '.$req->building.', '.$req->office;
        $order->country = $req->country;
        $order->region = $req->region;
        $order->city = $req->city;
        $order->street = $req->street;
        $order->building = $req->building;
        $order->office = $req->office;
        $order->senderContactPerson = $req->contact_person;
        $order->senderContactPhone = $req->contact_phone;
        $order->date = Carbon::parse($req->date);
        $order->time=$req->time;
        

        for ($i=0; $i < count($req->waybills); $i++) {
            $order->waybills[$i]["recipient_code"] = $req->waybills[$i]->recipient_code;
            $order->waybills[$i]["recipientAddress"] = $req->waybills[$i]->country.', '.$req->waybills[$i]->region.' '.$req->waybills[$i]->city.', '.$req->waybills[$i]->street.', '.$req->waybills[$i]->building.', '.$req->waybills[$i]->office;
            $order->waybills[$i]["country"] = $req->waybills[$i]->country;
            $order->waybills[$i]["city"] = $req->waybills[$i]->city;
            $order->waybills[$i]["region"]  = $req->waybills[$i]->region;
            $order->waybills[$i]["street"] = $req->waybills[$i]->street;
            $order->waybills[$i]["building"] = $req->waybills[$i]->building;
            $order->waybills[$i]["office"] = $req->waybills[$i]->office;
            $order->waybills[$i]["recipientContactPerson"] = $req->waybills[$i]->recipient_contact_person;
            $order->waybills[$i]["recipientContactPhone"] = $req->waybills[$i]->recipient_contact_phone;
            $order->waybills[$i]["deliveryType"] = $req->waybills[$i]->delivery_type;
            $order->waybills[$i]["placesAmount"] = $req->waybills[$i]->places;
            $order->waybills[$i]["weight"] = $req->waybills[$i]->weight;
            $order->waybills[$i]["volume"] = $req->waybills[$i]->volume;
            $order->waybills[$i]["payment"] = $req->waybills[$i]->payment;
            $order->waybills[$i]["payer"] = $req->waybills[$i]->payer;
            $order->waybills[$i]["overPayment"] = $req->waybills[$i]->over_payment;
            $order->waybills[$i]["sum"] = $req->waybills[$i]->sum;
            $order->waybills[$i]["comment"] = $req->waybills[$i]->comment;
        }

        $leads['add'] = [
            [
                'name'                => 'Сделка #' . time(),
                'company_id'          => '36419955',
                'tags'                => 'B2C',
                'responsible_user_id' => '1542400',
            ],
        ];
// print_r($req);
        print_r($order);

        $job = (new SendMassOrder($leads, $order, $sender_array[0]))
            ->onQueue('orders');
        dispatch($job);
    }

    public function setIDS()
    {
        $array=DB::table('leads_1C')
            ->where('lead_id', 0)
            ->get();
        for ($i=0; $i < count($array); $i++) { 
            $id=DB::table('leads_1C')->max('lead_id');
            DB::table('leads_1C')
                ->where('order_id', $array[$i]->order_id)
                ->update(["lead_id"=>$id+1]);
        }
        $test=DB::table('leads_1C')
            ->where('lead_id', 0)
            ->get();
            
    }

    public function register()
    {
        $alphabet = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ1234567890';
        $pass = array(); //remember to declare $pass as an array
        $alphaLength = strlen($alphabet) - 1; //put the length -1 in cache
        for ($i = 0; $i < 8; $i++) {
            $n = rand(0, $alphaLength);
            $pass[] = $alphabet[$n];
        }
        $pass = implode($pass);
        $contragent = DB::table('contragents')
            ->where('code', '=', $_POST['code'])
            ->get();
        $companies['add']=array(
           array(
              'name' => $contragent[0]->name,
              'responsible_user_id' => 1542400,
              'created_by' => 1542400,
              'created_at' => time(),
              'custom_fields' => array(
                 
              )
           )
        );
        $user=array(
         'USER_LOGIN'=>'crm@spark-logistics.com', #Ваш логин (электронная почта)
         'USER_HASH'=>'349f37c5a3675ee42e489b0cde2e492bfcd0917d' #Хэш для доступа к API (смотрите в профиле пользователя)
        );
        $subdomain='sparkcrm'; #Наш аккаунт - поддомен
        #Формируем ссылку для запроса
        $link='https://'.$subdomain.'.amocrm.ru/private/api/auth.php?type=json';
        /* Нам необходимо инициировать запрос к серверу. Воспользуемся библиотекой cURL (поставляется в составе PHP). Вы также
        можете
        использовать и кроссплатформенную программу cURL, если вы не программируете на PHP. */
        $curl=curl_init(); #Сохраняем дескриптор сеанса cURL
        #Устанавливаем необходимые опции для сеанса cURL
        curl_setopt($curl,CURLOPT_RETURNTRANSFER,true);
        curl_setopt($curl,CURLOPT_USERAGENT,'amoCRM-API-client/1.0');
        curl_setopt($curl,CURLOPT_URL,$link);
        curl_setopt($curl,CURLOPT_CUSTOMREQUEST,'POST');
        curl_setopt($curl,CURLOPT_POSTFIELDS,json_encode($user));
        curl_setopt($curl,CURLOPT_HTTPHEADER,array('Content-Type: application/json'));
        curl_setopt($curl,CURLOPT_HEADER,false);
        curl_setopt($curl,CURLOPT_COOKIEFILE,dirname(__FILE__).'/cookie.txt'); #PHP>5.3.6 dirname(__FILE__) -> __DIR__
        curl_setopt($curl,CURLOPT_COOKIEJAR,dirname(__FILE__).'/cookie.txt'); #PHP>5.3.6 dirname(__FILE__) -> __DIR__
        curl_setopt($curl,CURLOPT_SSL_VERIFYPEER,0);
        curl_setopt($curl,CURLOPT_SSL_VERIFYHOST,0);
        $out=curl_exec($curl); #Инициируем запрос к API и сохраняем ответ в переменную
        $code=curl_getinfo($curl,CURLINFO_HTTP_CODE); #Получим HTTP-код ответа сервера
        curl_close($curl); #Завершаем сеанс cURL
        /* Теперь мы можем обработать ответ, полученный от сервера. Это пример. Вы можете обработать данные своим способом. */
        $code=(int)$code;
        $errors=array(
        301=>'Moved permanently',
        400=>'Bad request',
        401=>'Unauthorized',
        403=>'Forbidden',
        404=>'Not found',
        500=>'Internal server error',
        502=>'Bad gateway',
        503=>'Service unavailable'
        );
        try
        {
        #Если код ответа не равен 200 или 204 - возвращаем сообщение об ошибке
        if($code!=200 && $code!=204)
         throw new Exception(isset($errors[$code]) ? $errors[$code] : 'Undescribed error',$code);
        }
        catch(Exception $E)
        {
        die('Ошибка: '.$E->getMessage().PHP_EOL.'Код ошибки: '.$E->getCode());
        }
        /*
        Данные получаем в формате JSON, поэтому, для получения читаемых данных,
        нам придётся перевести ответ в формат, понятный PHP
        */
        $Response=json_decode($out,true);
        $Response=$Response['response'];

        sleep(1);
        /* Теперь подготовим данные, необходимые для запроса к серверу */
        $subdomain='sparkcrm'; #Наш аккаунт - поддомен
        #Формируем ссылку для запроса
        $link='https://'.$subdomain.'.amocrm.ru/api/v2/companies';
        /* Нам необходимо инициировать запрос к серверу. Воспользуемся библиотекой cURL (поставляется в составе PHP). Подробнее о
        работе с этой
        библиотекой Вы можете прочитать в мануале. */
        $curl=curl_init(); #Сохраняем дескриптор сеанса cURL
        #Устанавливаем необходимые опции для сеанса cURL
        curl_setopt($curl,CURLOPT_RETURNTRANSFER,true);
        curl_setopt($curl,CURLOPT_USERAGENT,'amoCRM-API-client/1.0');
        curl_setopt($curl,CURLOPT_URL,$link);
        curl_setopt($curl,CURLOPT_CUSTOMREQUEST,'POST');
        curl_setopt($curl,CURLOPT_POSTFIELDS,json_encode($companies));
        curl_setopt($curl,CURLOPT_HTTPHEADER,array('Content-Type: application/json'));
        curl_setopt($curl,CURLOPT_HEADER,false);
        curl_setopt($curl,CURLOPT_COOKIEFILE,dirname(__FILE__).'/cookie.txt'); #PHP>5.3.6 dirname(__FILE__) -> __DIR__
        curl_setopt($curl,CURLOPT_COOKIEJAR,dirname(__FILE__).'/cookie.txt'); #PHP>5.3.6 dirname(__FILE__) -> __DIR__
        curl_setopt($curl,CURLOPT_SSL_VERIFYPEER,0);
        curl_setopt($curl,CURLOPT_SSL_VERIFYHOST,0);
        $out=curl_exec($curl); #Инициируем запрос к API и сохраняем ответ в переменную
        $code=curl_getinfo($curl,CURLINFO_HTTP_CODE);
        /* Теперь мы можем обработать ответ, полученный от сервера. Это пример. Вы можете обработать данные своим способом. */
        $code=(int)$code;
        $errors=array(
          301=>'Moved permanently',
          400=>'Bad request',
          401=>'Unauthorized',
          403=>'Forbidden',
          404=>'Not found',
          500=>'Internal server error',
          502=>'Bad gateway',
          503=>'Service unavailable'
        );
        try
        {
          #Если код ответа не равен 200 или 204 - возвращаем сообщение об ошибке
         if($code!=200 && $code!=204){
            throw new Exception(isset($errors[$code]) ? $errors[$code] : 'Undescribed error',$code);
            }
            else {
                DB::table('contragents')
                    ->where('code', '=', $_POST['code'])
                    ->update([
                        'amo_id'    => json_decode($out)->_embedded->items[0]->id,
                        'password'  => hash('sha256', $pass)
                    ]);
                $contr = DB::table('contragents')
                    ->where('code', '=', $_POST['code'])
                    ->select('name','juridical','id','amo_id','work_phone','email','fact_address','jur_address','full_name','nds_number','nds_date','bank_account','contract','password','code','manager')
                    ->get();
                DB::table('cabinet_users')
                    ->where('code', '=', $_POST['code'])
                    ->insert(
                        (array) $contr->toArray()[0]
                    );               
                print_r($pass);
            }
        }
        catch(Exception $E)
        {
          print_r('Ошибка: '.$E->getMessage().PHP_EOL.'Код ошибки: '.$E->getCode());
        }
        /*
         Данные получаем в формате JSON, поэтому, для получения читаемых данных,
         нам придётся перевести ответ в формат, понятный PHP
         */
    }

    public function resetPassword()
    {
        $alphabet = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ1234567890';
        $pass = array(); //remember to declare $pass as an array
        $alphaLength = strlen($alphabet) - 1; //put the length -1 in cache
        for ($i = 0; $i < 8; $i++) {
            $n = rand(0, $alphaLength);
            $pass[] = $alphabet[$n];
        }
        $pass = implode($pass);
        DB::table('contragents')
            ->where('code', '=', $_POST['code'])
            ->update([                
                'password'  => hash('sha256', $pass)
            ]);
        DB::table('cabinet_users')
            ->where('code', '=', $_POST['code'])
            ->update([                
                'password'  => hash('sha256', $pass)
            ]);
        print_r($pass);
    }
}