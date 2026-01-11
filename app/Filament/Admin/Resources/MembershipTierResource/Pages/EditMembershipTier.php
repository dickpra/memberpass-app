<?php

namespace App\Filament\Admin\Resources\MembershipTierResource\Pages;

use App\Filament\Admin\Resources\MembershipTierResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditMembershipTier extends EditRecord
{
    protected static string $resource = MembershipTierResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
