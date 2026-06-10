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
Route::get('/pay/{invoice}', \App\Livewire\Public\InvoicePayPortal::class)->name('invoice.pay');

// Stripe Webhook Handler
Route::post('/webhook/stripe', [\App\Http\Controllers\StripeWebhookController::class, 'handleWebhook'])
    ->middleware('throttle:60,1')
    ->name('stripe.webhook');

// SaaS Onboarding (Disabled in favor of Super-Admin Manual Intake)
// Route::get('/register', \App\Livewire\Public\RegisterTenant::class)->name('register');
Route::view('/billing/inactive', 'billing.inactive')->name('billing.inactive');

require __DIR__.'/settings.php';

// Custom Fallback for Impersonation Exit (Hijacking the package's broken native route)
Route::get('/filament-impersonate/leave', [\App\Http\Controllers\ImpersonationController::class, 'leave'])->name('filament-impersonate.leave');
