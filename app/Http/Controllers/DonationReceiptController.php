<?php

namespace App\Http\Controllers;

use App\Models\Donation;
use App\Models\GeneralSetting; // Asumsi ada setting untuk ambil logo/info organisasi
use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Auth;

class DonationReceiptController extends Controller
{
    public function download(Donation $donation)
    {
        // 1. KEAMANAN: Cek apakah yang download adalah pemilik donasi
        if ($donation->user_id !== Auth::id()) {
            abort(403, 'Unauthorized access.');
        }

        // 2. KEAMANAN: Cek apakah status sudah Approved
        if ($donation->status !== 'approved') {
            abort(403, 'Donation verification pending.');
        }

        // 3. Persiapan Data
        $settings = GeneralSetting::first(); // Ambil data organisasi
        
        // Load View PDF
        $pdf = Pdf::loadView('pdfs.donation-receipt', [
            'donation' => $donation,
            'settings' => $settings,
        ]);

        // Setup Ukuran Kertas (A4 Portrait atau Landscape kwitansi)
        $pdf->setPaper('a4', 'portrait');

        // 4. Download file dengan nama cantik
        $filename = 'WFIEd-Receipt-' . $donation->created_at->format('Ymd') . '-' . $donation->id . '.pdf';
        
        return $pdf->download($filename);
    }
}