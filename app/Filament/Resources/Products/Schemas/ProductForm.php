<?php

namespace App\Filament\Resources\Products\Schemas;

use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class ProductForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('remoteId'),
                TextInput::make('localId'),
                TextInput::make('code'),
                TextInput::make('account'),
                TextInput::make('name'),
                Textarea::make('description')
                    ->columnSpanFull(),
                TextInput::make('barcode'),
                TextInput::make('unitId'),
                TextInput::make('categoryId'),
                TextInput::make('defaultPrice')
                    ->required()
                    ->numeric()
                    ->default(0),
                Textarea::make('statuses')
                    ->columnSpanFull(),
                TextInput::make('purchasePrice')
                    ->required()
                    ->numeric()
                    ->default(0),
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
