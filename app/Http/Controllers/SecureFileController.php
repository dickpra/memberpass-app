<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Auth;
use App\Models\Donation;
use App\Models\Payment;

class SecureFileController extends Controller
{
    // Parameter sekarang cuma satu: $filepath (isinya path lengkap)
    public function show($filepath)
    {
        // $filepath isinya misal: "donation-proofs/105-budi/proof-123456.jpg"

        // 1. Cek Apakah File Ada di Disk Secure
        if (!Storage::disk('secure')->exists($filepath)) {
            abort(404);
        }

        // 2. CEK HAK AKSES
        
        // A. Admin Selalu Boleh
        if (Auth::guard('admin')->check()) {
            return $this->serveFile($filepath);
        }

        // B. Member (Cek Kepemilikan)
        if (Auth::guard('web')->check()) {
            $userId = Auth::guard('web')->id();

            // Kita cari di database, apakah ada kolom proof_file yang isinya SAMA PERSIS dengan path ini
            // DAN user_id nya adalah user yang sedang login
            
            $isMyDonation = Donation::where('user_id', $userId)
                ->where('proof_file', $filepath) // Cek full path
                ->exists();

            $isMyPayment = Payment::where('user_id', $userId)
                ->where('proof_file', $filepath)
                ->exists();

            if ($isMyDonation || $isMyPayment) {
                return $this->serveFile($filepath);
            }
        }

        abort(403, 'Unauthorized access.');
    }

    private function serveFile($path)
    {
        $file = Storage::disk('secure')->path($path);
        
        // Tambahkan header agar browser tahu tipe filenya (jpg/png/pdf)
        return response()->file($file);
    }
}