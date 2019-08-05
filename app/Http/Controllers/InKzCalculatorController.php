<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Calculator\CalculatorDeliveryInKz\CalculatorDeliveryInKz;

class InKzCalculatorController extends Controller
{
    public function getTotalPricing(Request $request, $params = array())
    {
        $calculator = new CalculatorDeliveryInKz;
        return $calculator->getCost($request->all());
    }
}