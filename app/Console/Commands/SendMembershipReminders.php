<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;
use Filament\Notifications\Notification;
use Carbon\Carbon;

class SendMembershipReminders extends Command
{
    protected $signature = 'membership:send-reminders';
    protected $description = 'Kirim notifikasi reminder H-3 Bulan';

    public function handle()
    {
        // Target: Member Active yang bukan VIP, dan expired tahun ini
        // H-3 Bulan dari Des 31 adalah = 1 Oktober.
        
        // Kita cek apakah hari ini tanggal 1 Oktober?
        if (Carbon::now()->format('m-d') !== '10-01') {
            $this->info('Hari ini bukan 1 Oktober. Tidak ada reminder dikirim.');
            return;
        }

        $users = User::where('status', 'active')
                     ->where('membership_type', '!=', 'VIP Lifetime')
                     ->get();

        foreach ($users as $user) {
            // Kirim Notifikasi Filament (Atau Email)
            Notification::make()
                ->title('Membership Renewal')
                ->body('Halo! Keanggotaan Anda berakhir 31 Desember. Silakan perpanjang untuk tahun depan.')
                ->warning()
                ->sendToDatabase($user);
                
            // Disini bisa tambah logic kirim Email via Mail::to($user)...
        }
        
        $this->info('Reminder sent.');
    }
}