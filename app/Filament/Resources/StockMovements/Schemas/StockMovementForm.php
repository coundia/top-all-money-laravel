<?php

namespace App\Filament\Resources\StockMovements\Schemas;

use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class StockMovementForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('type_stock_movement'),
                TextInput::make('code'),
                TextInput::make('remoteId'),
                TextInput::make('localId'),
                TextInput::make('quantity')
                    ->numeric(),
                TextInput::make('companyId'),
                TextInput::make('productVariantId'),
                TextInput::make('orderLineId'),
                TextInput::make('discriminator'),
                TextInput::make('account'),
                DateTimePicker::make('syncAt'),
                TextInput::make('version')
                    ->required()
                    ->numeric()
                    ->default(0),
                Toggle::make('isDirty')
                    ->required(),
                TextInput::make('createdBy'),
                DateTimePicker::make('createdAt')
                    ->required(),
                DateTimePicker::make('updatedAt')
                    ->required(),
            ]);
    }
}
