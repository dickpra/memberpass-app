<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;
use App\Models\Payment;
use Carbon\Carbon;

class AutoRenewVipMembers extends Command
{
    // Nama perintah terminal
    protected $signature = 'membership:renew-vip';
    protected $description = 'Otomatis perpanjang member VIP Lifetime di akhir tahun';

    public function handle()
    {
        // 1. Cari semua member dengan tier VIP Lifetime (sesuaikan string namanya)
        // Dan statusnya active
        $vipUsers = User::where('membership_type', 'VIP Lifetime') 
                       ->where('status', 'active')
                       ->get();

        $count = 0;
        $nextYear = Carbon::now()->addYear()->year;
        $expiryDate = Carbon::create($nextYear, 12, 31, 23, 59, 59);

        foreach ($vipUsers as $user) {
            // 2. Buat Invoice Otomatis (Gratis / Rp 0)
            Payment::create([
                'user_id' => $user->id,
                'amount' => 0,
                'currency' => 'IDR',
                'type' => 'renewal_auto', // Tanda bahwa ini otomatis
                'status' => 'approved', // Langsung lunas
                'sender_name' => 'SYSTEM AUTO RENEW',
                'admin_note' => "Perpanjangan Otomatis VIP untuk tahun $nextYear",
            ]);

            // 3. Update Expired Date User ke 31 Des Tahun Depan
            $user->update([
                'expiry_date' => $expiryDate
            ]);

            $count++;
            $this->info("Renewed VIP: {$user->name}");
        }

        $this->info("Selesai! Berhasil memperpanjang {$count} member VIP.");
    }
}