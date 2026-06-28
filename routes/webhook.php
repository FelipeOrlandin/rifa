<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\MercadoPagoWebhookController;

/*
|--------------------------------------------------------------------------
| Webhook Routes
|--------------------------------------------------------------------------
*/

Route::post('/webhook/mercadopago', [MercadoPagoWebhookController::class, 'handle'])
    ->name('webhook.mercadopago');
