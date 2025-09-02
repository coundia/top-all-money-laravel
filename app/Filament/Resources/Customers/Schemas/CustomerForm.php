<?php

namespace App\Filament\Resources\Customers\Schemas;

use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class CustomerForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('remoteId'),
                TextInput::make('localId'),
                TextInput::make('code'),
                TextInput::make('firstName'),
                TextInput::make('lastName'),
                TextInput::make('fullName'),
                TextInput::make('balance')
                    ->required()
                    ->numeric()
                    ->default(0),
                TextInput::make('balanceDebt')
                    ->required()
                    ->numeric()
                    ->default(0),
                TextInput::make('phone')
                    ->tel(),
                TextInput::make('email')
                    ->label('Email address')
                    ->email(),
                Textarea::make('notes')
                    ->columnSpanFull(),
                TextInput::make('status'),
                TextInput::make('companyId'),
                TextInput::make('addressLine1'),
                TextInput::make('addressLine2'),
                TextInput::make('city'),
                TextInput::make('region'),
                TextInput::make('country'),
                TextInput::make('postalCode'),
                DateTimePicker::make('createdAt')
                    ->required(),
                DateTimePicker::make('updatedAt')
                    ->required(),
                DateTimePicker::make('deletedAt'),
                DateTimePicker::make('syncAt'),
                TextInput::make('createdBy'),
                TextInput::make('account'),
                TextInput::make('version')
                    ->required()
                    ->numeric()
                    ->default(0),
                Toggle::make('isDirty')
                    ->required(),
            ]);
    }
}
