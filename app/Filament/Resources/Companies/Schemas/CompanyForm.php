<?php

namespace App\Filament\Resources\Companies\Schemas;

use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class CompanyForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('remoteId'),
                TextInput::make('localId'),
                TextInput::make('code'),
                TextInput::make('name'),
                Textarea::make('description')
                    ->columnSpanFull(),
                TextInput::make('phone')
                    ->tel(),
                TextInput::make('email')
                    ->label('Email address')
                    ->email(),
                TextInput::make('website'),
                TextInput::make('taxId'),
                TextInput::make('currency'),
                TextInput::make('address'),
                TextInput::make('addressLine2'),
                TextInput::make('city'),
                TextInput::make('region'),
                TextInput::make('country'),
                TextInput::make('postalCode'),
                Toggle::make('isDefault')
                    ->required(),
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
