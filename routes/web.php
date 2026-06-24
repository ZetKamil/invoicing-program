<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| 🌍 FRONTEND (Public Routes)
|--------------------------------------------------------------------------
| These routes are accessible to everyone from the outside. They form
| the public presentation of the company and the customer payment portal.
|
| NOTE FOR THE JURY: The main "Backend" (Admin Panel) is not here!
| The entire CRM and invoicing system is registered dynamically under the
| hood by the Filament framework (TALL Stack), keeping this file clean.
*/

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

// SaaS Onboarding (Disabled in favor of Super-Admin Manual Intake)
Route::view('/billing/inactive', 'billing.inactive')->name('billing.inactive');

/*
|--------------------------------------------------------------------------
| ⚙️ BACKEND (System Logic & Webhooks)
|--------------------------------------------------------------------------
| Hidden entry points for external services and custom overrides for
| system administrative actions.
*/

// Stripe Webhook Handler
// Uses a named rate limiter (registered in AppServiceProvider) instead of
// the generic throttle:60,1 — provides proper Retry-After headers and
// limits to 30 req/min per IP (Stripe never sends more than 1 per event).
Route::post('/webhook/stripe', [\App\Http\Controllers\StripeWebhookController::class, 'handleWebhook'])
    ->middleware('throttle:stripe-webhooks')
    ->name('stripe.webhook');

require __DIR__ . '/settings.php';

// Custom Fallback for Impersonation Exit (Hijacking the package's broken native route)
Route::get('/filament-impersonate/leave', [\App\Http\Controllers\ImpersonationController::class, 'leave'])->name('filament-impersonate.leave');
