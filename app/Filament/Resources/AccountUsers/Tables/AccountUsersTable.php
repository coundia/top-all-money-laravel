<?php

namespace App\Filament\Resources\AccountUsers\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class AccountUsersTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('id')
                    ->label('ID')
                    ->searchable(),
                TextColumn::make('code')
                    ->searchable(),
                TextColumn::make('account')
                    ->searchable(),
                TextColumn::make('user')
                    ->searchable(),
                TextColumn::make('email')
                    ->label('Email address')
                    ->searchable(),
                TextColumn::make('phone')
                    ->searchable(),
                TextColumn::make('identify')
                    ->searchable(),
                TextColumn::make('role')
                    ->searchable(),
                TextColumn::make('status')
                    ->searchable(),
                TextColumn::make('invitedBy')
                    ->searchable(),
                TextColumn::make('invitedAt')
                    ->dateTime()
                    ->sortable(),
                TextColumn::make('acceptedAt')
                    ->dateTime()
                    ->sortable(),
                TextColumn::make('revokedAt')
                    ->dateTime()
                    ->sortable(),
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
                IconColumn::make('isDirty')
                    ->boolean(),
                TextColumn::make('remoteId')
                    ->searchable(),
                TextColumn::make('createdBy')
                    ->searchable(),
                TextColumn::make('localId')
                    ->searchable(),
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
