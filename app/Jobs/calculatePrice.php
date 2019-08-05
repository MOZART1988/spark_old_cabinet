<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;

class calculatePrice implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $lead_id;
    protected $order_id;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($lead_id, $order_id)
    {
        $this->lead_id  = $lead_id;
        $this->order_id = $order_id;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $MIN_PRICE_AUTO      = 2000;
        $MIN_PRICE_AUTO_FIVE = 2000;
        $MIN_PRICE_AUTO_TEN  = 2000;
        $WAYBILL_PRICE       = 300;
        $PETROL_COST_COEF    = 1.14;
        $PRICE_ONE           = 590;
        $PRICE_TEN           = 590;
        $PRICE_HUNDRED       = 550;
        $OVERSIZED_COEF      = 1.5;
        $CITY_ONE_AND_HALF   = 3762;
        $CITY_THREE          = 5130;
        $CITY_FIVE           = 5586;
        $CITY_SEVEN          = 6498;
        $CITY_TEN            = 6498;
        $lifting_capacity;
        $hours = 3;
        $order = DB::table('orders_new')
            ->join('leads_new', 'orders_new.lead_id', '=', 'leads_new.lead_id')
            ->where('orders_new.lead_id', $this->lead_id)
            ->where('order_id', $this->order_id)
            ->get();
        $client_id = DB::table('contragents')
            ->join('leads_new', 'contragents.full_name', '=', 'leads_new.client_full_name')
            ->where('leads_new.lead_id', $this->lead_id)
            ->select('contragents.id')
            ->get();
        $check_individual = DB::table('individual_coefs')
            ->where('id', $client_id[0]->id)
            ->get();
        $weight     = 0;
        $price      = 0;
        $individual = 0;
        $vol        = 0;
        $oversized  = $order[0]->oversized;
        if ($order[0]->weight > $order[0]->volume_weight) {
            $weight = $order[0]->weight;
        } else {
            $weight = $order[0]->volume_weight;
        }

        if (!$check_individual->isEmpty()) {
            if ($check_individual[0]->calculation_type == "Тоннаж") {
                if ($order[0]->weight > $order[0]->volume_weight) {
                    $weight = $order[0]->weight;
                } else {
                    $weight = $order[0]->volume_weight;
                }
            } else {
                if ($check_individual[0]->calculation_type == "ФизВес") {
                    $weight = $order[0]->weight;
                } else {
                    $weight = $order[0]->cubic_capacity;
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
                ->where('code', $order[0]->direction)
                ->where('id', $client_id[0]->id)
                ->get();
            if ($direction->isEmpty()) {
                $direction = DB::table('directions')
                    ->where('code', $order[0]->direction)
                    ->get();
            }
        } else {
            $direction = DB::table('individual_tariffs')
                ->where('code', $order[0]->direction)
                ->where('id', $client_id[0]->id)
                ->get();
            if ($direction->isEmpty()) {
                $direction = DB::table('directions')
                    ->where('code', $order[0]->direction)
                    ->get();
            }
        }
        if ($order[0]->hours < 3) {
            $hours = 3;
        } else {
            $hours = $order[0]->hours;
        }
        if ($order[0]->delivery_type_big == 'LTL Авиа') {
            if ($weight <= 0.3) {
                $price = 1450 * $PETROL_COST_COEF + $WAYBILL_PRICE;
                if ($oversized == 1) {
                    $price = $price * $OVERSIZED_COEF;
                }
                $job = (new SendPrice($this->lead_id, $price))->onQueue('amoPrices');
                dispatch($job);
            } else {
                if ($weight <= 0.5) {
                    $price = 1550 * $PETROL_COST_COEF + $WAYBILL_PRICE;
                    if ($oversized == 1) {
                        $price = $price * $OVERSIZED_COEF;
                    }
                    $job = (new SendPrice($this->lead_id, $price))->onQueue('amoPrices');
                    dispatch($job);
                } else {
                    if ($weight <= 1) {
                        $price = 1750 * $PETROL_COST_COEF + $WAYBILL_PRICE;
                        if ($oversized == 1) {
                            $price = $price * $OVERSIZED_COEF;
                        }
                        $job = (new SendPrice($this->lead_id, $price))->onQueue('amoPrices');
                        dispatch($job);
                    } else {
                        if ($weight <= 10) {
                            $price = (($weight - 1) * $PRICE_ONE + 1750) * $PETROL_COST_COEF + $WAYBILL_PRICE;
                            if ($oversized == 1) {
                                $price = $price * $OVERSIZED_COEF;
                            }
                            $job = (new SendPrice($this->lead_id, $price))->onQueue('amoPrices');
                            dispatch($job);
                        } else {
                            if ($weight <= 100) {
                                $price = ((9 * $PRICE_ONE + 1750) + ($weight - 10) * $PRICE_TEN) * $PETROL_COST_COEF + $WAYBILL_PRICE;
                                if ($oversized == 1) {
                                    $price = $price * $OVERSIZED_COEF;
                                }
                                $job = (new SendPrice($this->lead_id, $price))->onQueue('amoPrices');
                                dispatch($job);
                            } else {
                                $price = (9 * $PRICE_ONE + 1750 + 90 * $PRICE_TEN + ($weight - 100) * $PRICE_HUNDRED) * $PETROL_COST_COEF + $WAYBILL_PRICE;
                                if ($oversized == 1) {
                                    $price = $price * $OVERSIZED_COEF;
                                }
                                $job = (new SendPrice($this->lead_id, $price))->onQueue('amoPrices');
                                dispatch($job);
                            }
                        }
                    }
                }
            }
            // $price = $MIN_PRICE_AUTO+($weight-1)*$tariff
            // $job = (new price($this->lead_id, $price))->onQueue('amoPrices');
            // dispatch($job);
        } else {
            if ($order[0]->delivery_type_big == 'FTL Фрахт' || $order[0]->delivery_type_big == 'FTL Городская доставка') {
                $job = (new task($this->lead_id, 'Указать бюджет по направлению.' . $direction[0]->direction, $order[0]->author, 0))->onQueue('amoTasks');
                dispatch($job);
            } else {
                if ($order[0]->delivery_type_big == 'LTL гор. доставка' || $order[0]->delivery_type_big == 'LTL гор. дост. день в день') {
                    if ($order[0]->lifting_capacity = '1.5 тонны') {
                        $price = $hours * $CITY_ONE_AND_HALF + $WAYBILL_PRICE;
                        $job   = (new SendPrice($this->lead_id, $price))->onQueue('amoPrices');
                        dispatch($job);
                    }
                    if ($order[0]->lifting_capacity = '3 тонны') {
                        $price = $hours * $CITY_THREE + $WAYBILL_PRICE;
                        $job   = (new SendPrice($this->lead_id, $price))->onQueue('amoPrices');
                        dispatch($job);
                    }
                    if ($order[0]->lifting_capacity = '5 тонн') {
                        $price = $hours * $CITY_FIVE + $WAYBILL_PRICE;
                        $job   = (new SendPrice($this->lead_id, $price))->onQueue('amoPrices');
                        dispatch($job);
                    }
                    if ($order[0]->lifting_capacity = '7 тонн') {
                        $price = $hours * $CITY_SEVEN + $WAYBILL_PRICE;
                        $job   = (new SendPrice($this->lead_id, $price))->onQueue('amoPrices');
                        dispatch($job);
                    }
                    if ($order[0]->lifting_capacity = '10 тонн') {
                        $price = $hours * $CITY_TEN + $WAYBILL_PRICE;
                        $job   = (new SendPrice($this->lead_id, $price))->onQueue('amoPrices');
                        dispatch($job);
                    }
                    if ($order[0]->lifting_capacity = '20 тонн') {
                        $job = (new task($this->lead_id, 'Указать бюджет по направлению.' . $direction[0]->direction, $order[0]->author, 0))->onQueue('amoTasks');
                        dispatch($job);
                    }
                } else {
                    if ($weight <= 5 && $individual == 1) {
                        $price = $MIN_PRICE_AUTO_FIVE * $weight * $PETROL_COST_COEF + $WAYBILL_PRICE;
                        if ($oversized == 1) {
                            $price = $price * $OVERSIZED_COEF;
                        }
                        $job = (new SendPrice($this->lead_id, $price))->onQueue('amoPrices');
                        dispatch($job);
                    } else {
                        if ($weight <= 10) {
                            if ($individual == 1) {
                                $price = $MIN_PRICE_AUTO_TEN * $weight * $PETROL_COST_COEF + $WAYBILL_PRICE;
                                if ($oversized == 1) {
                                    $price = $price * $OVERSIZED_COEF;
                                }
                                $job = (new SendPrice($this->lead_id, $price))->onQueue('amoPrices');
                                dispatch($job);
                            } else {
                                $price = $MIN_PRICE_AUTO * $weight * $PETROL_COST_COEF + $WAYBILL_PRICE;
                                if ($oversized == 1) {
                                    $price = $price * $OVERSIZED_COEF;
                                }
                                // print_r('For less then 10 kg, price is '.$price);
                                $job = (new SendPrice($this->lead_id, $price))->onQueue('amoPrices');
                                dispatch($job);
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
                                $job = (new task($this->lead_id, 'Цена за доставку по направлению "' . $direction[0]->direction . '" не содержится в базе данных. Пожалуйста, укажите бюджет по направлению вручную.', $order[0]->author, 0))->onQueue('amoTasks');
                                dispatch($job);
                            } else {
                                if ($weight <= 50) {
                                    $price = $MIN_PRICE_AUTO * 10 + ($weight - 10) * $tariff * $PETROL_COST_COEF + $WAYBILL_PRICE;
                                    if ($oversized == 1) {
                                        $price = $price * $OVERSIZED_COEF;
                                    }
                                    // print_r('For less then 50 kg, price is '.$price);
                                    $job = (new SendPrice($this->lead_id, $price))->onQueue('amoPrices');
                                    dispatch($job);
                                } else {
                                    $price = $weight * $tariff * $PETROL_COST_COEF + $WAYBILL_PRICE;
                                    if ($oversized == 1) {
                                        $price = $price * $OVERSIZED_COEF;
                                    }
                                    // print_r('For more then 50 kg, price is '.$price);
                                    $job = (new SendPrice($this->lead_id, $price))->onQueue('amoPrices');
                                    dispatch($job);
                                }
                            }
                        }
                    }
                }
            }
        }
    }
}
