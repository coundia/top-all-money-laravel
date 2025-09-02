<?php

namespace App\Filament\Resources\Debts\Schemas;

use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class DebtForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('remoteId'),
                TextInput::make('localId'),
                TextInput::make('code'),
                Textarea::make('notes')
                    ->columnSpanFull(),
                TextInput::make('balance')
                    ->required()
                    ->numeric()
                    ->default(0),
                TextInput::make('balanceDebt')
                    ->required()
                    ->numeric()
                    ->default(0),
                TextInput::make('dueDate'),
                Textarea::make('statuses')
                    ->columnSpanFull(),
                TextInput::make('account'),
                TextInput::make('customerId'),
                DateTimePicker::make('createdAt')
                    ->required(),
                DateTimePicker::make('updatedAt')
                    ->required(),
                DateTimePicker::make('deletedAt'),
                DateTimePicker::make('syncAt'),
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
