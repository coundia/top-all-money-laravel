<?php

namespace App\Filament\Resources\TransactionEntries\Pages;

use App\Filament\Resources\TransactionEntries\TransactionEntryResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListTransactionEntries extends ListRecords
{
    protected static string $resource = TransactionEntryResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
