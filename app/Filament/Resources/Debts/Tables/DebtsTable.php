<?php

namespace App\Filament\Resources\Debts\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class DebtsTable
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
                TextColumn::make('balance')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('balanceDebt')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('dueDate')
                    ->searchable(),
                TextColumn::make('account')
                    ->searchable(),
                TextColumn::make('customerId')
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
                TextColumn::make('createdBy')
                    ->searchable(),
                TextColumn::make('version')
                    ->numeric()
                    ->sortable(),
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
