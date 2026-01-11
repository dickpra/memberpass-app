<?php

use Illuminate\Support\Facades\Route;
use App\Models\Payment;
/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

Route::get('/', function () {
    return view('welcome');
});
Route::get('/invoice/{payment}', function (Payment $payment) {
    // Keamanan: Pastikan yang buka invoice adalah pemiliknya atau admin
    if (auth()->id() !== $payment->user_id && auth()->user()->role !== 'admin') {
        abort(403);
    }

    $settings = \App\Models\GeneralSetting::first();

    return view('invoice', compact('payment', 'settings'));
})->middleware('auth')->name('invoice.print');