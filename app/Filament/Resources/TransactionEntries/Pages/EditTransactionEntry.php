<?php

namespace App\Filament\Resources\TransactionEntries\Pages;

use App\Filament\Resources\TransactionEntries\TransactionEntryResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditTransactionEntry extends EditRecord
{
    protected static string $resource = TransactionEntryResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
