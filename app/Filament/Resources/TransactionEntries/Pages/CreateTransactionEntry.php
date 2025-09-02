<?php

namespace App\Filament\Resources\TransactionEntries\Pages;

use App\Filament\Resources\TransactionEntries\TransactionEntryResource;
use Filament\Resources\Pages\CreateRecord;

class CreateTransactionEntry extends CreateRecord
{
    protected static string $resource = TransactionEntryResource::class;
}
