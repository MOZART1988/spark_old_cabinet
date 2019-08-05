<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Support\Facades\DB;
use Exception;
use Artisan;

class SendCode implements ShouldQueue
{
	use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

	protected $code;
	/**
	 * Create a new job instance.
	 *
	 * @return void
	 */
	public function __construct($code)
	{
		$this->code = $code;
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
		 'USER_HASH'=>'fb6b7c9a471ceebfee63b325bb35f39ad6477944' #Хэш для доступа к API (смотрите в профиле пользователя)
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
		print_r('Ошибка: '.$E->getMessage().PHP_EOL.'Код ошибки: '.$E->getCode());
		}
		/*
		Данные получаем в формате JSON, поэтому, для получения читаемых данных,
		нам придётся перевести ответ в формат, понятный PHP
		*/
		$Response=json_decode($out,true);
		$Response=$Response['response'];

		sleep(1);

		$lead=DB::table('leads_1C')
			->where('order_id', $this->code)
			->get();		
		$address_from = $lead[0]->address_from;
		$sender_person = $lead[0]->sender_person;
		$sender_phone = $lead[0]->sender_phone;
		$sender_mobile_phone = $lead[0]->sender_mobile_phone;
		$full_name = $lead[0]->client_full_name;

		$company=DB::table('contragents')
			->where('full_name', $full_name)
			->get();
		// var_dump($req);
		$responsible_user_id;
		$author = $company[0]->manager;
		switch ($author) {
			case 'Ахметов Мейрулан':
		        $responsible_user_id=2180647;
		        break;
			case 'Дюсенова Зарина':
				$responsible_user_id=2147611;
				break;
			case 'Нурканова Эльмира':
				$responsible_user_id=2180614;
				break;
			case 'Дворецкий Виталий':
				$responsible_user_id=2180617;
				break;
			case 'Оспанова Анар':
				$responsible_user_id=2180620;
				break;
			case 'Мулдагалиева Эльвира':
				$responsible_user_id=2180623;
				break;
			case 'Краева Азалия':
				$responsible_user_id=2180626;
				break;
			case 'Оразгалиева Жанна':
				$responsible_user_id=2916334;
				break;
			case 'Зарипова Рита':
				$responsible_user_id=2659438;
				break;	
			case 'Аликулова Ляззат':
				$responsible_user_id=2180629;
				break;
			case 'Нурдаулетов Азамат':
				$responsible_user_id=2180632;
				break;
			case 'Ахметов Мейрулан':
				$responsible_user_id=2180647;
				break;
			case 'Шахиджанов Максим':
				$responsible_user_id=2180650;
				break;
			case 'Ескалиева Акбота':
				$responsible_user_id = 3000820;
				break;
			case 'Туктубаева Динара':
				$responsible_user_id= 3000790;
				break;
			default:
				$responsible_user_id=1542400;
				break;
		}
		$leads['add']=array(
			array(
				'name'                  =>  'Сделка #'.time().' 1C',
				'company_id'            =>  $company[0]->amo_id,
				'status_id'             =>  '15237505',
				'responsible_user_id'   =>  $responsible_user_id,
				'custom_fields'         =>  array(
					array(
						'id'     => 447515,
						'values' => array(
							array(
								'value'   => $address_from
							)
						)
					),
					array(
						'id'     => 452897,
						'values' => array(
							array(
								'value'   => $sender_person
							)
						)
					),
					array(
						'id'     => 452899,
						'values' => array(
							array(
								'value'   => $sender_mobile_phone.', '.$sender_phone
							)
						),
					),
				) 
			)
		);
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
			return json_encode("Something went wrong");
			throw new Exception(isset($errors[$code]) ? $errors[$code] : 'Undescribed error',$code);
		  }
		  else{
			$lead_id=json_decode($out)->_embedded->items[0]->id;
			$noteText='Теперь Вы можете заполнить и отправить заказ в 1с используя виджет, что расположен правее.'."\n".'При возникновении сбоев, используйте данную ссылку:'."\n".'https://cabinet.spark-logistics.kz/iddqd.html?lead_id='.$lead_id."&created_by=".$responsible_user_id;		
			$job = (new SendNote($lead_id,$noteText))->onQueue('links');

			dispatch($job);
			DB::table('leads_1C')
				->where('order_id', $this->code)
				->update(['lead_id' => $lead_id]);
			$lead =	DB::table('leads_1C')
					->where('order_id', $this->code)
					->get();
			print_r($lead);
			if ($lead[0]->responsible == 'Диспетчер') {
				Artisan::call('relog:pickUp', [
					'lead' => $lead_id
				]);
			}
			return json_encode($lead_id);
		  }
		}
		catch(Exception $E)
		{
		  print_r('Ошибка: '.$E->getMessage().PHP_EOL.'Код ошибки: '.$E->getCode());
		}

		sleep(1);
	}
}
