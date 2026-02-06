<?php

use Illuminate\Support\Facades\Route;
use App\Models\Payment;
use App\Http\Controllers\HomeController;
use App\Models\GeneralSetting;
use App\Models\BankAccount;
use App\Http\Controllers\DonationReceiptController;
use App\Http\Controllers\SecureFileController;
// Halaman Depan (Landing Page)
Route::get('/', [HomeController::class, 'index'])->name('home');

Route::get('/check-member', [HomeController::class, 'checkMember'])->name('check.member');

// HAPUS ->middleware('auth') JIKA ADA DI GROUP INI
Route::get('/invoice/{payment}', function (Payment $payment) {
    
    // 1. CEK OTENTIKASI MANUAL (SUPPORT DUAL GUARD)
    $webUser = auth('web')->user();   // Member
    $adminUser = auth('admin')->user(); // Admin

    // Jika tidak ada yang login sama sekali -> Lempar ke Login Member
    if (!$webUser && !$adminUser) {
        return redirect('/member/login');
    }

    // 2. CEK HAK AKSES (AUTHORIZATION)
    // Jika login sebagai Member, pastikan dia pemilik invoice
    if ($webUser && !$adminUser) {
        if ($payment->user_id !== $webUser->id) {
            abort(403, 'Unauthorized access to this invoice.');
        }
    }
    // Jika Admin ($adminUser ada), otomatis lolos (Boleh lihat semua)

    // 3. AMBIL DATA UNTUK VIEW
    $settings = GeneralSetting::first();
    $banks = BankAccount::where('is_active', true)->get();

    return view('invoice', [
        'payment' => $payment,
        'settings' => $settings,
        'banks' => $banks,
    ]);

})->name('invoice.print');

// ==========================================================
// 3. ROUTE KHUSUS MEMBER (Harus Login) -> TARUH DISINI
// ==========================================================
Route::middleware(['auth'])->group(function () {
    
    // Route Download Receipt Donasi
    Route::get('/member/donations/{donation}/receipt', [DonationReceiptController::class, 'download'])
        ->name('donation.receipt');

});

Route::get('/secure-files/{filepath}', [SecureFileController::class, 'show'])
    ->where('filepath', '.*') // <--- MAGIC CODE: Izinkan karakter apa aja termasuk slash
    ->name('secure.file');