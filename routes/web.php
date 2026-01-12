<?php

use Illuminate\Support\Facades\Route;
use App\Models\Payment;
use App\Http\Controllers\HomeController;



// Halaman Depan (Landing Page)
Route::get('/', [HomeController::class, 'index'])->name('home');

// Route login bawaan Filament (biarkan saja, biasanya otomatis)
Route::get('/invoice/{payment}', function (Payment $payment) {
    // Keamanan: Pastikan yang buka invoice adalah pemiliknya atau admin
    if (auth()->id() !== $payment->user_id && auth()->user()->role !== 'admin') {
        abort(403);
    }

    $settings = \App\Models\GeneralSetting::first();

    return view('invoice', compact('payment', 'settings'));
})->middleware('auth')->name('invoice.print');