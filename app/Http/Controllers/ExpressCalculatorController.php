<?php

namespace App\Http\Controllers;

use App\Calculator\CalculatorExpressDelivery\CalculatorExpressDelivery;
use Illuminate\Http\Request;

class ExpressCalculatorController extends Controller
{
    public function getTotalPricing(Request $request, $params = array())
    {
        $calculator = new CalculatorExpressDelivery;
        return $calculator->getCost($request->all());
    }
}