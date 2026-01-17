<?php

namespace App\Filament\Admin\Widgets;

use App\Models\Payment;
use App\Models\User;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Carbon\Carbon;

class StatsOverview extends BaseWidget
{
    protected static ?int $sort = 1; 
    protected static ?string $pollingInterval = '30s'; 

    protected function getStats(): array
    {
        // 1. Hitung Member Aktif
        $activeMembers = User::where('status', 'active')->count();
        $newMembersThisMonth = User::where('status', 'active')
            ->whereMonth('created_at', Carbon::now()->month)
            ->whereYear('created_at', Carbon::now()->year) // Tambah cek tahun biar akurat
            ->count();

        // 2. Hitung Omset (DUAL CURRENCY LOGIC)
        // Kita hitung terpisah agar matematikanya benar
        $revenueIDR = Payment::where('status', 'approved')
                        ->where('currency', 'IDR')
                        ->sum('amount');

        $revenueUSD = Payment::where('status', 'approved')
                        ->where('currency', 'USD')
                        ->sum('amount');

        // Format Tampilan String (Gabungkan IDR + USD)
        // Contoh Output: "IDR 5.000.000 + $ 150.00"
        $revenueDisplay = 'IDR ' . number_format($revenueIDR, 0, ',', '.');
        
        // Hanya tambahkan USD jika ada pemasukan Dollar
        if ($revenueUSD > 0) {
            $revenueDisplay .= ' + $ ' . number_format($revenueUSD, 2);
        }

        // 3. Hitung Pending Payment (Perlu Verifikasi)
        $pendingPayments = Payment::where('status', 'waiting_verification')->count();

        return [
            // KARTU 1: MEMBER
            Stat::make('Total Active Members', $activeMembers)
                ->description("+$newMembersThisMonth di bulan ini")
                ->descriptionIcon('heroicon-m-user-group')
                ->chart([7, 2, 10, 3, 15, 4, 17]) // Chart dummy (kosmetik)
                ->color('success'),

            // KARTU 2: REVENUE (DUAL CURRENCY)
            Stat::make('Total Revenue (Approved)', $revenueDisplay)
                ->description('Total akumulasi IDR & USD')
                ->descriptionIcon('heroicon-m-banknotes')
                ->color('primary'),

            // KARTU 3: PENDING
            Stat::make('Butuh Verifikasi', $pendingPayments)
                ->description($pendingPayments > 0 ? 'Segera proses!' : 'Semua aman')
                ->descriptionIcon('heroicon-m-clock')
                ->color($pendingPayments > 0 ? 'danger' : 'gray'),
        ];
    }
}