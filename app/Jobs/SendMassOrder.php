<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Exception;
use App\Mail\newOrder;

class SendMassOrder implements ShouldQueue
{
	use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

	protected $leads;
	protected $order;
	protected $sender;
	/**
	 * Create a new job instance.
	 *
	 * @return void
	 */

	public function __construct($leads,$order,$sender)
	{
		$this->leads = $leads;
		$this->order = $order;
		$this->sender = $sender;
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
		curl_setopt($curl,CURLOPT_POSTFIELDS,json_encode($this->leads));
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
		print_r($out);
		print_r("\n");
		print_r($code);
		try
		{
		  #Если код ответа не равен 200 или 204 - возвращаем сообщение об ошибке
			if($code!=200 && $code!=204) {
				throw new Exception(isset($errors[$code]) ? $errors[$code] : 'Undescribed error',$code);
			}
			else{
				print_r($this->order->waybills[0]["placesAmount"]);
				// Mail::to(\Config::get('managers.manager_emails.'.$this->leads['add'][0]['responsible_user_id']))
    //         		->send(new newOrder());
				Mail::to('voichenko.j@spark-logistics.com')
            		->send(new newOrder());

            	DB::table("leads_new")
            		->insert([
            			"lead_id" 				=> json_decode($out)->_embedded->items[0]->id,
            			"address_from"			=> $this->order->senderAddress,
            			"country"				=> $this->order->country,
            			"region"				=> $this->order->region,	
						"city"					=> $this->order->city,
						"street"				=> $this->order->street,
						"building"				=> $this->order->building,
						"office"				=> $this->order->office,
						"sender_person"			=> $this->order->senderContactPerson,			
						"sender_phone"			=> $this->order->senderContactPhone,			
						"date_from"				=> $this->order->date,
						"time_from"				=> $this->order->time,
						"sender_company_name"	=> $this->sender->full_name,
						"sender_company_bin"	=> $this->sender->id,
						"client_full_name"		=> $this->sender->full_name,
						"responsible"			=> $this->sender->manager,
						"delivery_type"			=> 'B2C',
						"author"				=> 'Интернет-заказ'
            		]);
            	for ($i=0; $i < count($this->order->waybills); $i++) { 
            		DB::table("orders_new")
	            		->insert([
	            			"order_id"				=>	$i,
	            			"lead_id" 				=>	json_decode($out)->_embedded->items[0]->id,
	            			"direction"				=>	'Интернет - Интернет',
	            			"code"					=>	$this->order->waybills[$i]["recipient_code"],
							"recipient_address"		=>	$this->order->waybills[$i]["recipientAddress"],
							"city"					=>	$this->order->waybills[$i]["city"],
							"region"				=>	$this->order->waybills[$i]["region"],
							"street"				=>	$this->order->waybills[$i]["street"],
							"building"				=>	$this->order->waybills[$i]["building"],
							"office"				=>	$this->order->waybills[$i]["office"],
							"recipient_contact_person"=>$this->order->waybills[$i]["recipientContactPerson"],
							"recipient_phone"		=>	$this->order->waybills[$i]["recipientContactPhone"],
							"delivery_type_big"		=>	$this->order->waybills[$i]["deliveryType"],
							"places"				=>	$this->order->waybills[$i]["placesAmount"],
							"weight"				=>	$this->order->waybills[$i]["weight"],
							"volume_weight"			=>	$this->order->waybills[$i]["volume"],
							"payment_type"			=>	$this->order->waybills[$i]["payment"],
							"payer"					=>	$this->order->waybills[$i]["payer"],
							"overcharged_payment"	=>	$this->order->waybills[$i]["overPayment"],
							"cargo_value"			=>	$this->order->waybills[$i]["sum"],
							"comment"				=>	$this->order->waybills[$i]["comment"],
	            		]);
            	}
			}
		}
		catch(Exception $E)
		{
		  echo('Ошибка: '.$E->getMessage().PHP_EOL.'Код ошибки: '.$E->getCode());
		}

		sleep(1);
	}
}