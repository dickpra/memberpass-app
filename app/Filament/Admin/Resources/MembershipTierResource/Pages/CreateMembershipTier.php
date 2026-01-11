<?php

namespace App\Filament\Admin\Resources\MembershipTierResource\Pages;

use App\Filament\Admin\Resources\MembershipTierResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateMembershipTier extends CreateRecord
{
    protected static string $resource = MembershipTierResource::class;
}
