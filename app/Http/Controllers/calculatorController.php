<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\DB;
use App\Mail\carcity;
use Illuminate\Support\Facades\Mail;

class calculatorController extends Controller
{
    public function sendMailCarcity()
    {
        $text='Аслан, надо забрать груз, телефон: '.$_GET['phone'].', ';
        if($_GET['weight']){
            $text=$text."\nВес: ".$_GET['weight'].', ';
        }
        if($_GET['width']){
            $text=$text."\nШирина: ".$_GET['width'].', ';
        }
        if($_GET['height']){
            $text=$text."\nВысота: ".$_GET['height'].', ';
        }
        if($_GET['length']){
            $text=$text."\nДлина: ".$_GET['length'].', ';
        }
        if($_GET['city']){
            $text=$text."\nГород: ".$_GET['city'].', ';
        }
        if($_GET['tariff']){
            if($_GET['tariff']==0){
                $text=$text."\nТариф: Эконом, ";
            }
            if($_GET['tariff']==1){
                $text=$text."\nТариф: Экспресс, ";
            }
            if($_GET['tariff']==2){
                $text=$text."\nТариф: Запад+, ";
            }
        }
        if($_GET['recipient']){
            $text=$text."\nДо получателя: ".$_GET['recipient'].', ';
        }
        if($_GET['shop']){
            $text=$text."\nБутик: ".$_GET['shop'].', ';
        }
        if($_GET['price']){
            $text=$text."\nРасчётная цена: ".$_GET['price'].', ';
        }        
        Mail::to('carcity@spark-logistics.com')
            ->send(new carcity($text));
    }

    public function calculateAutoInterCities()
    {
        $MIN_PRICE_AUTO      = 2000;
        $MIN_PRICE_AUTO_FIVE = 2000;
        $MIN_PRICE_AUTO_TEN  = 2000;
        $WAYBILL_PRICE       = 350;
        $PETROL_COST_COEF    = 1.15;
        $PRICE_ONE           = 590;
        $PRICE_TEN           = 590;
        $PRICE_HUNDRED       = 570;
        $OVERSIZED_COEF      = 1.5;
        $CITY_ONE_AND_HALF   = 3762;
        $CITY_THREE          = 5130;
        $CITY_FIVE           = 5586;
        $CITY_SEVEN          = 6498;
        $CITY_TEN            = 6498;
        $hours               = 3;
        $headers             = apache_request_headers();
        $check_individual    = collect([]);
        if (isset($headers['token'])) {
            $token = $headers['token'];
            $sender_array = DB::table('tokens')
                ->join('contragents', 'contragents.id', '=', 'tokens.id')
                ->where('token', $token)
                ->get();
                if($sender_array->isEmpty()){

                }
                else{
                    $check_individual = DB::table('individual_coefs')
                        ->where('id', $sender_array[0]->id)
                        ->get();                    
                }            
        } else {
            $sender_array = collect([]);
        }
        $user_weight   = $_POST['calc_weight'];
        $places        = $_POST['calc_places'];
        $individual    = 0;
        $volume        = $_POST['calc_width'] * $_POST['calc_height'] * $_POST['calc_length'];
        $volume_weight = $_POST['calc_width'] * $_POST['calc_height'] * $_POST['calc_length'] / 5000;
        $oversized     = false;
        $direction_user     = $_POST['calc_from'] . ' - ' . $_POST['calc_to'];
        if ($_POST['calc_width'] > 80 || $_POST['calc_height'] > 160 || $_POST['calc_length'] > 120) {
            $oversized = true;
        }
        if ($user_weight > $volume_weight) {
            $weight = $user_weight;
        } else {
            $weight = $volume_weight;
        }
        $vol = 0;
        if (!$check_individual->isEmpty()) {
            if ($check_individual[0]->calculation_type == "Тоннаж") {
                if ($user_weight > $volume_weight) {
                    $weight = $user_weight;
                } else {
                    $weight = $volume_weight;
                }
            } else {
                if ($check_individual[0]->calculation_type == "ФизВес") {
                    $weight = $user_weight;
                } else {
                    $weight = $volume;
                    $vol    = 1;
                }
            }
            $individual          = 1;
            $PRICE_ONE           = $check_individual[0]->avia_one_to_ten;
            $PRICE_TEN           = $check_individual[0]->avia_ten_to_hundred;
            $PRICE_HUNDRED       = $check_individual[0]->avia_hundred_and_more;
            $OVERSIZED_COEF      = $check_individual[0]->oversized_coef;
            $WAYBILL_PRICE       = $check_individual[0]->waybill;
            $PETROL_COST_COEF    = $check_individual[0]->petrol_coef + 1;
            $CITY_ONE_AND_HALF   = $check_individual[0]->city_one_and_half;
            $CITY_THREE          = $check_individual[0]->city_three;
            $CITY_FIVE           = $check_individual[0]->city_five;
            $CITY_SEVEN          = $check_individual[0]->city_seven;
            $CITY_TEN            = $check_individual[0]->city_ten;
            $MIN_PRICE_AUTO_FIVE = $check_individual[0]->min_five;
            $MIN_PRICE_AUTO_TEN  = $check_individual[0]->min_ten;

            $direction = DB::table('individual_tariffs')
                ->where('direction', $direction_user)
                ->where('id', $sender_array[0]->id)
                ->get();
            if ($direction->isEmpty()) {
                $direction = DB::table('directions')
                    ->where('direction', $direction_user)
                    ->get();
                if ($direction->isEmpty()) {
                    return 'Server lost';
                }
            }
        } else {
            if (!$sender_array->isEmpty()) {
                $direction = DB::table('individual_tariffs')
                    ->where('direction', $direction_user)
                    ->where('id', $sender_array[0]->id)
                    ->get();
                if ($direction->isEmpty()) {
                    $direction = DB::table('directions')
                        ->where('direction', $direction_user)
                        ->get();
                    if ($direction->isEmpty()) {
                        return 'Server lost';
                    }
                }
            } else {
                $direction = DB::table('directions')
                    ->where('direction', $direction_user)
                    ->get();
                if ($direction->isEmpty()) {
                    return 'Server lost';
                }
            }
        }
        if ($oversized == 1) {
            $weight = $weight * $OVERSIZED_COEF;
        }
        if ($weight <= 5 && $individual == 1) {
            $price = $MIN_PRICE_AUTO_FIVE * $PETROL_COST_COEF + $WAYBILL_PRICE;
            print_r('MIN_PRICE_AUTO_FIVE=');
            print_r($MIN_PRICE_AUTO_FIVE);
            print_r('PETROL_COST_COEF=');
            print_r($PETROL_COST_COEF);
            print_r('WAYBILL_PRICE=');
            print_r($WAYBILL_PRICE);
        } else {
            if ($weight <= 10) {
                if ($individual == 1) {
                    $price = $MIN_PRICE_AUTO_TEN * $PETROL_COST_COEF + $WAYBILL_PRICE;
                    print_r('2');
                } else {
                    $price = $MIN_PRICE_AUTO * $PETROL_COST_COEF + $WAYBILL_PRICE;
                }
            } else {
                if ($vol == 0) {
                    $tariff = $direction[0]->price_kg;
                } else {
                    if ($vol == 1) {
                        $tariff = $direction[0]->price_cube;
                    }
                }
                if ($tariff == 0) {
                    return 'Later';
                } else {
                    if ($weight <= 50) {
                        $price = ($MIN_PRICE_AUTO + ($weight - 10) * $tariff) * $PETROL_COST_COEF + $WAYBILL_PRICE;
                    } else {
                        $price = $weight * $tariff * $PETROL_COST_COEF + $WAYBILL_PRICE;
                    }
                }
            }
        }
        if (array_key_exists('calc_insuranse', $_POST)) {
            if ($_POST['calc_insuranse'] == 'on') {
                $price = $price + $_POST['calc_insuranse_sum'] * 0.003;
            };
        }
        print_r(json_encode([$price, $oversized]));
    }

    public function calculateIntoCity()
    {
        $CITY_ONE_AND_HALF = 4025;
        $CITY_THREE        = 5175;
        $CITY_FIVE         = 6325;
        $CITY_SEVEN        = 6498;
        $CITY_TEN          = 6498;
        $WAYBILL_PRICE     = 300;
        if ($_POST['calc_hours'] < 3) {
            $hours = 3;
        } else {
            $hours = $_POST['calc_hours'];
        }
        if ($_POST['calc_city_type'] == '1.5 тонны') {
            $price = $hours * $CITY_ONE_AND_HALF + $WAYBILL_PRICE;
        }
        if ($_POST['calc_city_type'] == '3 тонны') {
            $price = $hours * $CITY_THREE + $WAYBILL_PRICE;
        }
        if ($_POST['calc_city_type'] == '5 тонн') {
            $price = $hours * $CITY_FIVE + $WAYBILL_PRICE;
        }
        if ($_POST['calc_city_type'] == '7 тонн') {
            $price = $hours * $CITY_SEVEN + $WAYBILL_PRICE;
        }
        if ($_POST['calc_city_type'] == '10 тонн') {
            $price = $hours * $CITY_TEN + $WAYBILL_PRICE;
        }
        if ($_POST['calc_insuranse'] == 'on') {
            $price = $price + $_POST['calc_insuranse_sum'] * 0.003;
        }
        $oversized = false;
        print_r(json_encode([$price, $oversized]));
    }

    public function calculateAvia()
    {
        $WAYBILL_PRICE      = 350;
        $PETROL_COST_COEF   = 1.15;
        $MINIMAL_ZERO_THREE = 1850;
        $MINIMAL_ZERO_FIVE  = 1850;
        $MINIMAL_ONE        = 2150;
        $PRICE_ONE          = 590;
        $PRICE_TEN          = 590;
        $PRICE_HUNDRED      = 570;
        $OVERSIZED_COEF     = 1.5;
        $headers            = apache_request_headers();
        $check_individual   = collect([]);
        if (isset($headers['token'])) {
            $token = $headers['token'];
            $sender_array = DB::table('tokens')
                ->join('contragents', 'contragents.id', '=', 'tokens.id')
                ->where('token', $token)
                ->get();
            $check_individual = DB::table('individual_coefs')
                ->where('id', $sender_array[0]->id)
                ->get();
        } else {
            $sender_array = collect([]);
        }
        $user_weight   = $_POST['calc_weight'];
        $places        = $_POST['calc_places'];
        $individual    = 0;
        $volume        = $_POST['calc_width'] * $_POST['calc_height'] * $_POST['calc_length'];
        $volume_weight = $_POST['calc_width'] * $_POST['calc_height'] * $_POST['calc_length'] / 5000;
        $oversized     = false;
        $direction_user     = $_POST['calc_from'] . ' - ' . $_POST['calc_to'];
        if ($_POST['calc_width'] > 80 || $_POST['calc_height'] > 160 || $_POST['calc_length'] > 120) {
            $oversized = true;
        }
        if ($user_weight > $volume_weight) {
            $weight = $user_weight;
        } else {
            $weight = $volume_weight;
        }
        if ($oversized == 1) {
            $weight = $weight * $OVERSIZED_COEF;
        }
        if (!$check_individual->isEmpty()) {
            if ($check_individual[0]->calculation_type == "Тоннаж") {
                if ($user_weight > $volume_weight) {
                    $weight = $user_weight;
                } else {
                    $weight = $volume_weight;
                }
            } else {
                if ($check_individual[0]->calculation_type == "ФизВес") {
                    $weight = $user_weight;
                } else {
                    $weight = $volume;
                    $vol    = 1;
                }
            }
            $individual          = 1;
            $PRICE_ONE           = $check_individual[0]->avia_one_to_ten;
            $PRICE_TEN           = $check_individual[0]->avia_ten_to_hundred;
            $PRICE_HUNDRED       = $check_individual[0]->avia_hundred_and_more;
            $OVERSIZED_COEF      = $check_individual[0]->oversized_coef;
            $WAYBILL_PRICE       = $check_individual[0]->waybill;
            $PETROL_COST_COEF    = $check_individual[0]->petrol_coef + 1;
            $CITY_ONE_AND_HALF   = $check_individual[0]->city_one_and_half;
            $CITY_THREE          = $check_individual[0]->city_three;
            $CITY_FIVE           = $check_individual[0]->city_five;
            $CITY_SEVEN          = $check_individual[0]->city_seven;
            $CITY_TEN            = $check_individual[0]->city_ten;
            $MIN_PRICE_AUTO_FIVE = $check_individual[0]->min_five;
            $MIN_PRICE_AUTO_TEN  = $check_individual[0]->min_ten;

            $direction = DB::table('individual_tariffs')
                ->where('direction', $direction)
                ->where('id', $client_id[0]->id)
                ->get();
            if ($direction->isEmpty()) {
                $direction = DB::table('directions')
                    ->where('direction', $direction)
                    ->get();
                if ($direction->isEmpty()) {
                    return 'Server lost';
                }
            }
        } else {
            if (!$sender_array->isEmpty()) {
                $direction = DB::table('individual_tariffs')
                    ->where('direction', $direction_user)
                    ->where('id', $sender_array[0]->id)
                    ->get();
                if ($direction->isEmpty()) {
                    $direction = DB::table('directions')
                        ->where('direction', $direction_user)
                        ->get();
                    if ($direction->isEmpty()) {
                        return 'Server lost';
                    }
                }
            } else {
                $direction = DB::table('directions')
                    ->where('direction', $direction_user)
                    ->get();
                if ($direction->isEmpty()) {
                    return 'Server lost';
                }
            }
        }
        if ($weight <= 0.3) {
            $price = $MINIMAL_ZERO_THREE * $PETROL_COST_COEF + $WAYBILL_PRICE;
        } else {
            if ($weight <= 0.5) {
                $price = $MINIMAL_ZERO_FIVE * $PETROL_COST_COEF + $WAYBILL_PRICE;
            } else {
                if ($weight <= 1) {
                    $price = $MINIMAL_ONE * $PETROL_COST_COEF + $WAYBILL_PRICE;
                } else {
                    if ($weight <= 10) {
                        $price = (($weight - 1) * $PRICE_ONE + $MINIMAL_ONE) * $PETROL_COST_COEF + $WAYBILL_PRICE;
                    } else {
                        if ($weight <= 100) {
                            $price = ((9 * $PRICE_ONE + $MINIMAL_ONE) + ($weight - 10) * $PRICE_TEN) * $PETROL_COST_COEF + $WAYBILL_PRICE;
                        } else {
                            $price = (9 * $PRICE_ONE + $MINIMAL_ONE + 90 * $PRICE_TEN + ($weight - 100) * $PRICE_HUNDRED) * $PETROL_COST_COEF + $WAYBILL_PRICE;
                        }
                    }
                }
            }
        }
        if (array_key_exists('calc_insuranse', $_POST)) {
            if ($_POST['calc_insuranse'] == 'on') {
                $price = $price + $_POST['calc_insuranse_sum'] * 0.003;
            };
        }
        print_r(json_encode([$price, $oversized]));
    }
}
