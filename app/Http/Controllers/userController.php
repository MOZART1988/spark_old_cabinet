<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Exception;
use Illuminate\Support\Facades\Mail;
use App\Mail\newUser;
use App\ContragentTemp;
use App\Contragent1С;
use App\ContragentUser;

class userController extends Controller
{
	public function logout(){
		$headers = apache_request_headers();
		$token=$headers['token'];
		DB::table('tokens')
			->where('token', $token)
			->update(['token' => null, 'creationTime' => null]);
	}

	public function login(){
		$user=new \stdClass();
		$headers = apache_request_headers();
		$id=$headers['id'];
		$passwordHash=$headers['passwordHash'];
		$user=DB::table('contragents')
				->join('cabinet_users', 'contragents.id', '=', 'cabinet_users.id')
				->where('contragents.id',$id)
				->where('contragents.password',$passwordHash)
				->select('contragents.name', 'contragents.juridical', 'contragents.full_name', 'intro_main', 'intro_order', 'intro_tracking', 'intro_reports')
				->get();
		//print_r($user);
		if (sizeof($user)>0){
			$token=$this->checkToken($id);
			if($token){
				print_r(json_encode([$user[0]->name, $token, $user[0]->juridical, $user[0]->full_name, $user[0]->intro_main, $user[0]->intro_order, $user[0]->intro_tracking, $user[0]->intro_reports]));
			}
			else{
				$token=md5(uniqid($id, true));
				DB::table('tokens')
					->insert(['id' => $id,'token' => $token, 'creationTime' => Carbon::now()]);
				print_r(json_encode([$user[0]->name, $token, $user[0]->juridical, $user[0]->full_name, $user[0]->intro_main, $user[0]->intro_order, $user[0]->intro_tracking, $user[0]->intro_reports]));
			}            
		}
		else{    
			print_r("invalid");
		}
	}

	public function getUser(){        
		$headers = apache_request_headers();
		if(!isset($headers['token'])){
			print_r("unauthorized");
		}
		else{
			$token  =    $headers['token'];
			$user=DB::table('contragents')
				->join('tokens','contragents.id',  '=', 'tokens.id')
				->where('token',$token)
				->select('name', 'juridical', 'contragents.id', 'work_phone as phone', 'email', 'fact_address', 'jur_address', 'full_name', 'nds_number', 'nds_date', 'bank_account as account', 'contract')
				->get();
			if (sizeof($user)>0){
				print_r(json_encode($user));
			}
			else{
				print_r('invalid');
			}
		}
	}

	public function register(){
		$req     = file_get_contents('php://input');
		$req 	 = json_decode($req);
		$text = '';
		for ($i=0; $i < count($req); $i++) { 
			$text = $text.$req[$i]->name.' - '.$req[$i]->value.", \n";
		}
		Mail::to('nadezhuk.o@spark-logistics.com')
				->to('sidirov.s@spark-logistics.com')
            		->send(new newUser($text));
	}

	public function registerPhysic(){
		$headers = apache_request_headers();
		$id         =request()->get('id');
		$name       =request()->get('name');
		$skype      =request()->get('skypeAddress');
		$phone      =request()->get('phone');
		$password   =request()->get('password');
		$email      =request()->get('email');
		$check      =DB::table('contragents')
						->where('id',$id)
						->select('id')                
						->get();
		if(sizeof($check)>0){
			return json_encode("registered");
		}
		else{
			$this->amoAuth();
			$contacts['add']=array(
			   array(
				  'name' => $name,
				  'created_at' => time(),
				  'responsible_user_id'   =>'2147611',
				  'custom_fields' => array(
					array(
						'id' => 447511,
						'values' => array(
							array(
								'value'   => $id,
							)
						)
					),
					array(
						'id' => 80545,
						'values' => array(
							array(
								'value'   => $phone,
								'enum'    => "183923"
							)
						)
					),
					array(
						'id' => 80547,
						'values' => array(
							array(
								'value' => $email,
								'enum' => "183931"
							)                     
						)
					),
					array(
						'id' => 80551,
						'values' => array(
							array(
								'value'   => $skype,
								'enum'    => "183937"
							)
						)
					)              
				)
			)
			);
			/* Теперь подготовим данные, необходимые для запроса к серверу */
			$subdomain='sparkcrm'; #Наш аккаунт - поддомен
			#Формируем ссылку для запроса
			$link='https://'.$subdomain.'.amocrm.ru/api/v2/contacts';
			/* Нам необходимо инициировать запрос к серверу. Воспользуемся библиотекой cURL (поставляется в составе PHP). Подробнее о
			работе с этой
			библиотекой Вы можете прочитать в мануале. */
			$curl=curl_init(); #Сохраняем дескриптор сеанса cURL
			#Устанавливаем необходимые опции для сеанса cURL
			curl_setopt($curl,CURLOPT_RETURNTRANSFER,true);
			curl_setopt($curl,CURLOPT_USERAGENT,'amoCRM-API-client/1.0');
			curl_setopt($curl,CURLOPT_URL,$link);
			curl_setopt($curl,CURLOPT_CUSTOMREQUEST,'POST');
			curl_setopt($curl,CURLOPT_POSTFIELDS,json_encode($contacts));
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
				DB::table('contragents')
					->insert([
						'name'          =>  $name,
						'juridical'     =>  0,
						'id'            =>  $id,
						'amo_id'        =>  $out->id,
						'work_phone'    =>  $phone,
						'email'         =>  $email,
						'fact_address'  =>  null,
						'jur_address'   =>  null,
						'full_name'     =>  null,
						'nds_number'    =>  null,
						'nds_date'      =>  null,
						'contract'      =>  null,
						'bank_account'  =>  null,
						'password'      =>  $password
					]);
				$token=md5(uniqid($id, true));
				DB::table('tokens')
					->insert(['id' => $id,'token' => $token, 'creationTime' => Carbon::now()]);
				return json_encode([$token]);
			  }
			}
			catch(Exception $E)
			{
			  die('Ошибка: '.$E->getMessage().PHP_EOL.'Код ошибки: '.$E->getCode());
			}            
			/*
			 Данные получаем в формате JSON, поэтому, для получения читаемых данных,
			 нам придётся перевести ответ в формат, понятный PHP
			 */
			// $Response=json_decode($out,true);
			// $Response=$Response['_embedded']['items'];
			// $output='ID добавленных контактов:'.PHP_EOL;
			// foreach($Response as $v)
			//  if(is_array($v))
			//    $output.=$v['id'].PHP_EOL;
			// return $output;
			// print_r($Response);
		}
	}

	// public function registerJur(){
	// 	$headers = apache_request_headers();
	// 	$id=request()->get('id');
	// 	$name=request()->get('name');
	// 	$skypeAddress=request()->get('skypeAddress');
	// 	$phone=request()->get('phone');
	// 	$password=request()->get('password');
	// 	$email=request()->get('email');
	// 	$check=DB::table('contragents')
	// 			->where('id',$id)
	// 			->select('id')                
	// 			->get();
	// 	if(sizeof($check)>0){
	// 		return json_encode("QazWsxEdc");
	// 	}
	// 	else{
	// 		$this->amoAuth();
	// 		$company['add']=array(
	// 		   array(
	// 			  'name' => $name,
	// 			  'created_at' => time(),
	// 			  'responsible_user_id'   =>'2147611',
	// 			  'custom_fields' => array(
	// 				array(
	// 					'id' => 447535,
	// 					'values' => array(
	// 						array(
	// 							'value'   => $id,
	// 						)
	// 					)
	// 				),
	// 				array(
	// 					'id' => 80545,
	// 					'values' => array(
	// 						array(
	// 							'value'   => $phone,
	// 							'enum'    => "183919"
	// 						)
	// 					)
	// 				),
	// 				array(
	// 					'id' => 80553,
	// 					'values' => array(
	// 						array(
	// 							'value'   => $skypeAddress,
	// 							'enum'    => "183919"
	// 						)
	// 					)
	// 				),
	// 				array(
	// 					'id' => 80547,
	// 					'values' => array(
	// 						array(
	// 							'value'   => $email,
	// 							'enum'    => "183931"
	// 						)
	// 					)
	// 				)              
	// 			)
	// 		)
	// 		);
	// 		/* Теперь подготовим данные, необходимые для запроса к серверу */
	// 		$subdomain='sparkcrm'; #Наш аккаунт - поддомен
	// 		#Формируем ссылку для запроса
	// 		$link='https://'.$subdomain.'.amocrm.ru/api/v2/companies';
	// 		/* Нам необходимо инициировать запрос к серверу. Воспользуемся библиотекой cURL (поставляется в составе PHP). Подробнее о
	// 		работе с этой
	// 		библиотекой Вы можете прочитать в мануале. */
	// 		$curl=curl_init(); #Сохраняем дескриптор сеанса cURL
	// 		#Устанавливаем необходимые опции для сеанса cURL
	// 		curl_setopt($curl,CURLOPT_RETURNTRANSFER,true);
	// 		curl_setopt($curl,CURLOPT_USERAGENT,'amoCRM-API-client/1.0');
	// 		curl_setopt($curl,CURLOPT_URL,$link);
	// 		curl_setopt($curl,CURLOPT_CUSTOMREQUEST,'POST');
	// 		curl_setopt($curl,CURLOPT_POSTFIELDS,json_encode($company));
	// 		curl_setopt($curl,CURLOPT_HTTPHEADER,array('Content-Type: application/json'));
	// 		curl_setopt($curl,CURLOPT_HEADER,false);
	// 		curl_setopt($curl,CURLOPT_COOKIEFILE,dirname(__FILE__).'/cookie.txt'); #PHP>5.3.6 dirname(__FILE__) -> __DIR__
	// 		curl_setopt($curl,CURLOPT_COOKIEJAR,dirname(__FILE__).'/cookie.txt'); #PHP>5.3.6 dirname(__FILE__) -> __DIR__
	// 		curl_setopt($curl,CURLOPT_SSL_VERIFYPEER,0);
	// 		curl_setopt($curl,CURLOPT_SSL_VERIFYHOST,0);
	// 		$out=curl_exec($curl); #Инициируем запрос к API и сохраняем ответ в переменную
	// 		$code=curl_getinfo($curl,CURLINFO_HTTP_CODE);
	// 		/* Теперь мы можем обработать ответ, полученный от сервера. Это пример. Вы можете обработать данные своим способом. */
	// 		$code=(int)$code;
	// 		$errors=array(
	// 		  301=>'Moved permanently',
	// 		  400=>'Bad request',
	// 		  401=>'Unauthorized',
	// 		  403=>'Forbidden',
	// 		  404=>'Not found',
	// 		  500=>'Internal server error',
	// 		  502=>'Bad gateway',
	// 		  503=>'Service unavailable'
	// 		);
	// 		try
	// 		{
	// 		  #Если код ответа не равен 200 или 204 - возвращаем сообщение об ошибке
	// 			if($code!=200 && $code!=204) {
	// 				throw new Exception(isset($errors[$code]) ? $errors[$code] : 'Undescribed error',$code);
	// 			}
	// 			else{
	// 				DB::table('contragents')
	// 					->insert([
	// 						'name'          =>  $name,
	// 						'juridical'     =>  0,
	// 						'id'            =>  $id,
	// 						'work_phone'    =>  $phone,
	// 						'email'         =>  $email,
	// 						'fact_address'  =>  $skypeAddress,
	// 						'jur_address'   =>  null,
	// 						'full_name'     =>  null,
	// 						'nds_number'    =>  null,
	// 						'nds_date'      =>  null,
	// 						'contract'      =>  null,
	// 						'bank_account'  =>  null,
	// 						'password'      =>  $password
	// 					]);
	// 				$token=md5(uniqid($id, true));
	// 				DB::table('tokens')
	// 					->insert(['id' => $id,'token' => $token, 'creationTime' => Carbon::now()]);
	// 				return json_encode([$token]);
	// 				// return json_encode($out);
	// 			}
	// 		}
	// 		catch(Exception $E)
	// 		{
	// 		  die('Ошибка: '.$E->getMessage().PHP_EOL.'Код ошибки: '.$E->getCode());
	// 		}
	// 		/*
	// 		 Данные получаем в формате JSON, поэтому, для получения читаемых данных,
	// 		 нам придётся перевести ответ в формат, понятный PHP
	// 		 */
	// 		// $Response=json_decode($out,true);
	// 		// $Response=$Response['_embedded']['items'];
	// 		// $output='ID добавленных контактов:'.PHP_EOL;
	// 		// foreach($Response as $v)
	// 		//  if(is_array($v))
	// 		//    $output.=$v['id'].PHP_EOL;
	// 		// return $output;
	// 		// print_r($Response);
	// 	}
	// }

	public function amoAuth(){
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
		if(isset($Response['auth'])) #Флаг авторизации доступен в свойстве "auth"
		 return 'Авторизация прошла успешно';
		return 'Авторизация не удалась';
	}

	public function checkToken($id){
		$token=DB::table('tokens')
				->where('id', $id)
				->get();
		if(sizeof($token)>0){
			// $diff=Carbon::now()->diff(Carbon::parse($token[0]->creationTime));
			// print_r($diff->i);
			// if(($diff->h>4){
			//     DB::table('tokens')
			//         ->where('id', $id)
			//         ->delete();
			//         return false;
			// }
			// else{
				return $token[0]->token;
			// }                
		}
	}

	public function addContragent()
	{
		$req = file_get_contents('php://input');
		$req = json_decode($req);
		$password = hash('sha256',rand());
		$Contragent1С = new Contragent1С;
		$Contragent1С->amo_id = $req->amo_id;
		$Contragent1С->id = $req->id;
		$Contragent1С->juridical = $req->juridical;
		$Contragent1С->name = $req->name;
		$Contragent1С->work_phone = $req->work_phone;
		$Contragent1С->email = $req->email;
		$Contragent1С->fact_address = $req->fact_address;
		$Contragent1С->jur_address = $req->jur_address;
		$Contragent1С->full_name = $req->full_name;
		$Contragent1С->nds_number = $req->nds_number;
		$Contragent1С->nds_date = Carbon::parse($req->nds_date);
		$Contragent1С->bank_account = $req->bank_account;
		$Contragent1С->contract = $req->contract;
		$Contragent1С->manager = $req->manager;
		$Contragent1С->password = $password;
		$Contragent1С->save();

		$ContragentUser = new ContragentUser;
		$ContragentUser->amo_id = $req->amo_id;
		$ContragentUser->id = $req->id;
		$ContragentUser->juridical = $req->juridical;
		$ContragentUser->name = $req->name;
		$ContragentUser->work_phone = $req->work_phone;
		$ContragentUser->email = $req->email;
		$ContragentUser->fact_address = $req->fact_address;
		$ContragentUser->jur_address = $req->jur_address;
		$ContragentUser->full_name = $req->full_name;
		$ContragentUser->nds_number = $req->nds_number;
		$ContragentUser->nds_date = Carbon::parse($req->nds_date);
		$ContragentUser->bank_account = $req->bank_account;
		$ContragentUser->contract = $req->contract;
		$ContragentUser->manager = $req->manager;
		$ContragentUser->skype = $req->skype;
		$ContragentUser->whatsapp = $req->whatsapp;
		$ContragentUser->telegram = $req->telegram;
		$ContragentUser->password = $password;
		$ContragentUser->save();
	}

	// public function updateContragent()
	// {
	// 	DB::table('contragents')
	// 		->update();
	// 	DB::table('cabinet_users')
	// 		->update();
	// 	amoController::updateContragent();
	// }

	public function getContragentByID($amo_id)
	{
		return json_encode(ContragentTemp::find($amo_id));
	}
}
