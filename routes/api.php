<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redirect; 

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::middleware('auth:api')->get('/user', function (Request $request) {
	return $request->user();
});

Route::prefix('relog')->group(function () {
	Route::post('status', 'RelogController@statusCheck');
});


Route::get('/test', 'testController@testCreate');

Route::get('/cities', 'CitiesController@calculate');

Route::get('/directions/statistics', 'CitiesController@getDirectionsStatistics');

Route::post('/inkz', 'InKzCalculatorController@getTotalPricing');

Route::post('/express', 'ExpressCalculatorController@getTotalPricing');

Route::post('/incity', 'InCityCalculatorController@getTotalPricing');

Route::post('/order/to/db/{type}', 'cabinetController@addOrderToDB');

Route::post('/callback', 'amoController@callback');

Route::get('/reports', 'cabinetController@getAllOrders');

Route::get('/mail/carcity', 'calculatorController@sendMailCarcity');

Route::post('/calculate/incity', 'calculatorController@calculateIntoCity');

Route::post('/calculate/country', 'calculatorController@calculateAutoInterCities');

Route::post('/calculate/avia', 'calculatorController@calculateAvia');

Route::post('/calculate/ftl', 'calculatorController@ftl');

Route::get('/notifications', 'cabinetController@getAllNotifications');

Route::get('/notifications/read/{id}', 'cabinetController@notificationReadByUser');

Route::get('/orderdata', 'cabinetController@getOrdersData');

Route::get('/add/waybill/{waybill}', 'testController@addWaybills');

Route::get('/notifications/amount', 'cabinetController@getAllNotificationsAmount');

Route::get('/reports/date', 'cabinetController@getOrdersWithinDate');

Route::get('/reports/directions/filter', 'cabinetController@getOrdersByDirection');

Route::get('/intros', 'cabinetController@getInros');

Route::get('/reports/{id}', 'cabinetController@getOrderById');

Route::get('/reports/common/{id}', 'cabinetController@siteSearch');

Route::get('/reports/status/{id}', 'cabinetController@getOrdersByStatus');

Route::get('/qr/{id}', 'cabinetController@getInfoForQR');

Route::get('/reports/status/amocontra', 'cabinetController@getOrdersByStatus');

Route::put('/update/profile', 'cabinetController@updateUserProfile');

Route::get('/user',  'userController@getUser');

Route::put('/order', 'cabinetController@addOrder');

Route::post('/image', 'testController@image');

Route::put('/order/test', 'testController@addOrder');

Route::get('/conts', 'testController@conts');

Route::post('/contSend', 'testController@contsSent');

Route::get('/calculate/test', 'calculatorController@calculate');

Route::get('/directions/test', 'testController@getDirections');

Route::put('/intro/{id}/complete', 'cabinetController@introComplete');

Route::post('/order/fast', 'cabinetController@addOrderFast');

Route::get('/templates/sender/list', 'cabinetController@getSenderTemplateList');

Route::get('/templates/recipient/list', 'cabinetController@getRecipientTemplateList');

Route::get('/templates/sender/{id}', 'cabinetController@getSenderTemplate');

Route::get('/templates/recipient/{id}', 'cabinetController@getRecipientTemplate');

Route::get('/templates/order/list', 'cabinetController@getOrderTemplateList');

Route::get('/templates/order/{id}', 'cabinetController@getOrderTemplate');

Route::get('/templates/orders/short', 'cabinetController@getOrderTemplatesShort');

Route::get('/templates/recipients/short', 'cabinetController@getRecipientTemplatesShort');

Route::get('/templates/senders/short', 'cabinetController@getSenderTemplatesShort');

Route::get('/lead/{id}', 'amoController@getLead');

Route::get('/sendPriceAndClose', 'testController@sendCompleteWithPrice');

Route::get('/lead/{id}/orders', 'amoController@getOrdersByLead');

Route::get('/lead/{id}/orders/test', 'testController@getOrdersByLead');

Route::put('/manager/orders/lead/{id}', 'amoController@postOrdersOneLead');

Route::delete('/template/order/{id}', 'cabinetController@deleteOrderTemplate');

Route::delete('/template/contact/{id}', 'cabinetController@deleteContactTemplate');

Route::delete('/lead/{id}', 'amoController@deleteLead');

Route::put('/template/order/{id}', 'cabinetController@updateOrderTemplate');

Route::get('/contragents/list', 'amoController@getContragents');

Route::get('/contragents/single/{id}', 'userController@getContragentByID');

Route::post('/contragents', 'userController@addContragent');

Route::post('/contragents/to/temp', 'amoController@addCompany');

Route::get('/contragents/list/array', 'amoController@getContragentsAsArray');

Route::get('/leadid/{id}', 'cabinetController@addLead1C');

Route::get('/directions/list', 'amoController@getDirections');

Route::get('/contact/{id}', 'cabinetController@getContact');

Route::get('/company/{id}', 'cabinetController@getCompany');

Route::get('lead/{id}/complete', 'cabinetController@leadComplete');

Route::get('/cronLeadPrice','cabinetController@cronLeadPrice');

Route::get('/cronUpdateStatus','cabinetController@cronLeadStatuses');

Route::post('/alice', 'aliceController@alice');

Route::get('/alice1', 'aliceController@array');

Route::post('/link', 'amoController@link');

Route::get('/amocom', 'amoController@amocom');

Route::get('/profile', 'cabinetController@getUserProfile');

//Route::get('/code/{id}', 'amoController@code');

Route::get('/hot', 'cabinetController@getHotDirections');

Route::get('/leadbyorder/{ordernumber}', 'amoController@getLeadIDByOrderNumber');

Route::get('waybill/fill/{id}', 'amoController@getDataForFill');

Route::options('/order', function() {
		$response = [
			'methods-allowed' => ['POST'],
			'description' => 'Route to add an order'
		];
		return json_encode($response);
});


Route::options('/order/fast', function() {
		$response = [
			'methods-allowed' => ['POST'],
			'description' => 'Route to add an order fast'
		];
		return json_encode($response);
});

Route::put('/lead/orders/{leadId}', 'cabinetController@writeOrders');

Route::options('/callback', function() {
	$response = [
		'Access-Control-Allow-Origin' => '*',
		'Access-Control-Allow-Headers' => '*',
		'methods-allowed' => ['POST'],
		'description' => 'Route to get a callback'
	];
	return json_encode($response);
});

Route::options('/directions/test', function() {
	$response = [
		'Access-Control-Allow-Origin' => '*',
		'Access-Control-Allow-Headers' => '*',
		'methods-allowed' => ['GET'],
		'description' => 'Route to get a callback'
	];
	return json_encode($response);
});

Route::post('/login', 'userController@login')->middleware('cors');

Route::post('/register', 'userController@register')->middleware('cors');

Route::post('/logout', 'userController@logout');

Route::post('/registerphysic', 'userController@registerPhysic');

Route::post('/registerjur', 'userController@registerJur');

Route::get('/newest', 'cabinetController@getNewestNotifications');

Route::put('/template/update/recipient/{id}', 'cabinetController@updateRecipientTemplate');

Route::put('/template/update/sender/{id}', 'cabinetController@updateSenderTemplate');

Route::get('/set/complete', 'amoController@closeLeads');

Route::get('/set/prices', 'amoController@sendPrices');

Route::get('/set/waybills', 'amoController@setWaybills');

Route::post('/task/calculation', 'amoController@tasksForCalculation');

Route::post('/order/multiple', 'testController@leadTest');

// Route::get('/set/amoid', 'amoController@setAmoIds');

Route::get('/set/passwords', 'cabinetController@setPasswords');

Route::get('/send/message', 'testController@telegram');

Route::get('/lead/{leadId}/full', 'amoController@getLeadByID');

Route::get('/relog/pickup', 'RelogController@pickUp');

Route::get('/get/statistics', 'amoController@getStatistics');

Route::get('/get/lead/{id}', 'amoController@getLeadVue');

Route::get('/get/lead/{id}/waybills', 'amoController@getLeadWaybillsVue');

Route::put('/transfer', 'amoController@sendTransfer');

Route::post('/vue/lead', 'amoController@postLeadVue');

// Route::get('/sen/companies/crm', 'testController@companies');

Route::put('/mass/order', 'cabinetController@massOrder');

Route::put('/landing', 'cabinetController@landing');

Route::put('/ecar', 'cabinetController@ecar');

Route::get('/thereisnospoon', 'cabinetController@setIDS');

Route::post('/contr', 'cabinetController@register');

Route::post('/resetpass', 'cabinetController@resetPassword');