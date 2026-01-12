<?php

namespace App\Http\Controllers;

use App\Models\GeneralSetting;
use App\Models\MembershipTier;
use App\Models\User;
use Illuminate\Http\Request;

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
}