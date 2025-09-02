<?php

namespace App\Filament\Resources\Accounts\Schemas;

use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class AccountForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('remoteId'),
                TextInput::make('localId'),
                TextInput::make('balance')
                    ->required()
                    ->numeric()
                    ->default(0),
                TextInput::make('balance_prev')
                    ->required()
                    ->numeric()
                    ->default(0),
                TextInput::make('balance_blocked')
                    ->required()
                    ->numeric()
                    ->default(0),
                TextInput::make('balance_init')
                    ->required()
                    ->numeric()
                    ->default(0),
                TextInput::make('balance_goal')
                    ->required()
                    ->numeric()
                    ->default(0),
                TextInput::make('balance_limit')
                    ->required()
                    ->numeric()
                    ->default(0),
                TextInput::make('dateStartAccount'),
                TextInput::make('dateEndAccount'),
                TextInput::make('typeAccount'),
                TextInput::make('code'),
                Textarea::make('description')
                    ->columnSpanFull(),
                TextInput::make('status'),
                TextInput::make('currency'),
                Toggle::make('isDefault')
                    ->required(),
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
                Toggle::make('isShared')
                    ->required(),
                TextInput::make('createdBy'),
                Toggle::make('isDirty')
                    ->required(),
            ]);
    }
}
