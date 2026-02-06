<?php

namespace App\Filament\Admin\Resources\DonationProgramResource\Pages;

use App\Filament\Admin\Resources\DonationProgramResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Filament\Resources\Components\Tab; // PENTING: Import ini
use Illuminate\Database\Eloquent\Builder;

class ListDonationPrograms extends ListRecords
{
    protected static string $resource = DonationProgramResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }

    public function getTabs(): array
    {
        return [
            // TAB 1: YANG AKTIF
            'active' => Tab::make('Active (Live)')
                ->icon('heroicon-m-check-circle')
                ->modifyQueryUsing(fn (Builder $query) => $query->withoutTrashed())
                ->badge(fn () => $this->getModel()::count()) // Hitung jumlah aktif
                ->badgeColor('success'),

            // TAB 2: SAMPAH
            'trash' => Tab::make('Recycle Bin (Deleted)')
                ->icon('heroicon-m-trash')
                ->modifyQueryUsing(fn (Builder $query) => $query->onlyTrashed())
                ->badge(fn () => $this->getModel()::onlyTrashed()->count())
                ->badgeColor('danger'),
        ];
    }
}
