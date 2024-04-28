<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ProductResource\Pages;
use App\Filament\Resources\ProductResource\RelationManagers;
use App\Models\Product;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Forms\Set;
use Illuminate\Support\Str;

class ProductResource extends Resource
{
    protected static ?string $model = Product::class;

    protected static ?string $navigationIcon = 'heroicon-o-squares-2x2';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Group::make([
                    Forms\Components\Section::make('Product Information')->schema([
                        Forms\Components\TextInput::make('name')
                            ->required()
                            ->maxLength(255)
                            ->live(onBlur: true)
                            ->afterStateUpdated(function(string $operation, $state, Set $set){
                                if($operation !== 'create') {
                                    return;
                                }
                                $set('slug', Str::slug($state));
                        }),
                        Forms\Components\TextInput::make('slug')
                            ->required()
                            ->maxLength(255)
                            ->disabled()
                            ->dehydrated()
                            ->unique(Product::class, 'slug', ignoreRecord: true),
                        Forms\Components\MarkdownEditor::make('description')
                            ->columnSpanFull()
                            ->fileAttachmentsDirectory('products')
                    ])->columns(2),
                    Forms\Components\Section::make('images')->schema([
                        Forms\Components\FileUpload::make('images')
                            ->multiple()
                            ->directory('products')
                            ->maxFiles(5)
                            ->reorderable(),
                    ])
                ])->columnSpan(2),
                Forms\Components\Group::make()->schema([
                    Forms\Components\Section::make('Price')->schema([
                        Forms\Components\TextInput::make('price')
                            ->required()
                            ->numeric()
                            ->prefix('৳'),
                    ]),

                    Forms\Components\Section::make('Associations')->schema([
                       Forms\Components\Select::make('category_id')
                           ->required()
                           ->searchable()
                           ->preload()
                           ->relationship('category', 'name'),
                        Forms\Components\Select::make('brand_id')
                            ->required()
                            ->searchable()
                            ->preload()
                            ->relationship('brand', 'name'),
                    ]),

                    Forms\Components\Section::make('Status')->schema([
                        Forms\Components\Toggle::make('in_stock')
                            ->default(true),
                        Forms\Components\Toggle::make('is_active')
                            ->default(true),
                        Forms\Components\Toggle::make('is_featured'),
                        Forms\Components\Toggle::make('on_sale'),
                    ])
                ])->columnSpan(1),
            ])->columns(3);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->searchable(),
                Tables\Columns\TextColumn::make('image'),
                Tables\Columns\TextColumn::make('category.name')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('brand.name')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('price')
                    ->sortable()
                    ->money('BDT'),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('category')
                    ->relationship('category', 'name'),
                Tables\Filters\SelectFilter::make('brand')
                    ->relationship('brand', 'name'),
            ])
            ->actions([
               Tables\Actions\ActionGroup::make([
                   Tables\Actions\ViewAction::make(),
                   Tables\Actions\EditAction::make(),
                   Tables\Actions\DeleteAction::make(),
               ])
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
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
            'index' => Pages\ListProducts::route('/'),
            'create' => Pages\CreateProduct::route('/create'),
            'edit' => Pages\EditProduct::route('/{record}/edit'),
        ];
    }
}
