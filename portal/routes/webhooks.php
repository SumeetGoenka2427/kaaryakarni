<?php

use App\Http\Controllers\Webhooks\RazorpayWebhookController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Payment gateway webhooks (/webhooks/{provider})
|--------------------------------------------------------------------------
| Stateless: no session, no CSRF. Every request must pass the provider's
| signature verification before anything is read from it.
*/

Route::post('razorpay', RazorpayWebhookController::class)->name('razorpay');
