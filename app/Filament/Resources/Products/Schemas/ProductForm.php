<?php

namespace App\Filament\Resources\Products\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;
use Illuminate\Support\Str;

class ProductForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('category_id')
                    ->relationship('category', 'name')
                    ->label('Category')
                    ->searchable()
                    ->preload(),

                TextInput::make('name')
                    ->required()
                    ->live(onBlur: true)
                    ->afterStateUpdated(fn ($state, callable $set) => $set('slug', Str::slug($state ?? ''))),

                TextInput::make('slug')
                    ->required()
                    ->unique(ignoreRecord: true),

                Textarea::make('description')
                    ->columnSpanFull(),

                Textarea::make('short_description')
                    ->columnSpanFull(),

                TextInput::make('price')
                    ->required()
                    ->numeric()
                    ->prefix('$'),

                TextInput::make('compare_price')
                    ->numeric()
                    ->prefix('$'),

                TextInput::make('sku')
                    ->label('SKU'),

                TextInput::make('stock')
                    ->required()
                    ->numeric()
                    ->default(0),

                Toggle::make('is_active')
                    ->default(true),

                Toggle::make('is_featured')
                    ->default(false),

                Toggle::make('track_stock')
                    ->default(true),

                TextInput::make('weight')
                    ->numeric()
                    ->suffix('kg'),
            ]);
    }
}
