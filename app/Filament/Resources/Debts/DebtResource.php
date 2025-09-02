<?php

namespace App\Filament\Resources\Debts;

use App\Filament\Resources\Debts\Pages\CreateDebt;
use App\Filament\Resources\Debts\Pages\EditDebt;
use App\Filament\Resources\Debts\Pages\ListDebts;
use App\Filament\Resources\Debts\Schemas\DebtForm;
use App\Filament\Resources\Debts\Tables\DebtsTable;
use App\Models\Debt;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class DebtResource extends Resource
{
    protected static ?string $model = Debt::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    protected static ?string $recordTitleAttribute = 'debt';

    public static function form(Schema $schema): Schema
    {
        return DebtForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return DebtsTable::configure($table);
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
            'index' => ListDebts::route('/'),
            'create' => CreateDebt::route('/create'),
            'edit' => EditDebt::route('/{record}/edit'),
        ];
    }
}
