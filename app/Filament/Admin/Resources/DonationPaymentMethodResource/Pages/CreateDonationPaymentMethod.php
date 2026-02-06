<?php

namespace App\Filament\Admin\Resources\DonationPaymentMethodResource\Pages;

use App\Filament\Admin\Resources\DonationPaymentMethodResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateDonationPaymentMethod extends CreateRecord
{
    protected static string $resource = DonationPaymentMethodResource::class;
}
