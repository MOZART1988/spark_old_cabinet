<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Exception;
use DateTime;

class aliceController extends Controller
{
	public function alice()
	{
		$req=json_decode(file_get_contents('php://input'));
		$response = new \stdClass();
		$response->text="Здравствуйте! Это мы, хороводоведы.";
		$response->tts="Здравствуйте! Это мы, хоров+одо в+еды.";
		$response->buttons[0] = new \stdClass();
		$response->buttons[0]->title="Наш Сайт";
		// $response->buttons[0]->payload=NULL;
		$response->buttons[0]->url="https://spark-logistics.com";
		$response->buttons[0]->hide=true;
		$response->end_session=false;
		$response->session = new \stdClass();
		$response->session->session_id=$req->session->session_id;
		$response->session->message_id=$req->session->message_id;
		$response->session->user_id=$req->session->user_id;
		$response->version=$req->version;
		// print_r('<pre>');
		print_r(json_encode($response, TRUE));
		// print_r('</pre>');
	}
	public function array()
	{	
		phpinfo();
	}
}
