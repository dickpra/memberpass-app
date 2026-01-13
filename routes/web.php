<?php

use Illuminate\Support\Facades\Route;
use App\Models\Payment;
use App\Http\Controllers\HomeController;
use App\Models\GeneralSetting;
use App\Models\BankAccount;


// Halaman Depan (Landing Page)
Route::get('/', [HomeController::class, 'index'])->name('home');

// Route login bawaan Filament (biarkan saja, biasanya otomatis)
Route::get('/invoice/{payment}', function (Payment $payment) {
    // Keamanan: Pastikan yang buka invoice adalah pemiliknya atau admin
    if (auth()->id() !== $payment->user_id && auth()->user()->role !== 'admin') {
        abort(403);
    }

    // 2. Ambil Settings
    $settings = GeneralSetting::first();

    // 3. AMBIL DATA BANKS (Inilah solusi errornya)
    $banks = BankAccount::where('is_active', true)->get();

    // 4. Kirim ke View 'invoice'
    return view('invoice', [ // Pastikan nama view sesuai file kamu (invoice.blade.php)
        'payment' => $payment,
        'settings' => $settings,
        'banks' => $banks, // <--- INI WAJIB ADA
    ]);
})->middleware('auth')->name('invoice.print');