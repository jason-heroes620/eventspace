<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\EventController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\BoothController;
use App\Http\Controllers\EventBoothController;
use App\Http\Controllers\EventPaymentController;
use App\Http\Controllers\EventCategoriesController;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');


Route::get('events/{id?}', [EventController::class, 'events']);

Route::get('categories', [CategoryController::class, 'categories']);

Route::get('booths', [BoothController::class, 'booths']);

Route::get('/eventbooth/{id?}', [EventBoothController::class, 'eventbooth']);

Route::get('eventcategories/{id?}', [EventCategoriesController::class, 'eventcategories']);

Route::post('payment', [EventPaymentController::class, 'payment']);
Route::get('payment/{id?}', [EventPaymentController::class, 'payment']);

Route::post('EGHLPaymentCallback', [EventPaymentController::class, 'eghlpaymentcallback']);
Route::get('EGHLPaymentCallback', [EventPaymentController::class, 'eghlpaymentcallback']);