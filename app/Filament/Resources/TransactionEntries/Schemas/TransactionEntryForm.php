<?php

namespace App\Filament\Resources\TransactionEntries\Schemas;

use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class TransactionEntryForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('remoteId'),
                TextInput::make('localId'),
                TextInput::make('code'),
                Textarea::make('description')
                    ->columnSpanFull(),
                TextInput::make('amount')
                    ->required()
                    ->numeric()
                    ->default(0),
                TextInput::make('typeEntry')
                    ->required()
                    ->default('DEBIT'),
                TextInput::make('dateTransaction'),
                TextInput::make('status'),
                TextInput::make('entityName'),
                TextInput::make('entityId'),
                TextInput::make('accountId'),
                TextInput::make('categoryId'),
                TextInput::make('companyId'),
                TextInput::make('customerId'),
                TextInput::make('debtId'),
                DateTimePicker::make('createdAt')
                    ->required(),
                DateTimePicker::make('updatedAt')
                    ->required(),
                DateTimePicker::make('deletedAt'),
                DateTimePicker::make('syncAt'),
                TextInput::make('version')
                    ->required()
                    ->numeric()
                    ->default(0),
                TextInput::make('createdBy'),
                Toggle::make('isDirty')
                    ->required(),
            ]);
    }
}
