<?php

namespace App\Filament\Resources\TransactionItems\Pages;

use App\Filament\Resources\TransactionItems\TransactionItemResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditTransactionItem extends EditRecord
{
    protected static string $resource = TransactionItemResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
