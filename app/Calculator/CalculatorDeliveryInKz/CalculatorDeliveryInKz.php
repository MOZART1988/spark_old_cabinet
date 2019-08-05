<?php

namespace App\Calculator\CalculatorDeliveryInKz;

use App\Calculator\Calculator;

class CalculatorDeliveryInKz extends Calculator
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
        $city_to = $data['city_to'];

        foreach ($data['cargo'] as $key => $item) {
            $delivery_cost += $this->calculate($item, CalculatorDeliveryInKzPricing::$costs, $city_from, $city_to);
        }

        if($data['insurance']) {
            $product_cost = $data['total_cost'];

            if($product_cost >= CalculatorDeliveryInKzVars::$insurance_min) {
                $insurance_cost = (CalculatorDeliveryInKzVars::$insurance_factor / 100) * $product_cost;
                return array(
                    "delivery_cost" => $delivery_cost,
                    "insurance_cost" => $insurance_cost
                );
            }
            return array(
                "delivery_cost" => $delivery_cost,
                "insurance_price" => CalculatorDeliveryInKzVars::$insurance_cost
            );
        }

        return array("delivery_cost" => $delivery_cost);
    }

    /**
     * Calculate delivery between Kazakhstan cities and towns.
     * @param array $data
     * @throws \Exception
     * @return integer
     */
    protected function calculate($data = array(), $costs = array(), $city_from, $city_to)
    {
        $pricing = 0;

        $min_price = CalculatorDeliveryInKzVars::$minimum_price_for_5;
        $min_price2 = CalculatorDeliveryInKzVars::$minimum_price_for_5;
        $fuel = CalculatorDeliveryInKzVars::$fuel_surcharge;
        $waybill = CalculatorDeliveryInKzVars::$waybill;
        $outsized = CalculatorDeliveryInKzVars::$outsize_factor;
        $outweight = CalculatorDeliveryInKzVars::$outweight_factor;

        foreach ($costs as $key => $cost) {
            if ($cost['city_from'] == $city_from && $cost['city_to'] == $city_to) {
                $pricing = $cost;
            }
        }

        $width = $data['width'];
        $height = $data['height'];
        $length = $data['length'];

        $form_weight = $data['weight'];
        $volume_weight = self::volume_weight($width, $height, $length);
        // Вес
        if ($length > 120 || $height > 160 || $width > 80) {
            $weight = $form_weight * $outsized > $volume_weight ? $form_weight * $outsized : (float)$volume_weight;
        } elseif ($form_weight >= 120) {
            $weight = ($form_weight * $outweight) > $volume_weight ? ($form_weight * $outweight) : (float)$volume_weight;
        } else {
            $weight = $form_weight > $volume_weight ? $form_weight : (float)$volume_weight;
        }

        $weight = ceil($weight);

        if (($city_from == $pricing['city_from']) && ($city_to == $pricing['city_to'])) {
            $rate = (float)$pricing['pricing_from'];
            $days = $pricing['days_from'];
        } else {
            $rate = (float)$pricing['pricing_to'];
            $days = $pricing['days_to'];
        }

        if ($weight < 5) {
            // До 5 кг
            $calculation = $min_price + $min_price * $fuel + $waybill;
        } elseif ($weight <= 10) {
            // До 10 кг
            $calculation = $min_price2 + $min_price2 * $fuel + $waybill;
        } elseif ($weight < 50) {
            // Больше 10 кг
            // (Минсбор + (Вес - минсбор) * Тариф) * на другие
            $weight -= 10;
            $calculation = ($min_price2 + $weight * $rate) + ($min_price2 + $weight * $rate) * $fuel + $waybill;
        } elseif ($weight < 120) {
            // Больше 50 кг
            $calculation = ($weight * $rate) + ($weight * $rate) * $fuel + $waybill;
        } else {
            $calculation = ($weight * $rate) + ($weight * $rate) * $fuel + $waybill;
        }

        return $calculation;
    }
}