<?php

namespace App\Filament\Resources\AccountUsers\Pages;

use App\Filament\Resources\AccountUsers\AccountUserResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditAccountUser extends EditRecord
{
    protected static string $resource = AccountUserResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
