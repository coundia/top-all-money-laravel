<?php

namespace App\Filament\Resources\TransactionEntries\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class TransactionEntriesTable
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
                TextColumn::make('localId')
                    ->searchable(),
                TextColumn::make('code')
                    ->searchable(),
                TextColumn::make('amount')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('typeEntry')
                    ->searchable(),
                TextColumn::make('dateTransaction')
                    ->searchable(),
                TextColumn::make('status')
                    ->searchable(),
                TextColumn::make('entityName')
                    ->searchable(),
                TextColumn::make('entityId')
                    ->searchable(),
                TextColumn::make('accountId')
                    ->searchable(),
                TextColumn::make('categoryId')
                    ->searchable(),
                TextColumn::make('companyId')
                    ->searchable(),
                TextColumn::make('customerId')
                    ->searchable(),
                TextColumn::make('debtId')
                    ->searchable(),
                TextColumn::make('createdAt')
                    ->dateTime()
                    ->sortable(),
                TextColumn::make('updatedAt')
                    ->dateTime()
                    ->sortable(),
                TextColumn::make('deletedAt')
                    ->dateTime()
                    ->sortable(),
                TextColumn::make('syncAt')
                    ->dateTime()
                    ->sortable(),
                TextColumn::make('version')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('createdBy')
                    ->searchable(),
                IconColumn::make('isDirty')
                    ->boolean(),
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
