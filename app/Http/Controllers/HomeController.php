<?php

namespace App\Http\Controllers;

use App\Models\GeneralSetting;
use App\Models\MembershipTier;
use App\Models\User;
use Illuminate\Http\Request;
use Carbon\Carbon;



class HomeController extends Controller
{
    public function index()
    {
        // 1. Ambil Pengaturan CMS
        $settings = GeneralSetting::first();

        // 2. Ambil Paket Membership (Kecuali yang Invitation Only / Hidden)
        $tiers = MembershipTier::where('is_active', true)
            ->where('is_invitation_only', false)
            ->get();

        // 3. (Opsional) Social Proof: Hitung total member aktif
        $totalMembers = User::where('status', 'active')->count() + 150; // +150 angka pemanis awal

        return view('welcome', compact('settings', 'tiers', 'totalMembers'));
    }

    public function checkMember(Request $request)
    {
        $search = $request->input('search');
        $member = null;
        $status = null;
        $message = null;

        if ($search) {
            // 1. Cari Member (ID atau Email)
            $member = User::where('member_id', $search)
                        ->orWhere('email', $search)
                        ->first();

            if ($member) {
                // 2. Tentukan Status Real-time
                $now = Carbon::now();
                
                // Cek Expired (Kecuali VIP Lifetime)
                $isExpired = false;
                if ($member->membership_type !== 'VIP Lifetime' && $member->expiry_date) {
                    $isExpired = $now->greaterThan(Carbon::parse($member->expiry_date));
                }

                // Logic Status Akhir
                if ($member->status === 'active' && !$isExpired) {
                    $status = 'ACTIVE';
                } elseif ($member->status === 'banned') {
                    $status = 'BANNED';
                } else {
                    $status = 'INACTIVE / EXPIRED';
                }
            } else {
                $message = "Member tidak ditemukan. Periksa kembali ID atau Email.";
            }
        }

        return view('check-member', compact('member', 'status', 'message', 'search'));
    }
    
}