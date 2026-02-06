<?php

namespace App\Filament\Admin\Resources\DonationPaymentMethodResource\Pages;

use App\Filament\Admin\Resources\DonationPaymentMethodResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditDonationPaymentMethod extends EditRecord
{
    protected static string $resource = DonationPaymentMethodResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
