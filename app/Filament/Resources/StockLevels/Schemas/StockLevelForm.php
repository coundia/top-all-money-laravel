<?php

namespace App\Filament\Resources\StockLevels\Schemas;

use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class StockLevelForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('remoteId'),
                TextInput::make('code'),
                TextInput::make('localId'),
                TextInput::make('stockOnHand')
                    ->numeric(),
                TextInput::make('stockAllocated')
                    ->numeric(),
                TextInput::make('productVariantId')
                    ->required(),
                TextInput::make('companyId')
                    ->required(),
                DateTimePicker::make('syncAt'),
                DateTimePicker::make('deletedAt'),
                TextInput::make('version')
                    ->required()
                    ->numeric()
                    ->default(0),
                TextInput::make('account'),
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
