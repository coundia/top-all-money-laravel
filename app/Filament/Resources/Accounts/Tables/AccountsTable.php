<?php

namespace App\Filament\Resources\Accounts\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class AccountsTable
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
                TextColumn::make('balance')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('balance_prev')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('balance_blocked')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('balance_init')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('balance_goal')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('balance_limit')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('dateStartAccount')
                    ->searchable(),
                TextColumn::make('dateEndAccount')
                    ->searchable(),
                TextColumn::make('typeAccount')
                    ->searchable(),
                TextColumn::make('code')
                    ->searchable(),
                TextColumn::make('status')
                    ->searchable(),
                TextColumn::make('currency')
                    ->searchable(),
                IconColumn::make('isDefault')
                    ->boolean(),
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
                IconColumn::make('isShared')
                    ->boolean(),
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
