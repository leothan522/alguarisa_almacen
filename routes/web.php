<?php

use Illuminate\Support\Facades\Route;

Route::view('/', 'inicio')->name('home');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::view('home', 'dashboard')->name('web.home');
});

require __DIR__.'/settings.php';

Route::get('/instalar-app', function () {
    // $qrAndroid = qrCodeGenerate(\route('descargar-app.android'), null, null, 'qr-android-download');
    $qrIos = qrCodeGenerate(\route('home'), null, null, 'qr-ios-download');

    return view('descargar-app')
        // ->with('qrAndroid', $qrAndroid)
        ->with('qrIos', $qrIos);
})->name('instalar-app');
