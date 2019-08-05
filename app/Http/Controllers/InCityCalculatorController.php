<?php

namespace App\Http\Controllers;

use App\Calculator\CalculatorDeliveryInCity\CalculatorDeliverInCity;
use Illuminate\Http\Request;

class InCityCalculatorController extends Controller
{
    public function getTotalPricing(Request $request, $params = array())
    {
        $calculator = new CalculatorDeliverInCity;
        return $calculator->getCost($request->all());
    }
}