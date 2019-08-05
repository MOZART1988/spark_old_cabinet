<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Calculator\CalculatorCities;

class CitiesController extends Controller
{
    public function calculate(Request $request, $params = array())
    {
        return response()->json(CalculatorCities::$cities);
    }
}