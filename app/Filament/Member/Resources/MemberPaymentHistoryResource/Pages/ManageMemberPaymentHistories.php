<?php

namespace App\Filament\Member\Resources\MemberPaymentHistoryResource\Pages;

use App\Filament\Member\Resources\MemberPaymentHistoryResource;
use Filament\Actions;
use Filament\Resources\Pages\ManageRecords;

class ManageMemberPaymentHistories extends ManageRecords
{
    protected static string $resource = MemberPaymentHistoryResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
