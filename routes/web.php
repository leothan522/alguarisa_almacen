<?php

use Illuminate\Support\Facades\Route;

Route::view('/', 'inicio')->name('home');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::view('home', 'dashboard')->name('web.home');
});

require __DIR__.'/settings.php';
