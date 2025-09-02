<?php

namespace App\Filament\Resources\TransactionItems\Schemas;

use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class TransactionItemForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('transactionId'),
                TextInput::make('productId'),
                TextInput::make('remoteId'),
                TextInput::make('localId'),
                TextInput::make('label'),
                TextInput::make('quantity')
                    ->required()
                    ->numeric()
                    ->default(1),
                TextInput::make('unitId'),
                TextInput::make('unitPrice')
                    ->numeric(),
                TextInput::make('total')
                    ->numeric(),
                Textarea::make('notes')
                    ->columnSpanFull(),
                DateTimePicker::make('createdAt')
                    ->required(),
                DateTimePicker::make('updatedAt')
                    ->required(),
                DateTimePicker::make('deletedAt'),
                TextInput::make('account'),
                DateTimePicker::make('syncAt'),
                TextInput::make('code'),
                TextInput::make('createdBy'),
                TextInput::make('version')
                    ->required()
                    ->numeric()
                    ->default(0),
                Toggle::make('isDirty')
                    ->required(),
            ]);
    }
}
