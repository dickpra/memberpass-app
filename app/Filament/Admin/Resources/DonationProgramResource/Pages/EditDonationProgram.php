<?php

namespace App\Filament\Admin\Resources\DonationProgramResource\Pages;

use App\Filament\Admin\Resources\DonationProgramResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditDonationProgram extends EditRecord
{
    protected static string $resource = DonationProgramResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
