<?php

use Illuminate\Support\Facades\Route;
use Paymenter\Extensions\Gateways\ManualPayment\ManualPayment;

Route::post('/extensions/manual-payment/invoices/{invoice}/submit', [ManualPayment::class, 'submit'])
    ->middleware(['web', 'auth', 'can:view,invoice'])
    ->name('extensions.gateways.manual-payment.submit');
