<?php

namespace App\Filament\Resources\AccountUsers\Pages;

use App\Filament\Resources\AccountUsers\AccountUserResource;
use Filament\Resources\Pages\CreateRecord;

class CreateAccountUser extends CreateRecord
{
    protected static string $resource = AccountUserResource::class;
}
