<?php

namespace App\Filament\Resources\TransactionEntries;

use App\Filament\Resources\TransactionEntries\Pages\CreateTransactionEntry;
use App\Filament\Resources\TransactionEntries\Pages\EditTransactionEntry;
use App\Filament\Resources\TransactionEntries\Pages\ListTransactionEntries;
use App\Filament\Resources\TransactionEntries\Schemas\TransactionEntryForm;
use App\Filament\Resources\TransactionEntries\Tables\TransactionEntriesTable;
use App\Models\TransactionEntry;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class TransactionEntryResource extends Resource
{
    protected static ?string $model = TransactionEntry::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    protected static ?string $recordTitleAttribute = 'transactions';

    public static function form(Schema $schema): Schema
    {
        return TransactionEntryForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return TransactionEntriesTable::configure($table);
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
            'index' => ListTransactionEntries::route('/'),
            'create' => CreateTransactionEntry::route('/create'),
            'edit' => EditTransactionEntry::route('/{record}/edit'),
        ];
    }
}
