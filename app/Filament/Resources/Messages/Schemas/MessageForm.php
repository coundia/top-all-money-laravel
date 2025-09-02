<?php

namespace App\Filament\Resources\Messages\Schemas;

use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class MessageForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Textarea::make('content')
                    ->columnSpanFull(),
                Textarea::make('sender')
                    ->columnSpanFull(),
                Textarea::make('status')
                    ->columnSpanFull(),
                TextInput::make('createdBy'),
                Select::make('conversation_id')
                    ->relationship('conversation', 'title'),
                TextInput::make('remoteId'),
                TextInput::make('localId'),
                DateTimePicker::make('syncAt'),
                TextInput::make('version')
                    ->required()
                    ->numeric()
                    ->default(0),
                Toggle::make('isDirty')
                    ->required(),
            ]);
    }
}
