<?php

namespace App\Filament\Resources;

use App\Filament\Resources\OrderResource\Pages;
use App\Filament\Resources\OrderResource\RelationManagers;
use App\Models\Order;

use App\Models\Product;
use Filament\Actions\ActionGroup;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Forms\Set;
use Filament\Forms\Get;
use Illuminate\Support\Number;

class OrderResource extends Resource
{
    protected static ?string $model = Order::class;

    protected static ?string $navigationIcon = 'heroicon-o-shopping-bag';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Group::make()
                    ->schema([
                        Forms\Components\Section::make('Order Information')
                            ->schema([
                                Forms\Components\Select::make('user_id')
                                    ->relationship('user', 'name')
                                    ->label('Customer')
                                    ->required()
                                    ->searchable()
                                    ->preload(),

                                Forms\Components\Select::make('payment_method')
                                    ->options([
                                        'stripe' => 'Stripe',
                                        'paypal' => 'PayPal',
                                        'cash' => 'Cash on Delivery',
                                        'cod' => 'Cash on Pick-up',
                                        'bkash' => 'Bkash',
                                        'nagad' => 'Nagad',
                                        'rocket' => 'Rocket',
                                        'bank' => 'Bank Transfer',
                                        'other' => 'Other',
                                    ]),

                                Forms\Components\Select::make('payment_status')
                                    ->options([
                                        'pending' => 'Pending',
                                        'paid' => 'Paid',
                                        'failed' => 'Failed',
                                    ])->default('pending')
                                    ->required(),

                                Forms\Components\ToggleButtons::make('status')
                                    ->inline()
                                    ->required()
                                    ->options([
                                        'new' => 'New',
                                        'processing' => 'Processing',
                                        'shipped' => 'Shipped',
                                        'completed' => 'Completed',
                                        'canceled' => 'Canceled',
                                    ])->colors([
                                        'new' => 'info',
                                        'processing' => 'warning',
                                        'shipped' => 'success',
                                        'completed' => 'success',
                                        'canceled' => 'danger',
                                    ])
                                    ->icons([
                                        'new' => 'heroicon-m-sparkles',
                                        'processing' => 'heroicon-m-cog',
                                        'shipped' => 'heroicon-m-truck',
                                        'completed' => 'heroicon-m-check-circle',
                                        'canceled' => 'heroicon-m-x-circle',
                                    ])
                                    ->default('new'),

                                Forms\Components\Select::make('currency')
                                    ->options([
                                        'bdt' => 'BDT',
                                        'usd' => 'USD',
                                        'inr' => 'INR',
                                        'npr' => 'NPR',
                                        'pkr' => 'PKR',
                                        'lkr' => 'LKR',
                                    ])->default('bdt')
                                    ->required(),

                                Forms\Components\Select::make('shipping_method')
                                    ->options([
                                        'sdb' => 'Sundorban Delivery',
                                        'local' => 'Local Delivery',
                                        'express' => 'Express Delivery',
                                        'other' => 'Other',
                                    ]),

                                Forms\Components\Textarea::make('notes')
                                    ->columnSpanFull(),
                            ])->columns(2),

                        Forms\Components\Section::make('Order Items')
                            ->schema([
                                Forms\Components\Repeater::make('items')
                                    ->relationship()
                                    ->schema([
                                        Forms\Components\Select::make('product_id')
                                            ->relationship('product', 'name')
                                            ->searchable()
                                            ->preload()
                                            ->required()
                                            ->distinct()
                                            ->columnSpan(4)
                                            ->disableOptionsWhenSelectedInSiblingRepeaterItems()
                                            ->reactive()
                                            ->afterStateUpdated(fn($state, Set $set) => $set('unit_amount', Product::find($state)?->price ?? 0))
                                            ->afterStateUpdated(fn($state, Set $set) => $set('total_amount', Product::find($state)?->price ?? 0)),

                                        Forms\Components\TextInput::make('quantity')
                                            ->numeric()
                                            ->default(1)
                                            ->required()
                                            ->columnSpan(2)
                                            ->minValue(1)
                                            ->reactive()
                                            ->afterStateUpdated(fn($state, Set $set, Get $get) => $set('total_amount', $state * $get('unit_amount'))),

                                        Forms\Components\TextInput::make('unit_amount')
                                            ->numeric()
                                            ->required()
                                            ->columnSpan(3)
                                            ->dehydrated()
                                            ->disabled(),
                                        Forms\Components\TextInput::make('total_amount')
                                            ->numeric()
                                            ->columnSpan(3)
                                            ->dehydrated()
                                            ->required(),
                                    ])->columns(12),
                                Forms\Components\Placeholder::make('grand_total_placeholder')
                                    ->label('Grand Total')
                                    ->content(function (Get $get, Set $set) {
                                        $total = 0;
                                        if (!$repeaters = $get('items')) {
                                            return $total;
                                        }

                                        foreach ($repeaters as $key => $repeater) {
                                            $total += $get("items.{$key}.total_amount");
                                        }
                                        return Number::currency($total, 'bdt');
                                    }),
                                Forms\Components\Hidden::make('grans_total')
                                    ->default(0),
                            ])

                    ])->columnSpanFull()
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('user.name')
                    ->label('Customer')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('grand_total')
                    ->numeric()
                    ->sortable()
                    ->money('bdt'),

                Tables\Columns\TextColumn::make('payment_method')
                    ->sortable()
                    ->searchable(),

                Tables\Columns\TextColumn::make('payment_status')
                    ->sortable()
                    ->searchable(),

                Tables\Columns\TextColumn::make('currency')
                    ->sortable()
                    ->searchable(),

                Tables\Columns\TextColumn::make('shipping_method')
                    ->sortable()
                    ->searchable(),

                Tables\Columns\SelectColumn::make('status')
                    ->sortable()
                    ->searchable()
                    ->options([
                        'new' => 'New',
                        'processing' => 'Processing',
                        'shipped' => 'Shipped',
                        'completed' => 'Completed',
                        'canceled' => 'Canceled',
                    ])
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\ActionGroup::make([
                   Tables\Actions\ViewAction::make(),
                   Tables\Actions\EditAction::make(),
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
            'index' => Pages\ListOrders::route('/'),
            'create' => Pages\CreateOrder::route('/create'),
            'view' => Pages\ViewOrder::route('/{record}'),
            'edit' => Pages\EditOrder::route('/{record}/edit'),
        ];
    }
}
