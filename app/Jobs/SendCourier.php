<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use App\Mail\newOrder;

class SendCourier implements ShouldQueue
{
	use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

	protected $id;
	protected $contactPerson;
	protected $contactPhone;
	protected $amo_id;
	protected $leads;
	/**
	 * Create a new job instance.
	 *
	 * @return void
	 */
	public function __construct($id, $amo_id, $contactPerson, $contactPhone, $leads)
	{
			$this->id=$id;
			$this->amo_id=$amo_id;
			$this->contactPerson=$contactPerson;
			$this->contactPhone=$contactPhone;
			$this->leads=$leads;
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
		try
		{
		  #Если код ответа не равен 200 или 204 - возвращаем сообщение об ошибке
		 if($code!=200 && $code!=204) {
			throw new Exception(isset($errors[$code]) ? $errors[$code] : 'Undescribed error',$code);
		  }
		  else{
			sleep(1);
			$noteText='Вызов курьера'."\nКонтактное лицо:".print_r($this->contactPerson, true)."\nКонтактный телефон:".print_r($this->contactPhone, true);
			$job = (new SendNote(json_decode($out)->_embedded->items[0]->id, $noteText))->onQueue('notes');
			dispatch($job);
			DB::table('notifications')
			->insert([
				'userid'    =>  $this->id,
				'text'      =>  'Заявка на вызов курьера принята. В ближайшее время с Вами свяжется ответственный менеджер.'
			]);
			Mail::to(\Config::get('managers.manager_emails.'.$this->leads['add'][0]['responsible_user_id']))
            		->send(new newOrder());
		  }
		}
		catch(Exception $E)
		{
		  die('Ошибка: '.$E->getMessage().PHP_EOL.'Код ошибки: '.$E->getCode());
		}

		sleep(1);
	}
}
