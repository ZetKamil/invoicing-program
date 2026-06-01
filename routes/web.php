<?php

use Illuminate\Support\Facades\Route;

// Main landing page
Route::view('/', 'welcome')->name('home');

// Public Invoice Payment Portal
Route::get('/pay/{invoice}', \App\Livewire\Public\InvoicePayPortal::class)->name('invoice.pay');

// Stripe Webhook Handler
Route::post('/webhook/stripe', [\App\Http\Controllers\StripeWebhookController::class, 'handleWebhook'])->name('stripe.webhook');

require __DIR__.'/settings.php';
