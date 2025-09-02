<?php

namespace App\Filament\Resources\AccountUsers;

use App\Filament\Resources\AccountUsers\Pages\CreateAccountUser;
use App\Filament\Resources\AccountUsers\Pages\EditAccountUser;
use App\Filament\Resources\AccountUsers\Pages\ListAccountUsers;
use App\Filament\Resources\AccountUsers\Schemas\AccountUserForm;
use App\Filament\Resources\AccountUsers\Tables\AccountUsersTable;
use App\Models\AccountUser;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class AccountUserResource extends Resource
{
    protected static ?string $model = AccountUser::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    protected static ?string $recordTitleAttribute = 'accountUsers';

    public static function form(Schema $schema): Schema
    {
        return AccountUserForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return AccountUsersTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListAccountUsers::route('/'),
            'create' => CreateAccountUser::route('/create'),
            'edit' => EditAccountUser::route('/{record}/edit'),
        ];
    }
}
