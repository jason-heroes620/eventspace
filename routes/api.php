<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\EventController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\BoothController;
use App\Http\Controllers\EventBoothController;
use App\Http\Controllers\EventPaymentController;
use App\Http\Controllers\EventCategoriesController;
use App\Http\Controllers\EventApplicationsController;
use App\Http\Controllers\EventOrdersController;
use App\Http\Controllers\TermsAndConditionsController;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');


Route::get('events/{id?}', [EventController::class, 'events']);

Route::get('categories', [CategoryController::class, 'categories']);

Route::get('booths', [BoothController::class, 'booths']);

Route::get('/eventbooth/{id?}', [EventBoothController::class, 'eventbooth']);

Route::get('eventcategories/{id?}', [EventCategoriesController::class, 'eventCategories']);

Route::post('payment', [EventPaymentController::class, 'payment']);
Route::get('payment/{id?}', [EventPaymentController::class, 'payment']);
Route::get('makepayment/{id?}/code/{code?}', [EventPaymentController::class, 'paymentCode']);

Route::post('EGHLPaymentCallback', [EventPaymentController::class, 'eghlpaymentcallback']);
Route::get('EGHLPaymentCallback', [EventPaymentController::class, 'eghlpaymentcallback']);

Route::post('applications', [EventApplicationsController::class, 'applications']);
Route::get('applications/{id?}', [EventApplicationsController::class, 'applications']);

Route::get('tnc/{id?}', [TermsAndConditionsController::class, 'tnc']);

Route::post('orders', [EventOrdersController::class, 'orders']);
Route::get('orders', [EventOrdersController::class, 'orders']);
Route::post('EGHLOrderPaymentCallback', [EventOrdersController::class, 'eghlpaymentcallback']);
Route::get('EGHLOrderPaymentCallback', [EventOrdersController::class, 'eghlpaymentcallback']);
