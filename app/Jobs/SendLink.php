<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Exception;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class SendLink implements ShouldQueue
{
	use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

	protected $l_id;
	/**
	 * Create a new job instance.
	 *
	 * @return void
	 */
	public function __construct($l_id)
	{
		$this->l_id=$l_id;
	}

	/**
	 * Execute the job.
	 *
	 * @return void
	 */
	public function handle()
	{   
		#Массив с параметрами, которые нужно передать методом POST к API системы
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
		
		$leadId = $this->l_id;

		$subdomain='sparkcrm'; 
		// $link='https://'.$subdomain.'.amocrm.ru/api/v2/leads';
// https://sparkcrm.amocrm.ru/api/v2/leads?&status[1]=15272152&status[2]=15327316&status[3]=18394894&status[4]=18394897&status[5]=18394900&status[6]=18394903&status[7]=18394906&status[8]=18394909&status[9]=18394912&status[10]=18394915&status[11]=18519910

		$link='https://'.$subdomain.'.amocrm.ru/api/v2/leads?id='.$leadId;
		
		$curl=curl_init();
		curl_setopt($curl,CURLOPT_RETURNTRANSFER,true);
		curl_setopt($curl,CURLOPT_USERAGENT,'amoCRM-API-client/1.0');
		curl_setopt($curl,CURLOPT_URL,$link);
		curl_setopt($curl,CURLOPT_HEADER,false);
		curl_setopt($curl,CURLOPT_COOKIEFILE,dirname(__FILE__).'/cookie.txt'); #PHP>5.3.6 dirname(__FILE__) -> __DIR__
		curl_setopt($curl,CURLOPT_COOKIEJAR,dirname(__FILE__).'/cookie.txt'); #PHP>5.3.6 dirname(__FILE__) -> __DIR__
		curl_setopt($curl,CURLOPT_SSL_VERIFYPEER,0);
		curl_setopt($curl,CURLOPT_SSL_VERIFYHOST,0);
		curl_setopt($curl,CURLOPT_HTTPHEADER,array('IF-MODIFIED-SINCE: Mon, 01 Aug 2013 07:07:23'));

		$out=curl_exec($curl); #Инициируем запрос к API и сохраняем ответ в переменную
		$code=curl_getinfo($curl,CURLINFO_HTTP_CODE);
		curl_close($curl);
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
		  /* Если код ответа не равен 200 или 204 - возвращаем сообщение об ошибке */
		  if($code!=200 && $code!=204) {
		    throw new Exception(isset($errors[$code]) ? $errors[$code] : 'Undescribed error',$code);
		  }
		}
		catch(Exception $E)
		{
			Log::error('Ошибка: '.$E->getMessage().PHP_EOL.'Код ошибки: '.$E->getCode().' ');
		  die();
		}
		/*
		 Данные получаем в формате JSON, поэтому, для получения читаемых данных,
		 нам придётся перевести ответ в формат, понятный PHP
		 */
		$r=json_decode($out,true);

		$r=$r['_embedded']['items'];
		$company=DB::table('contragents')
			->where('amo_id', $r[0]['company']['id'])
			->get();
		$lead['client_full_name']=$company[0]->full_name;
		$lead['sender_company_name']=$company[0]->full_name;
		$templateID='';
		for ($i=0; $i < count($r[0]['custom_fields']); $i++) { 
			if($r[0]['custom_fields'][$i]['id']=='447515'){
				$lead['address_from']=$r[0]['custom_fields'][$i]['values'][0]['value'];
			}
			if($r[0]['custom_fields'][$i]['id']=='465051'){
				$templateID=$r[0]['custom_fields'][$i]['values'][0]['value'];
			}
			if($r[0]['custom_fields'][$i]['id']=='452899'){
				$lead['sender_mobile_phone']=$r[0]['custom_fields'][$i]['values'][0]['value'];
			}
			if($r[0]['custom_fields'][$i]['id']=='447513'){
				$lead['filial']=$r[0]['custom_fields'][$i]['values'][0]['value'];
			}
			if($r[0]['custom_fields'][$i]['id']=='447519'){
				$lead['sender_comments']=$r[0]['custom_fields'][$i]['values'][0]['value'];
			}
			if($r[0]['custom_fields'][$i]['id']=='447517'){
				$lead['date_from']=$r[0]['custom_fields'][$i]['values'][0]['value'];
			}
			if($r[0]['custom_fields'][$i]['id']=='447525'){
				$lead['delivery_type']=$r[0]['custom_fields'][$i]['values'][0]['value'];
			}
			if($r[0]['custom_fields'][$i]['id']=='452897'){
				$lead['sender_person']=$r[0]['custom_fields'][$i]['values'][0]['value'];
			}
			if($r[0]['custom_fields'][$i]['id']=='447521'){
				$lead['cargo_ready_time']=$r[0]['custom_fields'][$i]['values'][0]['value'];
			}
			switch ($r[0]['responsible_user_id']) {
				case '2147611':
					$lead['author']='Дюсенова Зарина';
					break;
				case '2180614':
					$lead['author']='Нурканова Эльмира';
					break;
				case '2180617':
					$lead['author']='Дворецкий Виталий';
					break;
				case '2180620':
					$lead['author']='Оспанова Анар';
					break;
				case '2180665':
					$lead['author']='Бархатова Карина';
					break;
				case '2180626':
					$lead['author']='Краева Азалия';
					break;
				case '2180629':
					$lead['author']='Аликулова Ляззат';
					break;
				case '2180632':
					$lead['author']='Азамат Нурдаулетов';
					break;
				case '2180647':
					$lead['author']='Ахметов Мейрулан';
					break;
				case '2180650':
					$lead['author']='Шахиджанов Максим';
					break;
				case '2659438':
					$lead['author']='Зарипова Рита';
					break;
				case '2916334':
					$lead['author']='Оразгалиева Жанна';
					break;
				case '2950831':
					$lead['author']='Мадемиева Жанар';
					break;
				case '3000790':
					$lead['author']='Туктубаева Динара';
					break;
				case '3000820':
					$lead['author']='Ескалиева Акбота';
					break;
				case '3027214':
					$lead['author']='Кабылбек Саят';
					break;
				case '3064471':
					$lead['author']='Мусина Роза';
					break;
				default:
					$lead['author']='Надежук Олег';
					break;
			}			
		}
		$lead_existing=DB::table('leads_new')
			->where('lead_id', $leadId)
			->get();
		if($lead_existing->isEmpty()){
			$lead['lead_id']=$leadId;
			DB::table('leads_new')
				->insert($lead);
		}
		else{			
			DB::table('leads_new')
				->where('lead_id', $lead_existing[0]->lead_id)
				->update($lead);
		}

		sleep(1);
		if(strlen($templateID)>0){
			$noteText='Теперь Вы можете заполнить и отправить заказ в 1с используя виджет, что расположен правее.'."\n".'При возникновении сбоев, используйте данную ссылку:'."\n".'https://cabinet.spark-logistics.kz/iddqd.html?lead_id='.$leadId."&created_by=".$r[0]['created_by']."&template_id=".$templateID;
		}
		else{
			$noteText='Теперь Вы можете заполнить и отправить заказ в 1с используя виджет, что расположен правее.'."\n".'При возникновении сбоев, используйте данную ссылку:'."\n".'https://cabinet.spark-logistics.kz/iddqd.html?lead_id='.$leadId."&created_by=".$r[0]['created_by'];
		}
		

		$data = array (
         'add' =>
         array (
             0 =>
                 array (
                     'element_id' => $leadId,
                     'element_type' => '2',                            
                     'text' => $noteText,
                     'note_type' => '4',
                     'created_at' => time(),
                     'responsible_user_id' => '1542400',
                     'created_by' => '1542400',
                 ),
         ),
        );
        $subdomain='sparkcrm'; #Наш аккаунт - поддомен
        $link='https://'.$subdomain.'.amocrm.ru/api/v2/notes';
        /* Нам необходимо инициировать запрос к серверу. Воспользуемся библиотекой cURL (поставляется в составе PHP). Подробнее о
        работе с этой
        библиотекой Вы можете прочитать в мануале. */
        $curl=curl_init(); #Сохраняем дескриптор сеанса cURL
        #Устанавливаем необходимые опции для сеанса cURL
        curl_setopt($curl,CURLOPT_RETURNTRANSFER,true);
        curl_setopt($curl,CURLOPT_USERAGENT,'amoCRM-API-client/1.0');
        curl_setopt($curl,CURLOPT_URL,$link);
        curl_setopt($curl,CURLOPT_CUSTOMREQUEST,'POST');
        curl_setopt($curl,CURLOPT_POSTFIELDS,json_encode($data));
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
        if($code!=200 && $code!=204)
         throw new Exception(isset($errors[$code]) ? $errors[$code] : 'Undescribed error',$code);
        }
        catch(Exception $E)
        {
            die('Ошибка: '.$E->getMessage().PHP_EOL.'Код ошибки: '.$E->getCode());
        }
        sleep(1);
	}
}
