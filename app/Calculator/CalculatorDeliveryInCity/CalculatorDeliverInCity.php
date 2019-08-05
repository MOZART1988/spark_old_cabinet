<?php

namespace App\Calculator\CalculatorDeliveryInCity;

use App\Calculator\Calculator;

class CalculatorDeliverInCity extends Calculator
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

        foreach ($data['cargo'] as $key => $item) {
            $delivery_cost += $this->calculate($item);
        }

        $delivery_cost = $delivery_cost + ($delivery_cost * 0.11);

        return array(
            "delivery_cost" => $delivery_cost
        );
    }

    protected function calculate($data = array())
    {
        $weight = $data['weight'];
        $volume_weight = self::volume_weight($data['width'], $data['height'], $data['length']);

        $weight = $weight > $volume_weight ? $weight : $volume_weight;

        if($weight < 3) {
            return 1450;
        } elseif($weight < 20) {
            return 3300;
        } elseif($weight < 50) {
            return 3750;
        } else {
            return 4200;
        }
    }
}