<?php

namespace App\Filament\Resources\StockLevels;

use App\Filament\Resources\StockLevels\Pages\CreateStockLevel;
use App\Filament\Resources\StockLevels\Pages\EditStockLevel;
use App\Filament\Resources\StockLevels\Pages\ListStockLevels;
use App\Filament\Resources\StockLevels\Schemas\StockLevelForm;
use App\Filament\Resources\StockLevels\Tables\StockLevelsTable;
use App\Models\StockLevel;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class StockLevelResource extends Resource
{
    protected static ?string $model = StockLevel::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    protected static ?string $recordTitleAttribute = 'stock';

    public static function form(Schema $schema): Schema
    {
        return StockLevelForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return StockLevelsTable::configure($table);
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
            'index' => ListStockLevels::route('/'),
            'create' => CreateStockLevel::route('/create'),
            'edit' => EditStockLevel::route('/{record}/edit'),
        ];
    }
}
