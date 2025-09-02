<?php

namespace App\Filament\Resources\StockLevels\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class StockLevelsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('id')
                    ->label('ID')
                    ->searchable(),
                TextColumn::make('remoteId')
                    ->searchable(),
                TextColumn::make('code')
                    ->searchable(),
                TextColumn::make('localId')
                    ->searchable(),
                TextColumn::make('stockOnHand')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('stockAllocated')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('productVariantId')
                    ->searchable(),
                TextColumn::make('companyId')
                    ->searchable(),
                TextColumn::make('syncAt')
                    ->dateTime()
                    ->sortable(),
                TextColumn::make('deletedAt')
                    ->dateTime()
                    ->sortable(),
                TextColumn::make('version')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('account')
                    ->searchable(),
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
