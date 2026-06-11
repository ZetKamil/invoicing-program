<?php

use Illuminate\Support\Facades\Route;

// Public Multi-Page Site
Route::view('/', 'pages.home')->name('home');
Route::view('/diensten', 'pages.services')->name('services');
Route::redirect('/pakketten', '/diensten');
Route::view('/cases', 'pages.cases')->name('cases');
Route::redirect('/realisaties', '/cases');
Route::view('/over-ons', 'pages.about')->name('about');
Route::view('/blog', 'pages.blog')->name('blog');

// Public Invoice Payment Portal
// Rate-limited to prevent ULID enumeration attacks on customer invoices.
Route::get('/pay/{invoice}', \App\Livewire\Public\InvoicePayPortal::class)
    ->middleware('throttle:invoice-portal')
    ->name('invoice.pay');

// Stripe Webhook Handler
// Uses a named rate limiter (registered in AppServiceProvider) instead of
// the generic throttle:60,1 â€” provides proper Retry-After headers and
// limits to 30 req/min per IP (Stripe never sends more than 1 per event).
Route::post('/webhook/stripe', [\App\Http\Controllers\StripeWebhookController::class, 'handleWebhook'])
    ->middleware('throttle:stripe-webhooks')
    ->name('stripe.webhook');

// SaaS Onboarding (Disabled in favor of Super-Admin Manual Intake)
// Route::get('/register', \App\Livewire\Public\RegisterBedrijf::class)->name('register');
Route::view('/billing/inactive', 'billing.inactive')->name('billing.inactive');

require __DIR__.'/settings.php';

// Custom Fallback for Impersonation Exit (Hijacking the package's broken native route)
Route::get('/filament-impersonate/leave', [\App\Http\Controllers\ImpersonationController::class, 'leave'])->name('filament-impersonate.leave');
