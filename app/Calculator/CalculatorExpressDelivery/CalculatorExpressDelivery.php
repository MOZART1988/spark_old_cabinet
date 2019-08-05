<?php

namespace App\Calculator\CalculatorExpressDelivery;

use App\Calculator\Calculator;
use App\Calculator\CalculatorExpressDelivery\CalculatorExpressData;

class CalculatorExpressDelivery extends Calculator
{
    public function getCost($data = array())
    {
        if (empty($data)) {
            throw new \Exception("Your array is empty");
        }

        /**
         * Check array for schema matching
         */
        if ($data) {

        }

        $delivery_cost = 0;
        $city_from = $data['city_from'];

        foreach (CalculatorExpressData::$data as $item) {
            if ($data['city_from'] == $item['city']) {
                $calculation_data = $item;
            }
        }

        foreach ($data['cargo'] as $key => $item) {
            $delivery_cost += $this->calculate($item, $calculation_data);
        }

        if($data['insurance']) {
            $product_cost = $data['total_cost'];

            if($product_cost >= CalculatorExpressData::$insurance_min) {
                $insurance_cost = (CalculatorExpressData::$insurance_factor / 100) * $product_cost;
                return array(
                    "delivery_cost" => $delivery_cost,
                    "insurance_cost" => $insurance_cost
                );
            }
            $calculation = array(
                "delivery_cost" => $delivery_cost,
                "insurance_price" => CalculatorExpressData::$insurance_cost
            );
        }

        $calculation = array(
            "delivery_cost" => $delivery_cost,
        );

        return $calculation;
    }

    private function calculate($data = array(), $calculation_data = array())
    {
//        dd($data, $calculation_data);
        $price = 0;

        $fuel = CalculatorExpressData::$fuel_surcharge;
        $waybill = CalculatorExpressData::$waybill;
        $over_price = CalculatorExpressData::$over_price;
        $outsized = CalculatorExpressData::$outsize_factor;
        $outweight = CalculatorExpressData::$outweight_factor;

        $width = $data['width'];
        $height = $data['height'];
        $length = $data['length'];

        $price_1 = $calculation_data['price_1'];
        $price_2 = $calculation_data['price_2'];
        $price_3 = $calculation_data['price_3'];
        $step_1 = $calculation_data['step_1'];
        $step_2 = $calculation_data['step_2'];
        $step_3 = $calculation_data['step_3'];

        $form_weight = $data['weight'];
        $volume_weight = self::volume_weight($width, $height, $length);

        if ($length > 120 || $height > 160 || $width > 80) {
            $weight = $form_weight * $outsized > $volume_weight ? $form_weight * $outsized : $volume_weight;
        } elseif ($form_weight >= 120) {
            $weight = ($form_weight * $outweight) > $volume_weight ? ($form_weight * $outweight) : $volume_weight;
        } else {
            $weight = $form_weight > $volume_weight ? $form_weight : $volume_weight;
        }

        if ($weight <= .3) {
            // До .3 кг
            $price = $price_1 + $price_1 * $fuel + $waybill;
        } elseif ($weight <= .5) {
            // До .5 кг
            $price = $price_2 + $price_2 * $fuel + $waybill;
        } elseif ($weight < 1) {
            // до 1 кг
            $price = $price_3 + $price_3 * $fuel + $waybill;
        } else {
            $ceiled = ((ceil($weight * 2) / 2) - 1) * 2;

            if($weight < 10) {
                $price = $price_3 + $over_price * $ceiled + ($price_3 + $step_1 * $ceiled) * $fuel + $waybill;
            } elseif ($weight < 100) {
                $price = $price_3 + $over_price * $ceiled + ($price_3 + $step_2 * $ceiled) * $fuel + $waybill;
            } else {
                $price = $price_3 + $over_price * $ceiled + ($price_3 + $step_3 * $ceiled) * $fuel + $waybill;
            }
        }

        return $price;
    }
}