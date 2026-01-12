<?php

namespace App\Filament\Admin\Widgets;

use App\Models\Payment;
use App\Models\User;
use App\Models\GeneralSetting;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Carbon\Carbon;

class StatsOverview extends BaseWidget
{
    protected static ?int $sort = 1; 
    
    // --- PERBAIKAN DI SINI (Tambahkan 'static') ---
    protected static ?string $pollingInterval = '30s'; 

    protected function getStats(): array
    {
        $settings = GeneralSetting::first();
        $currency = $settings->currency ?? 'IDR';

        // 1. Hitung Member Aktif
        $activeMembers = User::where('status', 'active')->count();
        $newMembersThisMonth = User::where('status', 'active')
            ->whereMonth('created_at', Carbon::now()->month)
            ->count();

        // 2. Hitung Omset (Hanya yang Approved)
        $revenue = Payment::where('status', 'approved')->sum('amount');
        
        // 3. Hitung Pending Payment
        $pendingPayments = Payment::where('status', 'waiting_verification')->count();

        return [
            Stat::make('Total Active Members', $activeMembers)
                ->description("+$newMembersThisMonth bulan ini")
                ->descriptionIcon('heroicon-m-user-group')
                ->chart([7, 2, 10, 3, 15, 4, 17]) 
                ->color('success'),

            Stat::make('Total Revenue', "$currency " . number_format($revenue))
                ->description('Total pemasukan bersih')
                ->descriptionIcon('heroicon-m-banknotes')
                ->color('primary'),

            Stat::make('Butuh Verifikasi', $pendingPayments)
                ->description('Menunggu persetujuan admin')
                ->descriptionIcon('heroicon-m-clock')
                ->color($pendingPayments > 0 ? 'danger' : 'gray'),
        ];
    }
}