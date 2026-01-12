<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;
use Carbon\Carbon;

class CheckExpiredMembers extends Command
{
    // Nama perintah terminal
    protected $signature = 'membership:check-expired';
    
    // Deskripsi perintah
    protected $description = 'Cek member yang masa aktifnya habis dan ubah status jadi inactive';

    public function handle()
    {
        $today = Carbon::now();

        // 1. CARI MEMBER YANG:
        // - Statusnya masih 'active'
        // - DAN Tanggal expired-nya kurang dari hari ini (Masa lalu)
        // - DAN BUKAN VIP (Opsional: Jika VIP mau di-handle script auto-renew terpisah)
        //   Tapi karena VIP juga punya expired date, amannya biarkan expired dulu, 
        //   nanti script auto-renew VIP yang akan menghidupkannya lagi di detik berikutnya.
        
        $expiredUsers = User::where('status', 'active')
                            ->whereDate('expiry_date', '<', $today)
                            ->get();

        $count = 0;

        foreach ($expiredUsers as $user) {
            // Ubah jadi inactive (Mati, tapi bisa diperpanjang user)
            $user->update(['status' => 'inactive']);
            
            $this->info("Member Expired: {$user->name} ({$user->member_id})");
            $count++;
        }

        if ($count > 0) {
            $this->info("Selesai! Berhasil menonaktifkan {$count} member yang expired.");
        } else {
            $this->info("Tidak ada member yang expired hari ini.");
        }
    }
}