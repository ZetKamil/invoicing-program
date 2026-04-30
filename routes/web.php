<?php

use Illuminate\Support\Facades\Route;

// Main landing page
Route::view('/', 'welcome')->name('home');

require __DIR__.'/settings.php';
