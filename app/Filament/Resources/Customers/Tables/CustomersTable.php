<?php

namespace App\Filament\Resources\Customers\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class CustomersTable
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
                TextColumn::make('firstName')
                    ->searchable(),
                TextColumn::make('lastName')
                    ->searchable(),
                TextColumn::make('fullName')
                    ->searchable(),
                TextColumn::make('balance')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('balanceDebt')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('phone')
                    ->searchable(),
                TextColumn::make('email')
                    ->label('Email address')
                    ->searchable(),
                TextColumn::make('status')
                    ->searchable(),
                TextColumn::make('companyId')
                    ->searchable(),
                TextColumn::make('addressLine1')
                    ->searchable(),
                TextColumn::make('addressLine2')
                    ->searchable(),
                TextColumn::make('city')
                    ->searchable(),
                TextColumn::make('region')
                    ->searchable(),
                TextColumn::make('country')
                    ->searchable(),
                TextColumn::make('postalCode')
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
                TextColumn::make('account')
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
