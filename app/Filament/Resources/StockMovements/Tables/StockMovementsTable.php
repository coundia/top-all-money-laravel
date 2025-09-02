<?php

namespace App\Filament\Resources\StockMovements\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class StockMovementsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('id')
                    ->label('ID')
                    ->searchable(),
                TextColumn::make('type_stock_movement')
                    ->searchable(),
                TextColumn::make('code')
                    ->searchable(),
                TextColumn::make('remoteId')
                    ->searchable(),
                TextColumn::make('localId')
                    ->searchable(),
                TextColumn::make('quantity')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('companyId')
                    ->searchable(),
                TextColumn::make('productVariantId')
                    ->searchable(),
                TextColumn::make('orderLineId')
                    ->searchable(),
                TextColumn::make('discriminator')
                    ->searchable(),
                TextColumn::make('account')
                    ->searchable(),
                TextColumn::make('syncAt')
                    ->dateTime()
                    ->sortable(),
                TextColumn::make('version')
                    ->numeric()
                    ->sortable(),
                IconColumn::make('isDirty')
                    ->boolean(),
                TextColumn::make('createdBy')
                    ->searchable(),
                TextColumn::make('createdAt')
                    ->dateTime()
                    ->sortable(),
                TextColumn::make('updatedAt')
                    ->dateTime()
                    ->sortable(),
            ])
            ->filters([
                //
            ])
            ->recordActions([
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
