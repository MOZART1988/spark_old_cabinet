<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Support\Facades\DB;

class GetAmoID implements ShouldQueue
{
	use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

	/**
	 * Create a new job instance.
	 *
	 * @return void
	 */

	public function __construct()
	{

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

		for ($i=0; $i < 5000; $i+=500) {
			/* Для начала нам необходимо инициализировать данные, необходимые для составления запроса. */
			$subdomain='sparkcrm'; #Наш аккаунт - поддомен
			#Формируем ссылку для запроса
			$link='https://'.$subdomain.'.amocrm.ru/api/v2/companies';
			/* Заметим, что в ссылке можно передавать и другие параметры, которые влияют на выходной результат (смотрите
			документацию).
			Следовательно, мы можем заменить ссылку, приведённую выше на одну из следующих, либо скомбинировать параметры так, как Вам
			необходимо. */
			$link='https://'.$subdomain.'.amocrm.ru/api/v2/companies?limit_rows=500&limit_offset='.$i;
			/* Нам необходимо инициировать запрос к серверу. Воспользуемся библиотекой cURL (поставляется в составе PHP). Подробнее о
			работе с этой
			библиотекой Вы можете прочитать в мануале. */
			$curl=curl_init(); #Сохраняем дескриптор сеанса cURL
			#Устанавливаем необходимые опции для сеанса cURL
			curl_setopt($curl,CURLOPT_RETURNTRANSFER,true);
			curl_setopt($curl,CURLOPT_USERAGENT,'amoCRM-API-client/1.0');
			curl_setopt($curl,CURLOPT_URL,$link);
			curl_setopt($curl,CURLOPT_HEADER,false);
			curl_setopt($curl,CURLOPT_COOKIEFILE,dirname(__FILE__).'/cookie.txt'); #PHP>5.3.6 dirname(__FILE__) -> __DIR__
			curl_setopt($curl,CURLOPT_COOKIEJAR,dirname(__FILE__).'/cookie.txt'); #PHP>5.3.6 dirname(__FILE__) -> __DIR__
			curl_setopt($curl,CURLOPT_SSL_VERIFYPEER,0);
			curl_setopt($curl,CURLOPT_SSL_VERIFYHOST,0);
			$out=curl_exec($curl); #Инициируем запрос к API и сохраняем ответ в переменную
			$code=curl_getinfo($curl,CURLINFO_HTTP_CODE);
			curl_close($curl);
			/* Вы также можете передать дополнительный HTTP-заголовок IF-MODIFIED-SINCE, в котором указывается дата в формате D, d M Y
			H:i:s. При
			передаче этого заголовка будут возвращены контакты, изменённые позже этой даты. */
			// curl_setopt($curl,CURLOPT_HTTPHEADER,array('IF-MODIFIED-SINCE: Mon, 01 Aug 2013 07:07:23'));
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
			$Response=$Response['_embedded']['items'];
			for ($j=0; $j < count($Response); $j++) {
				if(isset($Response[$j])){
					if(isset($Response[$j]['custom_fields'])){
						if(isset($Response[$j]['custom_fields'][0])){
							if(isset($Response[$j]['custom_fields'][0]['values'])){
								if(isset($Response[$j]['custom_fields'][0]['values'][0])){
									if(isset($Response[$j]['custom_fields'][0]['values'][0]['value'])){
										DB::table('contragents')
											->where('full_name', $Response[$j]['custom_fields'][0]['values'][0]['value'])
											->update(['amo_id' => $Response[$j]['id']]);
									}
								}
							}
						}
					}
				}				
			}			
			sleep(1);
		}
	}
}