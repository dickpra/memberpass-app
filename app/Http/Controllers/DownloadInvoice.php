<?php

namespace App\Http\Controllers;

use App\Models\GeneralSetting;
use App\Models\Payment;
use App\Models\BankAccount; // <--- WAJIB IMPORT INI
use Illuminate\Http\Request;

class DownloadInvoice extends Controller
{
    /**
     * Handle the incoming request.
     */
    public function __invoke(Payment $payment)
    {
        // 1. Cek Hak Akses (Security)
        if ($payment->user_id !== auth()->id() && !auth()->user()->hasRole('admin')) {
            abort(403);
        }

        // 2. Ambil Data Setting
        $settings = GeneralSetting::first();
        
        // 3. AMBIL DATA BANK (Inilah yang ketinggalan sebelumnya)
        $banks = BankAccount::where('is_active', true)->get();

        // 4. Kirim ke View
        return view('invoices.default', [
            'payment'  => $payment,
            'settings' => $settings,
            'banks'    => $banks, // <--- PASTIKAN BARIS INI ADA
        ]);
    }
}