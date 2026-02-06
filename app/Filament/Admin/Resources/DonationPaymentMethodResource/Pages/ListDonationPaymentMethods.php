<?php

namespace App\Filament\Admin\Resources\DonationPaymentMethodResource\Pages;

use App\Filament\Admin\Resources\DonationPaymentMethodResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListDonationPaymentMethods extends ListRecords
{
    protected static string $resource = DonationPaymentMethodResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
