<?php

namespace App\Http\Controllers;

use App\Jobs\tagContragents;
use App\Jobs\SendCourier;
use App\Jobs\SendLeadToStatus;
use App\Jobs\SendOrder;
use Carbon\Carbon;
use DateTime;
use Exception;
use Illuminate\Support\Facades\DB;

class reserveController extends Controller
{
    // public function amoAuth()
    // {
    //     #Массив с параметрами, которые нужно передать методом POST к API системы
    //     $user = [
    //         'USER_LOGIN' => 'crm@spark-logistics.com', #Ваш логин (электронная почта)
    //         'USER_HASH'  => '4d455c00920812341e5ccbaa412624fb', #Хэш для доступа к API (смотрите в профиле пользователя)
    //     ];
    //     $subdomain = 'sparkcrm'; #Наш аккаунт - поддомен
    //     #Формируем ссылку для запроса
    //     $link = 'https://' . $subdomain . '.amocrm.ru/private/api/auth.php?type=json';
    //     /* Нам необходимо инициировать запрос к серверу. Воспользуемся библиотекой cURL (поставляется в составе PHP). Вы также
    //     можете
    //     использовать и кроссплатформенную программу cURL, если вы не программируете на PHP. */
    //     $curl = curl_init(); #Сохраняем дескриптор сеанса cURL
    //     #Устанавливаем необходимые опции для сеанса cURL
    //     curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
    //     curl_setopt($curl, CURLOPT_USERAGENT, 'amoCRM-API-client/1.0');
    //     curl_setopt($curl, CURLOPT_URL, $link);
    //     curl_setopt($curl, CURLOPT_CUSTOMREQUEST, 'POST');
    //     curl_setopt($curl, CURLOPT_POSTFIELDS, json_encode($user));
    //     curl_setopt($curl, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
    //     curl_setopt($curl, CURLOPT_HEADER, false);
    //     curl_setopt($curl, CURLOPT_COOKIEFILE, dirname(__FILE__) . '/cookie.txt'); #PHP>5.3.6 dirname(__FILE__) -> __DIR__
    //     curl_setopt($curl, CURLOPT_COOKIEJAR, dirname(__FILE__) . '/cookie.txt'); #PHP>5.3.6 dirname(__FILE__) -> __DIR__
    //     curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, 0);
    //     curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 0);
    //     $out  = curl_exec($curl); #Инициируем запрос к API и сохраняем ответ в переменную
    //     $code = curl_getinfo($curl, CURLINFO_HTTP_CODE); #Получим HTTP-код ответа сервера
    //     curl_close($curl); #Завершаем сеанс cURL
    //     /* Теперь мы можем обработать ответ, полученный от сервера. Это пример. Вы можете обработать данные своим способом. */
    //     $code   = (int) $code;
    //     $errors = [
    //         301 => 'Moved permanently',
    //         400 => 'Bad request',
    //         401 => 'Unauthorized',
    //         403 => 'Forbidden',
    //         404 => 'Not found',
    //         500 => 'Internal server error',
    //         502 => 'Bad gateway',
    //         503 => 'Service unavailable',
    //     ];
    //     try
    //     {
    //         #Если код ответа не равен 200 или 204 - возвращаем сообщение об ошибке
    //         if ($code != 200 && $code != 204) {
    //             throw new Exception(isset($errors[$code]) ? $errors[$code] : 'Undescribed error', $code);
    //         }

    //     } catch (Exception $E) {
    //         die('Ошибка: ' . $E->getMessage() . PHP_EOL . 'Код ошибки: ' . $E->getCode());
    //     }
    //     /*
    //     Данные получаем в формате JSON, поэтому, для получения читаемых данных,
    //     нам придётся перевести ответ в формат, понятный PHP
    //      */
    //     $Response = json_decode($out, true);
    //     $Response = $Response['response'];
    //     if (isset($Response['auth'])) #Флаг авторизации доступен в свойстве "auth"
    //     {
    //         return 'Авторизация прошла успешно';
    //     }

    //     return 'Авторизация не удалась';
    // }

        // public function getOrdersTillDate($date)
    // {
    //     $headers = apache_request_headers();
    //     if (!isset($headers['token'])) {
    //         print_r("unauthorized");
    //     } else {
    //         $token  = $headers['token'];
    //         $orders = DB::table('orders_new')
    //             ->join('leads_new', 'orders_new.lead_id', '=', 'leads_new.lead_id')
    //             ->join('contragents', 'sender_company_name', '=', 'contragents.full_name')
    //             ->join('directions', 'orders_new.direction', '=', 'directions.code')
    //             ->join('tokens', function ($join) {
    //                 $join->on('contragents.id', '=', 'tokens.id');
    //                 // ->orOn('sender_person_iin',  '=', 'tokens.id');
    //             })
    //             ->where('token', $token)
    //             ->where('date_from', '<=', $date)
    //             ->whereNotNull('waybill')
    //             ->select('order_id', 'order_creation_time', 'leads_new.order_number', 'waybill', 'directions.direction', 'delivery_type', 'shipped', 'shipping', 'in_region', 'on_the_way', 'ready_to_send', 'delivered_to_warehouse', 'taken_by_driver', 'transferred_to_driver', 'accepted_by_dispatcher')
    //             ->get();
    //         for ($i = 0; $i < count($orders); $i++) {
    //             if ($orders[$i]->shipped == 1) {
    //                 $orders[$i]->currentStatus = "Доставлен";
    //             } elseif ($orders[$i]->shipping == 1) {
    //                 $orders[$i]->currentStatus = "На доставке";
    //             } elseif ($orders[$i]->in_region == 1) {
    //                 $orders[$i]->currentStatus = "Прибыл в регион";
    //             } elseif ($orders[$i]->on_the_way == 1) {
    //                 $orders[$i]->currentStatus = "В пути";
    //             } elseif ($orders[$i]->ready_to_send == 1) {
    //                 $orders[$i]->currentStatus = "Готов к отправке";
    //             } elseif ($orders[$i]->delivered_to_warehouse == 1) {
    //                 $orders[$i]->currentStatus = "Прибыл на склад";
    //             } elseif ($orders[$i]->taken_by_courier == 1) {
    //                 $orders[$i]->currentStatus = "Забран курьером";
    //             } elseif ($orders[$i]->transferred_to_courier == 1) {
    //                 $orders[$i]->currentStatus = "Передан курьеру";
    //             } elseif ($orders[$i]->accepted_by_dispatcher == 1) {
    //                 $orders[$i]->currentStatus = "Принято диспетчером";
    //             }
    //         }
    //         header('Content-Type: application/json');
    //         $json = json_encode($orders, JSON_PRETTY_PRINT);
    //         print_r($json);
    //     }
    // }

    // public function getOrdersWithinDate()
    // {
    //     $headers = apache_request_headers();
    //     if (!isset($headers['token'])) {
    //         print_r("unauthorized");
    //     } else {
    //         $token     = $headers['token'];
    //         $dateLast  = $_GET["dateLast"];
    //         $dateFirst = $_GET["dateFirst"];
    //         if ($dateLast != null && $dateFirst != null) {
    //             $orders = DB::table('orders_new')
    //                 ->join('leads_new', 'orders_new.lead_id', '=', 'leads_new.lead_id')
    //                 ->join('contragents', 'sender_company_name', '=', 'contragents.full_name')
    //                 ->join('directions', 'orders_new.direction', '=', 'directions.code')
    //                 ->join('tokens', function ($join) {
    //                     $join->on('contragents.id', '=', 'tokens.id');
    //                     // ->orOn('sender_person_iin',  '=', 'tokens.id');
    //                 })
    //                 ->where('token', $token)
    //                 ->where('date_to', '<=', $dateLast)
    //                 ->where('date_from', '>=', $dateFirst)
    //                 ->whereNotNull('waybill')
    //                 ->select('order_id', 'order_creation_time', 'leads_new.order_number', 'waybill', 'directions.direction', 'delivery_type', 'shipped', 'shipping', 'in_region', 'on_the_way', 'ready_to_send', 'delivered_to_warehouse', 'taken_by_driver', 'transferred_to_driver', 'accepted_by_dispatcher')
    //                 ->get();
    //             for ($i = 0; $i < count($orders); $i++) {
    //                 if ($orders[$i]->shipped == 1) {
    //                     $orders[$i]->currentStatus = "Доставлен";
    //                 } elseif ($orders[$i]->shipping == 1) {
    //                     $orders[$i]->currentStatus = "На доставке";
    //                 } elseif ($orders[$i]->in_region == 1) {
    //                     $orders[$i]->currentStatus = "Прибыл в регион";
    //                 } elseif ($orders[$i]->on_the_way == 1) {
    //                     $orders[$i]->currentStatus = "В пути";
    //                 } elseif ($orders[$i]->ready_to_send == 1) {
    //                     $orders[$i]->currentStatus = "Готов к отправке";
    //                 } elseif ($orders[$i]->delivered_to_warehouse == 1) {
    //                     $orders[$i]->currentStatus = "Прибыл на склад";
    //                 } elseif ($orders[$i]->taken_by_courier == 1) {
    //                     $orders[$i]->currentStatus = "Забран курьером";
    //                 } elseif ($orders[$i]->transferred_to_courier == 1) {
    //                     $orders[$i]->currentStatus = "Передан курьеру";
    //                 } elseif ($orders[$i]->accepted_by_dispatcher == 1) {
    //                     $orders[$i]->currentStatus = "Принято диспетчером";
    //                 }
    //             }
    //             header('Content-Type: application/json');
    //             $json = json_encode($orders, JSON_PRETTY_PRINT);
    //             print_r($json);
    //         } else {
    //             if ($dateLast != null) {
    //                 $this->getOrdersTillDate($dateLast);
    //             } elseif ($dateFirst != null) {
    //                 $this->getOrdersFromDate($dateFirst);
    //             }
    //         }
    //     }
    // }

    // public function getOrdersFromDate()
    // {
    //     $headers = apache_request_headers();
    //     if (!isset($headers['token'])) {
    //         print_r("unauthorized");
    //     } else {
    //         $token  = $headers['token'];
    //         $orders = DB::table('orders_new')
    //             ->join('leads_new', 'orders_new.lead_id', '=', 'leads_new.lead_id')
    //             ->join('contragents', 'sender_company_name', '=', 'contragents.full_name')
    //             ->join('directions', 'orders_new.direction', '=', 'directions.code')
    //             ->join('tokens', function ($join) {
    //                 $join->on('contragents.id', '=', 'tokens.id');
    //                 // ->orOn('sender_person_iin',  '=', 'tokens.id');
    //             })
    //             ->where('token', $token)
    //             ->where('date_from', '>=', $date)
    //             ->where('token', $token)
    //             ->whereNotNull('waybill')
    //             ->select('order_id', 'order_creation_time', 'leads_new.order_number', 'waybill', 'directions.direction', 'delivery_type', 'shipped', 'shipping', 'in_region', 'on_the_way', 'ready_to_send', 'delivered_to_warehouse', 'taken_by_driver', 'transferred_to_driver', 'accepted_by_dispatcher')
    //             ->get();
    //         for ($i = 0; $i < count($orders); $i++) {
    //             if ($orders[$i]->shipped == 1) {
    //                 $orders[$i]->currentStatus = "Доставлен";
    //             } elseif ($orders[$i]->shipping == 1) {
    //                 $orders[$i]->currentStatus = "На доставке";
    //             } elseif ($orders[$i]->in_region == 1) {
    //                 $orders[$i]->currentStatus = "Прибыл в регион";
    //             } elseif ($orders[$i]->on_the_way == 1) {
    //                 $orders[$i]->currentStatus = "В пути";
    //             } elseif ($orders[$i]->ready_to_send == 1) {
    //                 $orders[$i]->currentStatus = "Готов к отправке";
    //             } elseif ($orders[$i]->delivered_to_warehouse == 1) {
    //                 $orders[$i]->currentStatus = "Прибыл на склад";
    //             } elseif ($orders[$i]->taken_by_courier == 1) {
    //                 $orders[$i]->currentStatus = "Забран курьером";
    //             } elseif ($orders[$i]->transferred_to_courier == 1) {
    //                 $orders[$i]->currentStatus = "Передан курьеру";
    //             } elseif ($orders[$i]->accepted_by_dispatcher == 1) {
    //                 $orders[$i]->currentStatus = "Принято диспетчером";
    //             }
    //         }
    //         header('Content-Type: application/json');
    //         $json = json_encode($orders, JSON_PRETTY_PRINT);
    //         print_r($json);
    //     }
    // }

        // public function getOrdersByStatus($id)
    // {
    //     // $headers = apache_request_headers();
    //     // if(!isset($headers['token'])){
    //     //     print_r("unauthorized");
    //     // }
    //     // else{
    //     //     $token  =    $headers['token'];
    //     if ($_GET['token'] == '' || is_null($_GET['token'])) {
    //         print_r("unauthorized");
    //     } else {
    //         $token  = $_GET['token'];
    //         $status = $id;
    //         switch ($status) {
    //             case '0':
    //                 $orders = DB::table('orders_new')
    //                     ->join('leads_new', 'orders_new.lead_id', '=', 'leads_new.lead_id')
    //                     ->join('contragents', 'sender_company_name', '=', 'contragents.full_name')
    //                     ->join('directions', 'orders_new.direction', '=', 'directions.code')
    //                     ->join('tokens', function ($join) {
    //                         $join->on('contragents.id', '=', 'tokens.id');
    //                         // ->orOn('sender_person_iin',  '=', 'tokens.id');
    //                     })
    //                     ->where('token', '=', $token)
    //                     ->where('accepted_by_dispatcher', '=', '1')
    //                     ->where('transferred_to_driver', '=', '0')
    //                     ->where('taken_by_driver', '=', '0')
    //                     ->where('delivered_to_warehouse', '=', '0')
    //                     ->where('ready_to_send', '=', '0')
    //                     ->where('on_the_way', '=', '0')
    //                     ->where('in_region', '=', '0')
    //                     ->where('shipping', '=', '0')
    //                     ->where('shipped', '=', '0')
    //                     ->whereNotNull('waybill')
    //                     ->select('order_id', 'order_creation_time', 'leads_new.order_number', 'waybill', 'directions.direction', 'delivery_type', 'shipped', 'shipping', 'in_region', 'on_the_way', 'ready_to_send', 'delivered_to_warehouse', 'taken_by_driver', 'transferred_to_driver', 'accepted_by_dispatcher')
    //                     ->get();
    //                 for ($i = 0; $i < count($orders); $i++) {
    //                     $orders[$i]->currentStatus = "Принято диспетчером";
    //                 }
    //                 header('Content-Type: application/json');
    //                 $json = json_encode($orders, JSON_PRETTY_PRINT);
    //                 print_r($json);
    //                 break;
    //             case '1':
    //                 $orders = DB::table('orders_new')
    //                     ->join('leads_new', 'orders_new.lead_id', '=', 'leads_new.lead_id')
    //                     ->join('contragents', 'sender_company_name', '=', 'contragents.full_name')
    //                     ->join('directions', 'orders_new.direction', '=', 'directions.code')
    //                     ->join('tokens', function ($join) {
    //                         $join->on('contragents.id', '=', 'tokens.id');
    //                         // ->orOn('sender_person_iin',  '=', 'tokens.id');
    //                     })
    //                     ->where('token', '=', $token)
    //                     ->where('transferred_to_courier', '=', '1')
    //                     ->where('taken_by_courier', '=', '0')
    //                     ->where('delivered_to_warehouse', '=', '0')
    //                     ->where('ready_to_send', '=', '0')
    //                     ->where('on_the_way', '=', '0')
    //                     ->where('in_region', '=', '0')
    //                     ->where('shipping', '=', '0')
    //                     ->where('shipped', '=', '0')
    //                     ->whereNotNull('waybill')
    //                     ->select('order_id', 'order_creation_time', 'leads_new.order_number', 'waybill', 'directions.direction', 'delivery_type', 'shipped', 'shipping', 'in_region', 'on_the_way', 'ready_to_send', 'delivered_to_warehouse', 'taken_by_driver', 'transferred_to_driver', 'accepted_by_dispatcher')
    //                     ->get();
    //                 for ($i = 0; $i < count($orders); $i++) {
    //                     $orders[$i]->currentStatus = "Передан курьеру";
    //                 }
    //                 header('Content-Type: application/json');
    //                 $json = json_encode($orders, JSON_PRETTY_PRINT);
    //                 print_r($json);
    //                 break;
    //             case '2':
    //                 $orders = DB::table('orders_new')
    //                     ->join('leads_new', 'orders_new.lead_id', '=', 'leads_new.lead_id')
    //                     ->join('contragents', 'sender_company_name', '=', 'contragents.full_name')
    //                     ->join('directions', 'orders_new.direction', '=', 'directions.code')
    //                     ->join('tokens', function ($join) {
    //                         $join->on('contragents.id', '=', 'tokens.id');
    //                         // ->orOn('sender_person_iin',  '=', 'tokens.id');
    //                     })
    //                     ->where('token', '=', $token)
    //                     ->where('taken_by_courier', '=', '1')
    //                     ->where('delivered_to_warehouse', '=', '0')
    //                     ->where('ready_to_send', '=', '0')
    //                     ->where('on_the_way', '=', '0')
    //                     ->where('in_region', '=', '0')
    //                     ->where('shipping', '=', '0')
    //                     ->where('shipped', '=', '0')
    //                     ->whereNotNull('waybill')
    //                     ->select('order_id', 'order_creation_time', 'leads_new.order_number', 'waybill', 'directions.direction', 'delivery_type', 'shipped', 'shipping', 'in_region', 'on_the_way', 'ready_to_send', 'delivered_to_warehouse', 'taken_by_driver', 'transferred_to_driver', 'accepted_by_dispatcher')
    //                     ->get();
    //                 for ($i = 0; $i < count($orders); $i++) {
    //                     $orders[$i]->currentStatus = "Забран курьером";
    //                 }
    //                 header('Content-Type: application/json');
    //                 $json = json_encode($orders, JSON_PRETTY_PRINT);
    //                 print_r($json);
    //                 break;
    //             case '3':
    //                 $orders = DB::table('orders_new')
    //                     ->join('leads_new', 'orders_new.lead_id', '=', 'leads_new.lead_id')
    //                     ->join('contragents', 'sender_company_name', '=', 'contragents.full_name')
    //                     ->join('directions', 'orders_new.direction', '=', 'directions.code')
    //                     ->join('tokens', function ($join) {
    //                         $join->on('contragents.id', '=', 'tokens.id');
    //                         // ->orOn('sender_person_iin',  '=', 'tokens.id');
    //                     })
    //                     ->where('token', '=', $token)
    //                     ->where('delivered_to_warehouse', '=', '1')
    //                     ->where('ready_to_send', '=', '0')
    //                     ->where('on_the_way', '=', '0')
    //                     ->where('in_region', '=', '0')
    //                     ->where('shipping', '=', '0')
    //                     ->where('shipped', '=', '0')
    //                     ->whereNotNull('waybill')
    //                     ->select('order_id', 'order_creation_time', 'leads_new.order_number', 'waybill', 'directions.direction', 'delivery_type', 'shipped', 'shipping', 'in_region', 'on_the_way', 'ready_to_send', 'delivered_to_warehouse', 'taken_by_driver', 'transferred_to_driver', 'accepted_by_dispatcher')
    //                     ->get();
    //                 for ($i = 0; $i < count($orders); $i++) {
    //                     $orders[$i]->currentStatus = "Прибыл на склад";
    //                 }
    //                 header('Content-Type: application/json');
    //                 $json = json_encode($orders, JSON_PRETTY_PRINT);
    //                 print_r($json);
    //                 break;
    //             case '4':
    //                 $orders = DB::table('orders_new')
    //                     ->join('leads_new', 'orders_new.lead_id', '=', 'leads_new.lead_id')
    //                     ->join('directions', 'orders_new.direction', '=', 'directions.code')
    //                     ->join('contragents', 'sender_company_name', '=', 'contragents.full_name')
    //                     ->join('tokens', function ($join) {
    //                         $join->on('contragents.id', '=', 'tokens.id');
    //                         // ->orOn('sender_person_iin',  '=', 'tokens.id');
    //                     })
    //                     ->where('token', '=', $token)
    //                     ->where('ready_to_send', '=', '1')
    //                     ->where('on_the_way', '=', '0')
    //                     ->where('in_region', '=', '0')
    //                     ->where('shipping', '=', '0')
    //                     ->where('shipped', '=', '0')
    //                     ->whereNotNull('waybill')
    //                     ->select('order_id', 'order_creation_time', 'leads_new.order_number', 'waybill', 'directions.direction', 'delivery_type', 'shipped', 'shipping', 'in_region', 'on_the_way', 'ready_to_send', 'delivered_to_warehouse', 'taken_by_driver', 'transferred_to_driver', 'accepted_by_dispatcher')
    //                     ->get();
    //                 for ($i = 0; $i < count($orders); $i++) {
    //                     $orders[$i]->currentStatus = "Готов к отправке";
    //                 }
    //                 header('Content-Type: application/json');
    //                 $json = json_encode($orders, JSON_PRETTY_PRINT);
    //                 print_r($json);
    //                 break;
    //             case '5':
    //                 $orders = DB::table('orders_new')
    //                     ->join('leads_new', 'orders_new.lead_id', '=', 'leads_new.lead_id')
    //                     ->join('directions', 'orders_new.direction', '=', 'directions.code')
    //                     ->join('contragents', 'sender_company_name', '=', 'contragents.full_name')
    //                     ->join('tokens', function ($join) {
    //                         $join->on('contragents.id', '=', 'tokens.id');
    //                         // ->orOn('sender_person_iin',  '=', 'tokens.id');
    //                     })
    //                     ->where('token', '=', $token)
    //                     ->where('on_the_way', '=', '1')
    //                     ->where('in_region', '=', '0')
    //                     ->where('shipping', '=', '0')
    //                     ->where('shipped', '=', '0')
    //                     ->whereNotNull('waybill')
    //                     ->select('order_id', 'order_creation_time', 'leads_new.order_number', 'waybill', 'directions.direction', 'delivery_type', 'shipped', 'shipping', 'in_region', 'on_the_way', 'ready_to_send', 'delivered_to_warehouse', 'taken_by_driver', 'transferred_to_driver', 'accepted_by_dispatcher')
    //                     ->get();
    //                 for ($i = 0; $i < count($orders); $i++) {
    //                     $orders[$i]->currentStatus = "В пути";
    //                 }
    //                 header('Content-Type: application/json');
    //                 $json = json_encode($orders, JSON_PRETTY_PRINT);
    //                 print_r($json);
    //                 break;
    //             case '6':
    //                 $orders = DB::table('orders_new')
    //                     ->join('leads_new', 'orders_new.lead_id', '=', 'leads_new.lead_id')
    //                     ->join('directions', 'orders_new.direction', '=', 'directions.code')
    //                     ->join('contragents', 'sender_company_name', '=', 'contragents.full_name')
    //                     ->join('tokens', function ($join) {
    //                         $join->on('contragents.id', '=', 'tokens.id');
    //                         // ->orOn('sender_person_iin',  '=', 'tokens.id');
    //                     })
    //                     ->where('token', '=', $token)
    //                     ->where('in_region', '=', '1')
    //                     ->where('shipping', '=', '0')
    //                     ->where('shipped', '=', '0')
    //                     ->whereNotNull('waybill')
    //                     ->select('order_id', 'order_creation_time', 'leads_new.order_number', 'waybill', 'directions.direction', 'delivery_type', 'shipped', 'shipping', 'in_region', 'on_the_way', 'ready_to_send', 'delivered_to_warehouse', 'taken_by_driver', 'transferred_to_driver', 'accepted_by_dispatcher')
    //                     ->get();
    //                 for ($i = 0; $i < count($orders); $i++) {
    //                     $orders[$i]->currentStatus = "Прибыл в регион";
    //                 }
    //                 header('Content-Type: application/json');
    //                 $json = json_encode($orders, JSON_PRETTY_PRINT);
    //                 print_r($json);
    //                 break;
    //             case '7':
    //                 $orders = DB::table('orders_new')
    //                     ->join('leads_new', 'orders_new.lead_id', '=', 'leads_new.lead_id')
    //                     ->join('directions', 'orders_new.direction', '=', 'directions.code')
    //                     ->join('contragents', 'sender_company_name', '=', 'contragents.full_name')
    //                     ->join('tokens', function ($join) {
    //                         $join->on('contragents.id', '=', 'tokens.id');
    //                         // ->orOn('sender_person_iin',  '=', 'tokens.id');
    //                     })
    //                     ->where('token', '=', $token)
    //                     ->where('shipping', '=', '1')
    //                     ->where('shipped', '=', '0')
    //                     ->whereNotNull('waybill')
    //                     ->select('order_id', 'order_creation_time', 'leads_new.order_number', 'waybill', 'directions.direction', 'delivery_type', 'shipped', 'shipping', 'in_region', 'on_the_way', 'ready_to_send', 'delivered_to_warehouse', 'taken_by_driver', 'transferred_to_driver', 'accepted_by_dispatcher')
    //                     ->get();
    //                 for ($i = 0; $i < count($orders); $i++) {
    //                     $orders[$i]->currentStatus = "На доставке";
    //                 }
    //                 header('Content-Type: application/json');
    //                 $json = json_encode($orders, JSON_PRETTY_PRINT);
    //                 print_r($json);
    //                 break;
    //             case '8':
    //                 $orders = DB::table('orders_new')
    //                     ->join('leads_new', 'orders_new.lead_id', '=', 'leads_new.lead_id')
    //                     ->join('directions', 'orders_new.direction', '=', 'directions.code')
    //                     ->join('contragents', 'sender_company_name', '=', 'contragents.full_name')
    //                     ->join('tokens', function ($join) {
    //                         $join->on('contragents.id', '=', 'tokens.id');
    //                         // ->orOn('sender_person_iin',  '=', 'tokens.id');
    //                     })
    //                     ->where('token', '=', $token)
    //                     ->where('shipped', '=', '1')
    //                     ->whereNotNull('waybill')
    //                     ->select('order_id', 'order_creation_time', 'leads_new.order_number', 'waybill', 'directions.direction', 'delivery_type', 'shipped', 'shipping', 'in_region', 'on_the_way', 'ready_to_send', 'delivered_to_warehouse', 'taken_by_driver', 'transferred_to_driver', 'accepted_by_dispatcher')
    //                     ->get();
    //                 for ($i = 0; $i < count($orders); $i++) {
    //                     if ($orders[$i]->shipped == 1) {
    //                         $orders[$i]->currentStatus = "Доставлен";
    //                     }
    //                 }
    //                 header('Content-Type: application/json');
    //                 $json = json_encode($orders, JSON_PRETTY_PRINT);
    //                 print_r($json);
    //                 break;
    //             default:
    //                 $error = "Произошла какая то ошибка, пожалуйста перезагрузите страницу";
    //                 print_r($error);
    //                 break;
    //         }
    //     }
    // }

    // public function getOrdersByDirection()
    // {
    //     $headers = apache_request_headers();
    //     if (!isset($headers['token'])) {
    //         print_r("unauthorized");
    //     } else {
    //         $token = $headers['token'];
    //         $from  = $_GET['from'];
    //         $to    = $_GET['to'];
    //         if ($from != '' && $to != '' && !is_null($from) && !is_null($to)) {
    //             $orders = DB::table('orders_new')
    //                 ->join('leads_new', 'orders_new.lead_id', '=', 'leads_new.lead_id')
    //                 ->join('contragents', 'sender_company_name', '=', 'contragents.full_name')
    //                 ->join('directions', 'orders_new.direction', '=', 'directions.code')
    //                 ->join('tokens', function ($join) {
    //                     $join->on('contragents.id', '=', 'tokens.id');
    //                     // ->orOn('sender_person_iin',  '=', 'tokens.id');
    //                 })
    //                 ->where('token', '=', $token)
    //                 ->where('directions.direction', '=', $from . ' - ' . $to)
    //                 ->whereNotNull('waybill')
    //                 ->select('order_id', 'order_creation_time', 'leads_new.order_number', 'waybill', 'directions.direction', 'delivery_type', 'shipped', 'shipping', 'in_region', 'on_the_way', 'ready_to_send', 'delivered_to_warehouse', 'taken_by_driver', 'transferred_to_driver', 'accepted_by_dispatcher')
    //                 ->get();
    //             for ($i = 0; $i < count($orders); $i++) {
    //                 if ($orders[$i]->shipped == 1) {
    //                     $orders[$i]->currentStatus = "Доставлен";
    //                 } elseif ($orders[$i]->shipping == 1) {
    //                     $orders[$i]->currentStatus = "На доставке";
    //                 } elseif ($orders[$i]->in_region == 1) {
    //                     $orders[$i]->currentStatus = "Прибыл в регион";
    //                 } elseif ($orders[$i]->on_the_way == 1) {
    //                     $orders[$i]->currentStatus = "В пути";
    //                 } elseif ($orders[$i]->ready_to_send == 1) {
    //                     $orders[$i]->currentStatus = "Готов к отправке";
    //                 } elseif ($orders[$i]->delivered_to_warehouse == 1) {
    //                     $orders[$i]->currentStatus = "Прибыл на склад";
    //                 } elseif ($orders[$i]->taken_by_driver == 1) {
    //                     $orders[$i]->currentStatus = "Забран курьером";
    //                 } elseif ($orders[$i]->transferred_to_driver == 1) {
    //                     $orders[$i]->currentStatus = "Передан курьеру";
    //                 } elseif ($orders[$i]->accepted_by_dispatcher == 1) {
    //                     $orders[$i]->currentStatus = "Принято диспетчером";
    //                 }
    //             }
    //             header('Content-Type: application/json');
    //             $json = json_encode($orders, JSON_PRETTY_PRINT);
    //             print_r($json);
    //         } else {
    //             if ($from == '' || is_null($from)) {
    //                 $orders = DB::table('orders_new')
    //                     ->join('leads_new', 'orders_new.lead_id', '=', 'leads_new.lead_id')
    //                     ->join('contragents', 'sender_company_name', '=', 'contragents.full_name')
    //                     ->join('directions', 'orders_new.direction', '=', 'directions.code')
    //                     ->join('tokens', function ($join) {
    //                         $join->on('contragents.id', '=', 'tokens.id');
    //                         // ->orOn('sender_person_iin',  '=', 'tokens.id');
    //                     })
    //                     ->where('token', '=', $token)
    //                     ->where('directions.direction', 'like', '% - ' . $to)
    //                     ->whereNotNull('waybill')
    //                     ->select('order_id', 'order_creation_time', 'leads_new.order_number', 'waybill', 'directions.direction', 'delivery_type', 'shipped', 'shipping', 'in_region', 'on_the_way', 'ready_to_send', 'delivered_to_warehouse', 'taken_by_driver', 'transferred_to_driver', 'accepted_by_dispatcher')
    //                     ->get();
    //                 for ($i = 0; $i < count($orders); $i++) {
    //                     if ($orders[$i]->shipped == 1) {
    //                         $orders[$i]->currentStatus = "Доставлен";
    //                     } elseif ($orders[$i]->shipping == 1) {
    //                         $orders[$i]->currentStatus = "На доставке";
    //                     } elseif ($orders[$i]->in_region == 1) {
    //                         $orders[$i]->currentStatus = "Прибыл в регион";
    //                     } elseif ($orders[$i]->on_the_way == 1) {
    //                         $orders[$i]->currentStatus = "В пути";
    //                     } elseif ($orders[$i]->ready_to_send == 1) {
    //                         $orders[$i]->currentStatus = "Готов к отправке";
    //                     } elseif ($orders[$i]->delivered_to_warehouse == 1) {
    //                         $orders[$i]->currentStatus = "Прибыл на склад";
    //                     } elseif ($orders[$i]->taken_by_driver == 1) {
    //                         $orders[$i]->currentStatus = "Забран курьером";
    //                     } elseif ($orders[$i]->transferred_to_driver == 1) {
    //                         $orders[$i]->currentStatus = "Передан курьеру";
    //                     } elseif ($orders[$i]->accepted_by_dispatcher == 1) {
    //                         $orders[$i]->currentStatus = "Принято диспетчером";
    //                     }
    //                 }
    //                 header('Content-Type: application/json');
    //                 $json = json_encode($orders, JSON_PRETTY_PRINT);
    //                 print_r($json);
    //             }
    //             if ($to == '' || is_null($to)) {
    //                 $orders = DB::table('orders_new')
    //                     ->join('leads_new', 'orders_new.lead_id', '=', 'leads_new.lead_id')
    //                     ->join('contragents', 'sender_company_name', '=', 'contragents.full_name')
    //                     ->join('directions', 'orders_new.direction', '=', 'directions.code')
    //                     ->join('tokens', function ($join) {
    //                         $join->on('contragents.id', '=', 'tokens.id');
    //                         // ->orOn('sender_person_iin',  '=', 'tokens.id');
    //                     })
    //                     ->where('token', '=', $token)
    //                     ->where('directions.direction', 'like', $from . ' - %')
    //                     ->whereNotNull('waybill')
    //                     ->select('order_id', 'order_creation_time', 'leads_new.order_number', 'waybill', 'directions.direction', 'delivery_type', 'shipped', 'shipping', 'in_region', 'on_the_way', 'ready_to_send', 'delivered_to_warehouse', 'taken_by_driver', 'transferred_to_driver', 'accepted_by_dispatcher')
    //                     ->get();
    //                 for ($i = 0; $i < count($orders); $i++) {
    //                     if ($orders[$i]->shipped == 1) {
    //                         $orders[$i]->currentStatus = "Доставлен";
    //                     } elseif ($orders[$i]->shipping == 1) {
    //                         $orders[$i]->currentStatus = "На доставке";
    //                     } elseif ($orders[$i]->in_region == 1) {
    //                         $orders[$i]->currentStatus = "Прибыл в регион";
    //                     } elseif ($orders[$i]->on_the_way == 1) {
    //                         $orders[$i]->currentStatus = "В пути";
    //                     } elseif ($orders[$i]->ready_to_send == 1) {
    //                         $orders[$i]->currentStatus = "Готов к отправке";
    //                     } elseif ($orders[$i]->delivered_to_warehouse == 1) {
    //                         $orders[$i]->currentStatus = "Прибыл на склад";
    //                     } elseif ($orders[$i]->taken_by_driver == 1) {
    //                         $orders[$i]->currentStatus = "Забран курьером";
    //                     } elseif ($orders[$i]->transferred_to_driver == 1) {
    //                         $orders[$i]->currentStatus = "Передан курьеру";
    //                     } elseif ($orders[$i]->accepted_by_dispatcher == 1) {
    //                         $orders[$i]->currentStatus = "Принято диспетчером";
    //                     }
    //                 }
    //                 header('Content-Type: application/json');
    //                 $json = json_encode($orders, JSON_PRETTY_PRINT);
    //                 print_r($json);
    //             }
    //             if (($to == '' || is_null($to)) && ($from == '' || is_null($from))) {
    //                 print_r('address not specified');
    //             }
    //         }
    //         // print_r(json_encode($_GET['to']));
    //         // print_r($from);
    //     }
    // }

        // public function addNote($leadId, $noteText)
    // {
    //     // Data for note, used to deal with orders
    //     $data = [
    //         'add' => [
    //             0 => [
    //                 'element_id'          => $leadId,
    //                 'element_type'        => '2',
    //                 'text'                => $noteText,
    //                 'note_type'           => '4',
    //                 'created_at'          => time(),
    //                 'responsible_user_id' => '1542400',
    //                 'created_by'          => '1542400',
    //             ],
    //         ],
    //     ];
    //     $subdomain = 'sparkcrm'; #Наш аккаунт - поддомен
    //     $link      = 'https://' . $subdomain . '.amocrm.ru/api/v2/notes';
    //     /* Нам необходимо инициировать запрос к серверу. Воспользуемся библиотекой cURL (поставляется в составе PHP). Подробнее о
    //     работе с этой
    //     библиотекой Вы можете прочитать в мануале. */
    //     $curl = curl_init(); #Сохраняем дескриптор сеанса cURL
    //     #Устанавливаем необходимые опции для сеанса cURL
    //     curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
    //     curl_setopt($curl, CURLOPT_USERAGENT, 'amoCRM-API-client/1.0');
    //     curl_setopt($curl, CURLOPT_URL, $link);
    //     curl_setopt($curl, CURLOPT_CUSTOMREQUEST, 'POST');
    //     curl_setopt($curl, CURLOPT_POSTFIELDS, json_encode($data));
    //     curl_setopt($curl, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
    //     curl_setopt($curl, CURLOPT_HEADER, false);
    //     curl_setopt($curl, CURLOPT_COOKIEFILE, dirname(__FILE__) . '/cookie.txt'); #PHP>5.3.6 dirname(__FILE__) -> __DIR__
    //     curl_setopt($curl, CURLOPT_COOKIEJAR, dirname(__FILE__) . '/cookie.txt'); #PHP>5.3.6 dirname(__FILE__) -> __DIR__
    //     curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, 0);
    //     curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 0);
    //     $out  = curl_exec($curl); #Инициируем запрос к API и сохраняем ответ в переменную
    //     $code = curl_getinfo($curl, CURLINFO_HTTP_CODE);
    //     /* Теперь мы можем обработать ответ, полученный от сервера. Это пример. Вы можете обработать данные своим способом. */
    //     $code   = (int) $code;
    //     $errors = [
    //         301 => 'Moved permanently',
    //         400 => 'Bad request',
    //         401 => 'Unauthorized',
    //         403 => 'Forbidden',
    //         404 => 'Not found',
    //         500 => 'Internal server error',
    //         502 => 'Bad gateway',
    //         503 => 'Service unavailable',
    //     ];
    //     try
    //     {
    //         #Если код ответа не равен 200 или 204 - возвращаем сообщение об ошибке
    //         if ($code != 200 && $code != 204) {
    //             throw new Exception(isset($errors[$code]) ? $errors[$code] : 'Undescribed error', $code);
    //         }

    //     } catch (Exception $E) {
    //         print_r('Ошибка: ' . $E->getMessage() . PHP_EOL . 'Код ошибки: ' . $E->getCode());
    //     }
    // }

        // public function moveLeadToWork($id)
    // {
    //     $this->amoAuth();
    //     $leads['update'] = [];

    //     $lead = [
    //         'id'         => $id,
    //         'status_id'  => 15237505,
    //         'updated_at' => time(),
    //     ];
    //     array_push($leads['update'], $lead);

    //     /* Теперь подготовим данные, необходимые для запроса к серверу */
    //     $subdomain = 'sparkcrm'; #Наш аккаунт - поддомен
    //     #Формируем ссылку для запроса
    //     $link = 'https://' . $subdomain . '.amocrm.ru/api/v2/leads';
    //     /* Нам необходимо инициировать запрос к серверу. Воспользуемся библиотекой cURL (поставляется в составе PHP). Подробнее о
    //     работе с этой
    //     библиотекой Вы можете прочитать в мануале. */
    //     $curl = curl_init(); #Сохраняем дескриптор сеанса cURL
    //     #Устанавливаем необходимые опции для сеанса cURL
    //     curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
    //     curl_setopt($curl, CURLOPT_USERAGENT, 'amoCRM-API-client/1.0');
    //     curl_setopt($curl, CURLOPT_URL, $link);
    //     curl_setopt($curl, CURLOPT_CUSTOMREQUEST, 'POST');
    //     curl_setopt($curl, CURLOPT_POSTFIELDS, json_encode($leads));
    //     curl_setopt($curl, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
    //     curl_setopt($curl, CURLOPT_HEADER, false);
    //     curl_setopt($curl, CURLOPT_COOKIEFILE, dirname(__FILE__) . '/cookie.txt'); #PHP>5.3.6 dirname(__FILE__) -> __DIR__
    //     curl_setopt($curl, CURLOPT_COOKIEJAR, dirname(__FILE__) . '/cookie.txt'); #PHP>5.3.6 dirname(__FILE__) -> __DIR__
    //     curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, 0);
    //     curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 0);
    //     $out  = curl_exec($curl); #Инициируем запрос к API и сохраняем ответ в переменную
    //     $code = curl_getinfo($curl, CURLINFO_HTTP_CODE);
    //     // Теперь мы можем обработать ответ, полученный от сервера. Это пример. Вы можете обработать данные своим способом.
    //     $code   = (int) $code;
    //     $errors = [
    //         301 => 'Moved permanently',
    //         400 => 'Bad request',
    //         401 => 'Unauthorized',
    //         403 => 'Forbidden',
    //         404 => 'Not found',
    //         500 => 'Internal server error',
    //         502 => 'Bad gateway',
    //         503 => 'Service unavailable',
    //     ];
    //     try
    //     {
    //         #Если код ответа не равен 200 или 204 - возвращаем сообщение об ошибке
    //         if ($code != 200 && $code != 204) {
    //             throw new Exception(isset($errors[$code]) ? $errors[$code] : 'Undescribed error', $code);
    //         }
    //     } catch (Exception $E) {
    //         die('Ошибка: ' . $E->getMessage() . PHP_EOL . 'Код ошибки: ' . $E->getCode());
    //     }
    // }

    // public function addOrderToDB($type)
    // {
    //     $this->amoAuth();
    //     $id        = $_POST['leads']['status'][0]['id'];
    //     $subdomain = 'sparkcrm';
    //     // $link='https://'.$subdomain.'.amocrm.ru/api/v2/leads';
    //     // https://sparkcrm.amocrm.ru/api/v2/leads?&status[1]=15272152&status[2]=15327316&status[3]=18394894&status[4]=18394897&status[5]=18394900&status[6]=18394903&status[7]=18394906&status[8]=18394909&status[9]=18394912&status[10]=18394915&status[11]=18519910

    //     $link = 'https://' . $subdomain . '.amocrm.ru/api/v2/leads?id=' . $id;

    //     $curl = curl_init();
    //     curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
    //     curl_setopt($curl, CURLOPT_USERAGENT, 'amoCRM-API-client/1.0');
    //     curl_setopt($curl, CURLOPT_URL, $link);
    //     curl_setopt($curl, CURLOPT_HEADER, false);
    //     curl_setopt($curl, CURLOPT_COOKIEFILE, dirname(__FILE__) . '/cookie.txt'); #PHP>5.3.6 dirname(__FILE__) -> __DIR__
    //     curl_setopt($curl, CURLOPT_COOKIEJAR, dirname(__FILE__) . '/cookie.txt'); #PHP>5.3.6 dirname(__FILE__) -> __DIR__
    //     curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, 0);
    //     curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 0);
    //     curl_setopt($curl, CURLOPT_HTTPHEADER, ['IF-MODIFIED-SINCE: Mon, 01 Aug 2013 07:07:23']);

    //     $out  = curl_exec($curl); #Инициируем запрос к API и сохраняем ответ в переменную
    //     $code = curl_getinfo($curl, CURLINFO_HTTP_CODE);
    //     curl_close($curl);
    //     $code   = (int) $code;
    //     $errors = [
    //         301 => 'Moved permanently',
    //         400 => 'Bad request',
    //         401 => 'Unauthorized',
    //         403 => 'Forbidden',
    //         404 => 'Not found',
    //         500 => 'Internal server error',
    //         502 => 'Bad gateway',
    //         503 => 'Service unavailable',
    //     ];
    //     try
    //     {
    //         /* Если код ответа не равен 200 или 204 - возвращаем сообщение об ошибке */
    //         if ($code != 200 && $code != 204) {
    //             throw new Exception(isset($errors[$code]) ? $errors[$code] : 'Undescribed error', $code);
    //         }
    //     } catch (Exception $E) {
    //         die('Ошибка: ' . $E->getMessage() . PHP_EOL . 'Код ошибки: ' . $E->getCode() . ' ' . $status_id);
    //     }
    //     /*
    //     Данные получаем в формате JSON, поэтому, для получения читаемых данных,
    //     нам придётся перевести ответ в формат, понятный PHP
    //      */
    //     $r = json_decode($out, true);

    //     $r       = $r['_embedded']['items'];
    //     $company = $this->getCompany($r[0]['company']['id']);
    //     for ($i = 0; $i < count($company['custom_fields']); $i++) {
    //         if ($company['custom_fields'][$i]['id'] == '134605') {
    //             $lead['client_full_name']    = str_replace('&quot;', '"', $company['custom_fields'][$i]['values'][0]['value']);
    //             $lead['sender_company_name'] = str_replace('&quot;', '"', $company['custom_fields'][$i]['values'][0]['value']);
    //         }
    //     }

    //     for ($i = 0; $i < count($r[0]['custom_fields']); $i++) {
    //         if ($r[0]['custom_fields'][$i]['id'] == '447515') {
    //             $lead['address_from'] = $r[0]['custom_fields'][$i]['values'][0]['value'];
    //         }
    //         if ($r[0]['custom_fields'][$i]['id'] == '452899') {
    //             $lead['sender_mobile_phone'] = $r[0]['custom_fields'][$i]['values'][0]['value'];
    //         }
    //         if ($r[0]['custom_fields'][$i]['id'] == '447513') {
    //             $lead['filial'] = $r[0]['custom_fields'][$i]['values'][0]['value'];
    //         }
    //         if ($r[0]['custom_fields'][$i]['id'] == '447519') {
    //             $lead['sender_comments'] = $r[0]['custom_fields'][$i]['values'][0]['value'];
    //         }
    //         if ($r[0]['custom_fields'][$i]['id'] == '447517') {
    //             $lead['date_from'] = $r[0]['custom_fields'][$i]['values'][0]['value'];
    //         }
    //         if ($r[0]['custom_fields'][$i]['id'] == '447525') {
    //             $lead['delivery_type'] = $r[0]['custom_fields'][$i]['values'][0]['value'];
    //         }
    //         if ($r[0]['custom_fields'][$i]['id'] == '452915') {
    //             $lead['sender_person'] = $r[0]['custom_fields'][$i]['values'][0]['value'];
    //         }
    //         if ($r[0]['custom_fields'][$i]['id'] == '447521') {
    //             $lead['cargo_ready_time'] = $r[0]['custom_fields'][$i]['values'][0]['value'];
    //         }
    //         switch ($r[0]['created_by']) {
    //             case '2147611':
    //                 $lead['author'] = 'Дюсенова Зарина';
    //                 break;
    //             case '2180614':
    //                 $lead['author'] = 'Нурканова Эльмира';
    //                 break;
    //             case '2180617':
    //                 $lead['author'] = 'Дворецкий Виталий';
    //                 break;
    //             case '2180620':
    //                 $lead['author'] = 'Жимбаева Эльмира';
    //                 break;
    //             case '2180623':
    //                 $lead['author'] = 'Мулдагалиева Эльвира';
    //                 break;
    //             case '2180626':
    //                 $lead['author'] = 'Краева Азалия';
    //                 break;
    //             case '2180629':
    //                 $lead['author'] = 'Аликулова Ляззат';
    //                 break;
    //             case '2180632':
    //                 $lead['author'] = 'Нурдаулетов Азамат';
    //                 break;
    //             case '2180647':
    //                 $lead['author'] = 'Ахметов Мейрулан';
    //                 break;
    //             case '2180650':
    //                 $lead['author'] = 'Шахиджанов Максим';
    //                 break;
    //             default:
    //                 $lead['author'] = 'Сидиров Сергей';
    //                 break;
    //         }
    //     }
    //     sleep(1);
    //     $lead_existing = DB::table('leads_new')
    //         ->where('lead_id', $id)
    //         ->get();
    //     if ($lead_existing->isEmpty()) {
    //         $lead['lead_id']             = $id;
    //         $lead['order_creation_time'] = Carbon::now();
    //         DB::table('leads_new')
    //             ->insert($lead);
    //         if ($type == 1) {
    //             $this->addNote($id, 'Для отправки заказа в 1С, пройдите по этой ссылке:' . "\n" . 'http://45.32.153.55/cabinet/iddqd.html?lead_id=' . $id . "&created_by=" . $r[0]['created_by']);
    //         }
    //     } else {
    //         DB::table('leads_new')
    //             ->where('lead_id', $lead_existing[0]->lead_id)
    //             ->update($lead);
    //         if ($type == 1) {
    //             $this->addNote($id, 'Для отправки заказа в 1С, пройдите по этой ссылке:' . "\n" . 'http://45.32.153.55/cabinet/iddqd.html?lead_id=' . $id . "&created_by=" . $r[0]['created_by']);
    //         }
    //     }
    // }

        // public function leadComplete($id)
    // {
    //     $this->amoAuth();
    //     $leads['update'] = [];

    //     $lead = [
    //         'id'         => $id,
    //         'status_id'  => 142,
    //         'updated_at' => time(),
    //     ];
    //     array_push($leads['update'], $lead);

    //     /* Теперь подготовим данные, необходимые для запроса к серверу */
    //     $subdomain = 'sparkcrm'; #Наш аккаунт - поддомен
    //     #Формируем ссылку для запроса
    //     $link = 'https://' . $subdomain . '.amocrm.ru/api/v2/leads';
    //     /* Нам необходимо инициировать запрос к серверу. Воспользуемся библиотекой cURL (поставляется в составе PHP). Подробнее о
    //     работе с этой
    //     библиотекой Вы можете прочитать в мануале. */
    //     $curl = curl_init(); #Сохраняем дескриптор сеанса cURL
    //     #Устанавливаем необходимые опции для сеанса cURL
    //     curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
    //     curl_setopt($curl, CURLOPT_USERAGENT, 'amoCRM-API-client/1.0');
    //     curl_setopt($curl, CURLOPT_URL, $link);
    //     curl_setopt($curl, CURLOPT_CUSTOMREQUEST, 'POST');
    //     curl_setopt($curl, CURLOPT_POSTFIELDS, json_encode($leads));
    //     curl_setopt($curl, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
    //     curl_setopt($curl, CURLOPT_HEADER, false);
    //     curl_setopt($curl, CURLOPT_COOKIEFILE, dirname(__FILE__) . '/cookie.txt'); #PHP>5.3.6 dirname(__FILE__) -> __DIR__
    //     curl_setopt($curl, CURLOPT_COOKIEJAR, dirname(__FILE__) . '/cookie.txt'); #PHP>5.3.6 dirname(__FILE__) -> __DIR__
    //     curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, 0);
    //     curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 0);
    //     $out  = curl_exec($curl); #Инициируем запрос к API и сохраняем ответ в переменную
    //     $code = curl_getinfo($curl, CURLINFO_HTTP_CODE);
    //     // Теперь мы можем обработать ответ, полученный от сервера. Это пример. Вы можете обработать данные своим способом.
    //     $code   = (int) $code;
    //     $errors = [
    //         301 => 'Moved permanently',
    //         400 => 'Bad request',
    //         401 => 'Unauthorized',
    //         403 => 'Forbidden',
    //         404 => 'Not found',
    //         500 => 'Internal server error',
    //         502 => 'Bad gateway',
    //         503 => 'Service unavailable',
    //     ];
    //     try
    //     {
    //         #Если код ответа не равен 200 или 204 - возвращаем сообщение об ошибке
    //         if ($code != 200 && $code != 204) {
    //             throw new Exception(isset($errors[$code]) ? $errors[$code] : 'Undescribed error', $code);
    //         }
    //     } catch (Exception $E) {
    //         die('Ошибка: ' . $E->getMessage() . PHP_EOL . 'Код ошибки: ' . $E->getCode());
    //     }
    // }

    // public function addLead1C($id)
    // {
    //     $lead = DB::table('leads_1C')
    //         ->where('order_id', $id)
    //         ->get();
    //     $author              = $lead[0]->responsible;
    //     $address_from        = $lead[0]->address_from;
    //     $sender_person       = $lead[0]->sender_person;
    //     $sender_phone        = $lead[0]->sender_phone;
    //     $sender_mobile_phone = $lead[0]->sender_mobile_phone;
    //     $full_name           = $lead[0]->client_full_name;

    //     $company = DB::table('contragents')
    //         ->where('full_name', $full_name)
    //         ->get();
    //     // var_dump($req);
    //     sleep(1);
    //     $this->amoAuth();
    //     sleep(1);
    //     $responsible_user_id;
    //     switch ($author) {
    //         case 'Дюсенова Зарина':
    //             $responsible_user_id = 2147611;
    //             break;
    //         case 'Нурканова Эльмира':
    //             $responsible_user_id = 2180614;
    //             break;
    //         case 'Дворецкий Виталий':
    //             $responsible_user_id = 2180617;
    //             break;
    //         case 'Жимбаева Эльмира':
    //             $responsible_user_id = 2180620;
    //             break;
    //         case 'Мулдагалиева Эльвира':
    //             $responsible_user_id = 2180623;
    //             break;
    //         case 'Краева Азалия':
    //             $responsible_user_id = 2180626;
    //             break;
    //         case 'Аликулова Ляззат':
    //             $responsible_user_id = 2180629;
    //             break;
    //         case 'Нурдаулетов Азамат':
    //             $responsible_user_id = 2180632;
    //             break;
    //         case 'Ахметов Мейрулан':
    //             $responsible_user_id = 2180647;
    //             break;
    //         case 'Шахиджанов Максим':
    //             $responsible_user_id = 2180650;
    //             break;
    //         default:
    //             $responsible_user_id = 1542400;
    //             break;
    //     }
    //     $leads['add'] = [
    //         [
    //             'name'                => 'Сделка #' . time() . ' 1C',
    //             'company_id'          => $company[0]->amo_id,
    //             'status_id'           => '15237505',
    //             'responsible_user_id' => $responsible_user_id,
    //             'custom_fields'       => [
    //                 [
    //                     'id'     => 447515,
    //                     'values' => [
    //                         [
    //                             'value' => $address_from,
    //                         ],
    //                     ],
    //                 ],
    //                 [
    //                     'id'     => 452897,
    //                     'values' => [
    //                         [
    //                             'value' => $sender_person,
    //                         ],
    //                     ],
    //                 ],
    //                 [
    //                     'id'     => 452899,
    //                     'values' => [
    //                         [
    //                             'value' => $sender_mobile_phone . ', ' . $sender_phone,
    //                         ],
    //                     ],
    //                 ],
    //             ],
    //         ],
    //     ];
    //     /* Теперь подготовим данные, необходимые для запроса к серверу */
    //     $subdomain = 'sparkcrm'; #Наш аккаунт - поддомен
    //     #Формируем ссылку для запроса
    //     $link = 'https://' . $subdomain . '.amocrm.ru/api/v2/leads';
    //     /* Нам необходимо инициировать запрос к серверу. Воспользуемся библиотекой cURL (поставляется в составе PHP). Подробнее о
    //     работе с этой
    //     библиотекой Вы можете прочитать в мануале. */
    //     $curl = curl_init(); #Сохраняем дескриптор сеанса cURL
    //     #Устанавливаем необходимые опции для сеанса cURL
    //     curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
    //     curl_setopt($curl, CURLOPT_USERAGENT, 'amoCRM-API-client/1.0');
    //     curl_setopt($curl, CURLOPT_URL, $link);
    //     curl_setopt($curl, CURLOPT_CUSTOMREQUEST, 'POST');
    //     curl_setopt($curl, CURLOPT_POSTFIELDS, json_encode($leads));
    //     curl_setopt($curl, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
    //     curl_setopt($curl, CURLOPT_HEADER, false);
    //     curl_setopt($curl, CURLOPT_COOKIEFILE, dirname(__FILE__) . '/cookie.txt'); #PHP>5.3.6 dirname(__FILE__) -> __DIR__
    //     curl_setopt($curl, CURLOPT_COOKIEJAR, dirname(__FILE__) . '/cookie.txt'); #PHP>5.3.6 dirname(__FILE__) -> __DIR__
    //     curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, 0);
    //     curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 0);
    //     $out  = curl_exec($curl); #Инициируем запрос к API и сохраняем ответ в переменную
    //     $code = curl_getinfo($curl, CURLINFO_HTTP_CODE);
    //     /* Теперь мы можем обработать ответ, полученный от сервера. Это пример. Вы можете обработать данные своим способом. */
    //     $code   = (int) $code;
    //     $errors = [
    //         301 => 'Moved permanently',
    //         400 => 'Bad request',
    //         401 => 'Unauthorized',
    //         403 => 'Forbidden',
    //         404 => 'Not found',
    //         500 => 'Internal server error',
    //         502 => 'Bad gateway',
    //         503 => 'Service unavailable',
    //     ];
    //     try
    //     {
    //         #Если код ответа не равен 200 или 204 - возвращаем сообщение об ошибке
    //         if ($code != 200 && $code != 204) {
    //             return json_encode("Something went wrong");
    //             throw new Exception(isset($errors[$code]) ? $errors[$code] : 'Undescribed error', $code);
    //         } else {
    //             $lead_id = json_decode($out)->_embedded->items[0]->id;
    //             $this->addNote($lead_id, 'Для отправки заказа в 1С, пройдите по этой ссылке:' . "\n" . 'http://45.32.153.55/cabinet/iddqd.html?lead_id=' . $lead_id);
    //             DB::table('leads_1C')
    //                 ->where('order_id', $id)
    //                 ->update(['lead_id' => $lead_id]);
    //             return json_encode($lead_id);
    //         }
    //     } catch (Exception $E) {
    //         die('Ошибка: ' . $E->getMessage() . PHP_EOL . 'Код ошибки: ' . $E->getCode());
    //     }
    // }

    
}
