<?php

namespace App\Http\Controllers;

use App\Jobs\SendCourier;
use App\Jobs\SendLeadToStatus;
use App\Jobs\SendOrder;
use App\Jobs\sendPriceAndClose;
use Carbon\Carbon;
use DateTime;
use Illuminate\Support\Facades\DB;
use Exception;
use App\Lead;

class testController extends Controller
{   
    public function updateSenderTemplate($id)
    {
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
    }

    // public function conts()
    // {
    //     return DB::table('contragents')
    //         ->where('password', '')
    //         ->select('name')
    //         ->get(); 
    // }

    // public function contsSent()
    // {
    //     $req     = file_get_contents('php://input');
    //     $req=json_decode($req);
    //     for ($i=0; $i < count($req); $i++) { 
    //        DB::table('contragents')
    //         ->where('name', $req[$i]->name)
    //         ->update([
    //             'password'  => $req[$i]->hash
    //         ]);            
    //     }
    //     file_put_contents('/var/www/html/API/passwords.json', json_encode($req, JSON_UNESCAPED_UNICODE));   
    // }

    public function updateRecipientTemplate($id)
    {
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
    }

    public function image()
    {
        $pic = $_FILES['file'];
        $upload_dir = '/var/www/html/cabinet/img/photos/'; //Создадим папку для хранения изображений
                
        $urls = [];
        for ($i=0; $i < count($pic['tmp_name']); $i++) { 
            $temp = explode(".",$pic['name'][$i]);
            $newfilename = Carbon::now()->timestamp. 'T' . md5($pic['name'][$i].Carbon::now()->timestamp) . '.' .end($temp);
            move_uploaded_file($pic['tmp_name'][$i], $upload_dir.$newfilename);
            $urls[$i]='https://cabinet.spark-logistics.kz/img/photos/'.$newfilename;
        }
        // print_r('<pre>');
        print_r(json_encode($urls));
    }

    public function getDirections()
    {
        $headers = apache_request_headers();
        $allDirections = DB::table('directions')
            ->select('directions.direction')
            ->get();
        $directions = [];
        for ($i = 0; $i < count($allDirections); $i++) {
            // print_r($allDirections[$i]->direction."\n");
            array_push($directions, explode(" - ", $allDirections[$i]->direction)[0]);
            if (array_key_exists(1, explode(" - ", $allDirections[$i]->direction))) {
                array_push($directions, explode(" - ", $allDirections[$i]->direction)[1]);
            }            
        }
        $res2 = array(); 
        foreach($directions as $key=>$val) {    
            $res2[$val] = true; 
        } 
        $res2 = array_keys($res2);
        print_r(json_encode($res2));
    }

    public function sendCompleteWithPrice()
    {
        $a = json_decode(file_get_contents('/var/www/html/API/complete_with_price.json'));
        $b=[];
        for ($i=0; $i < 500; $i++) { 
            $b[$i]=$a[$i];
        }
        
        $job = (new sendPriceAndClose($b))->onQueue('prc');
        dispatch($job);
        for ($i=500; $i < count($a); $i++) { 
            $b[$i-500]=$a[$i];
        }
        $job = (new sendPriceAndClose($b))->onQueue('prc');
        dispatch($job);
        for ($i=1000; $i < count($a); $i++) { 
            $b[$i-1000]=$a[$i];
        }
        $job = (new sendPriceAndClose($b))->onQueue('prc');
        dispatch($job);
        for ($i=1500; $i < count($a); $i++) { 
            $b[$i-1500]=$a[$i];
        }
        $job = (new sendPriceAndClose($b))->onQueue('prc');
        dispatch($job);
        for ($i=2000; $i < count($a); $i++) { 
            $b[$i-2000]=$a[$i];
        }
        $job = (new sendPriceAndClose($b))->onQueue('prc');
        dispatch($job);
        for ($i=2500; $i < count($a); $i++) { 
            $b[$i-2500]=$a[$i];
        }
        $job = (new sendPriceAndClose($b))->onQueue('prc');
        dispatch($job);
        for ($i=2500; $i < count($a); $i++) { 
            $b[$i-2500]=$a[$i];
        }
        $job = (new sendPriceAndClose($b))->onQueue('prc');
        dispatch($job);
        for ($i=3000; $i < count($a); $i++) { 
            $b[$i-3000]=$a[$i];
        }
        $job = (new sendPriceAndClose($b))->onQueue('prc');
        dispatch($job);
        for ($i=3500; $i < count($a); $i++) { 
            $b[$i-3500]=$a[$i];
        }
        $job = (new sendPriceAndClose($b))->onQueue('prc');
        dispatch($job);
        for ($i=4000; $i < count($a); $i++) { 
            $b[$i-4000]=$a[$i];
        }
        $job = (new sendPriceAndClose($b))->onQueue('prc');
        dispatch($job);
        for ($i=4500; $i < count($a); $i++) { 
            $b[$i-4500]=$a[$i];
        }
        $job = (new sendPriceAndClose($b))->onQueue('prc');
        dispatch($job);
        for ($i=5000; $i < count($a); $i++) { 
            $b[$i-5000]=$a[$i];
        }
        $job = (new sendPriceAndClose($b))->onQueue('prc');
        dispatch($job);
        for ($i=5500; $i < count($a); $i++) { 
            $b[$i-5500]=$a[$i];
        }
        $job = (new sendPriceAndClose($b))->onQueue('prc');
        dispatch($job);
        for ($i=6000; $i < count($a); $i++) { 
            $b[$i-6000]=$a[$i];
        }
        $job = (new sendPriceAndClose($b))->onQueue('prc');
        dispatch($job);
        for ($i=6500; $i < count($a); $i++) { 
            $b[$i-6500]=$a[$i];
        }
        $job = (new sendPriceAndClose($b))->onQueue('prc');
        dispatch($job);
        // print_r('<pre>');
        // print_r($a);
    }

    public function testCreate()
    {
        $user=array(
         'USER_LOGIN'=>'crm@spark-logistics.com', #Ваш логин (электронная почта)
         'USER_HASH'=>'4d455c00920812341e5ccbaa412624fb' #Хэш для доступа к API (смотрите в профиле пользователя)
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

        $leads['update'] = [
                [
                    "id"=> "11615457",
                    "created_at"=> Carbon::now()->year(2014)->month(5)->day(21)->hour(22)->minute(32)->second(5)->timestamp,
                    "updated_at"=> time(),
                    "sale"=> "5000",
                    "custom_fields"=> []
                ]
            ];
        /* Теперь подготовим данные, необходимые для запроса к серверу */
        $subdomain='sparkcrm'; #Наш аккаунт - поддомен
        #Формируем ссылку для запроса
        $link='https://'.$subdomain.'.amocrm.ru/api/v2/leads';
        /* Нам необходимо инициировать запрос к серверу. Воспользуемся библиотекой cURL (поставляется в составе PHP). Подробнее о
        работе с этой
        библиотекой Вы можете прочитать в мануале. */
        $curl=curl_init(); #Сохраняем дескриптор сеанса cURL
        #Устанавливаем необходимые опции для сеанса cURL
        curl_setopt($curl,CURLOPT_RETURNTRANSFER,true);
        curl_setopt($curl,CURLOPT_USERAGENT,'amoCRM-API-client/1.0');
        curl_setopt($curl,CURLOPT_URL,$link);
        curl_setopt($curl,CURLOPT_CUSTOMREQUEST,'POST');
        curl_setopt($curl,CURLOPT_POSTFIELDS,json_encode($leads));
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
            if($code!=200 && $code!=204) {
                throw new Exception(isset($errors[$code]) ? $errors[$code] : 'Undescribed error',$code);
            }
            else{
 
            }
        }
        catch(Exception $E)
        {
          echo('Ошибка: '.$E->getMessage().PHP_EOL.'Код ошибки: '.$E->getCode());
        }
    }

    public function addWaybills($waybill)
    {
        $fields['add'] = array(
           [
              'name' => "Накладная",
              'field_type'=> 1,
              'element_type' => 2,
              'origin' => md5($waybill+time()),
              'is_editable' => 0,
              "values"=> [
                    [
                        "value"=>$waybill //Поле типа текст
                    ]
                ]
           ]
        );
       $subdomain='sparkcrm'; #Наш аккаунт - поддомен
        #Формируем ссылку для запроса
        $link='https://'.$subdomain.'.amocrm.ru/api/v2/leads';
        /* Нам необходимо инициировать запрос к серверу. Воспользуемся библиотекой cURL (поставляется в составе PHP). Подробнее о
        работе с этой
        библиотекой Вы можете прочитать в мануале. */
        $curl=curl_init(); #Сохраняем дескриптор сеанса cURL
        #Устанавливаем необходимые опции для сеанса cURL
        curl_setopt($curl,CURLOPT_RETURNTRANSFER,true);
        curl_setopt($curl,CURLOPT_USERAGENT,'amoCRM-API-client/1.0');
        curl_setopt($curl,CURLOPT_URL,$link);
        curl_setopt($curl,CURLOPT_CUSTOMREQUEST,'POST');
        curl_setopt($curl,CURLOPT_POSTFIELDS,json_encode($leads));
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
            if($code!=200 && $code!=204) {
                throw new Exception(isset($errors[$code]) ? $errors[$code] : 'Undescribed error',$code);
            }
            else{
 
            }
        }
        catch(Exception $E)
        {
          echo('Ошибка: '.$E->getMessage().PHP_EOL.'Код ошибки: '.$E->getCode());
        }
    }

    public function leadTest()
    {
        $req     = file_get_contents('php://input');
        $req     = json_decode($req);
        print_r('<pre>');
        // print_r($req);
        print_r($req[0]);
    }

    public function telegram()
    {
        Telegram::sendMessage('default', 'Here we go!');
    }

    public function companies()
    {
        // print_r('<pre>');
        $companies = json_decode(file_get_contents('/var/www/html/API/contragents.json'));
        // print_r($companies);
        $data = array (
          'add' => 
                array (
                    
                ),
        );
        for ($i=2350; $i < 2691; $i++) { 
                $j = array (
                      'name' => $companies[$i]->name,
                      'responsible_user_id'=> "1542400",
                      'created_by'=> "1542400",
                      'request_id' => trim($companies[$i]->id),
                      'custom_fields' => 
                      array (
                        0 => 
                        array (
                          'id' => '80545',
                          'values' => 
                          array (
                            0 => 
                            array (
                              'value' => $companies[$i]->work_phone,
                              'enum' => 'WORK',
                            ),
                          ),
                        ),
                        1 => 
                        array (
                          'id' => '80547',
                          'values' => 
                          array (
                            0 => 
                            array (
                              'value' => $companies[$i]->email,
                              'enum' => 'WORK',
                            ),
                          ),
                        ),
                        2 => 
                        array (
                          'id' => '80553',
                          'values' => 
                          array (
                            0 => 
                            array (
                              'value' => $companies[$i]->fact_address,
                            ),
                          ),
                        ),
                        3 => 
                        array (
                          'id' => '134605',
                          'values' => 
                          array (
                            0 => 
                            array (
                              'value' => $companies[$i]->full_name,
                            ),
                          ),
                        ),
                        4 => 
                        array (
                          'id' => '134607',
                          'values' => 
                          array (
                            0 => 
                            array (
                              'value' => $companies[$i]->nds_number,
                            ),
                          ),
                        ),
                        5 => 
                        array (
                          'id' => '134611',
                          'values' => 
                          array (
                            0 => 
                            array (
                              'value' =>  $companies[$i]->bank_account,
                            ),
                          ),
                        ),
                        6 => 
                        array (
                          'id' => '134613',
                          'values' => 
                          array (
                            0 => 
                            array (
                              'value' => $companies[$i]->contract,
                            ),
                          ),
                        ),
                        7 => 
                        array (
                          'id' => '447535',
                          'values' => 
                          array (
                            0 => 
                            array (
                              'value' => trim($companies[$i]->id),
                            ),
                          ),
                        ),
                        8 => 
                        array (
                          'id' => '475035',
                          'values' => 
                          array (
                            0 => 
                            array (
                              'value' => $companies[$i]->jur_address,
                            ),
                          ),
                        ),
                        9 => 
                        array (
                          'id' => '475173',
                          'values' => 
                          array (
                            0 => 
                            array (
                              'value' =>  $companies[$i]->juridical ? '982989' : '982991',
                            ),
                          ),
                        ),
                        10 => 
                        array (
                          'id' => '475171',
                          'values' => 
                          array (
                            0 => 
                            array (
                              'value' => date("Y-m-d",strtotime($companies[$i]->nds_date))
                            ),
                          ),
                        ),
                      ),
                    );
           array_push($data['add'], $j);
        }
        $data_chunked = array_chunk($data['add'], 400);

        print_r('<pre>');
        // print_r($data_chunked);
        // print_r($data);
        $user=array(
         'USER_LOGIN'=>'crm@spark-logistics.com', #Ваш логин (электронная почта)
         'USER_HASH'=>'349f37c5a3675ee42e489b0cde2e492bfcd0917d' #Хэш для доступа к API (смотрите в профиле пользователя)
        );
        $subdomain='sparkcrm'; #Наш аккаунт - поддомен
        #Формируем ссылку для запроса
        $link='https://'.$subdomain.'.amocrm.ru/private/api/auth.php?type=json';
         // Нам необходимо инициировать запрос к серверу. Воспользуемся библиотекой cURL (поставляется в составе PHP). Вы также
        // можете
        // использовать и кроссплатформенную программу cURL, если вы не программируете на PHP. 
        $curl=curl_init(); #Сохраняем дескриптор сеанса cURL
        // #Устанавливаем необходимые опции для сеанса cURL
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
        //  // Теперь мы можем обработать ответ, полученный от сервера. Это пример. Вы можете обработать данные своим способом. 
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
        // #Если код ответа не равен 200 или 204 - возвращаем сообщение об ошибке
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
        
        $link = "https://sparkcrm.amocrm.ru/api/v2/companies";

        $headers[] = "Accept: application/json";

        // print_r($data_chunked);

        for ($i=0; $i < count($data_chunked); $i++) { 
            //Curl options
            $send['add'] = $data_chunked[$i];
            // print_r($send);
            $curl = curl_init();
            curl_setopt($curl, CURLOPT_RETURNTRANSFER,true);
            curl_setopt($curl, CURLOPT_USERAGENT, "amoCRM-API-client-
            undefined/2.0");
            curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
            curl_setopt($curl, CURLOPT_POSTFIELDS, http_build_query($send));
            curl_setopt($curl, CURLOPT_URL, $link);
            curl_setopt($curl, CURLOPT_HEADER,false);
            curl_setopt($curl,CURLOPT_COOKIEFILE,dirname(__FILE__)."/cookie.txt");
            curl_setopt($curl,CURLOPT_COOKIEJAR,dirname(__FILE__)."/cookie.txt");
            $out = curl_exec($curl);
            curl_close($curl);
            $result = json_decode($out,TRUE);
            // print_r($result);
            for ($i=0; $i < count($result["_embedded"]["items"]); $i++) {
                DB::table('contragents')
                    ->where('id', 'like', "%".$result["_embedded"]["items"][$i]["request_id"]."%")
                    ->update(['amo_id' => $result["_embedded"]["items"][$i]["id"]]);
            }
            print_r($result["_embedded"]);
            sleep(1);
        }
    }
}

