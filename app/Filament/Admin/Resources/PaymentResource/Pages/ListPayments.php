<?php

namespace App\Filament\Admin\Resources\PaymentResource\Pages;

use App\Filament\Admin\Resources\PaymentResource;
use Filament\Actions;
use Filament\Resources\Components\Tab; // PENTING: Import ini
use Filament\Resources\Pages\ListRecords;
use Illuminate\Database\Eloquent\Builder;

class ListPayments extends ListRecords
{
    protected static string $resource = PaymentResource::class;

    protected function getHeaderActions(): array
    {
        return [
            // Actions\CreateAction::make(),
        ];
    }

    // --- LOGIKA TABS FILTER ---
    public function getTabs(): array
    {
        return [
            'all' => Tab::make('All Payments'),
            
            'waiting' => Tab::make('Needs Verification')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('status', 'waiting_verification'))
                ->badge(PaymentResource::getModel()::where('status', 'waiting_verification')->count())
                ->badgeColor('warning'),

            // --- TAMBAHAN BARU: TAB CANCELLATION REQUEST ---
            // Logikanya: Cari payment yang User-nya berstatus 'cancellation_requested'
            'cancellation_requests' => Tab::make('Cancellation Requests')
                ->modifyQueryUsing(fn (Builder $query) => $query->whereHas('user', function ($q) {
                    $q->where('status', 'cancellation_requested');
                }))
                ->badgeColor('danger'), // Warna merah biar kelihatan urgent
            // ------------------------------------------------

            'approved' => Tab::make('Approved')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('status', 'approved')),

            'rejected' => Tab::make('Rejected')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('status', 'rejected')),
        ];
    }
    
    // Opsional: Set default tab yang aktif saat dibuka pertama kali
    public function getDefaultActiveTab(): string | int | null
    {
        return 'waiting';
    }
}