<?php

namespace App\Filament\Resources\AccountUsers\Schemas;

use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class AccountUserForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('code'),
                TextInput::make('account'),
                TextInput::make('user'),
                TextInput::make('email')
                    ->label('Email address')
                    ->email(),
                TextInput::make('phone')
                    ->tel(),
                TextInput::make('identify'),
                TextInput::make('role'),
                TextInput::make('status'),
                TextInput::make('invitedBy'),
                DateTimePicker::make('invitedAt')
                    ->required(),
                DateTimePicker::make('acceptedAt'),
                DateTimePicker::make('revokedAt'),
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
                Toggle::make('isDirty')
                    ->required(),
                TextInput::make('remoteId'),
                TextInput::make('createdBy'),
                TextInput::make('localId'),
            ]);
    }
}
