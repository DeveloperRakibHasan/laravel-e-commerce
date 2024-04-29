<?php

namespace App\Filament\Widgets;

use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;

use App\Filament\Resources\OrderResource;

class LatestOrders extends BaseWidget
{
    protected int | string | array $columnSpan = 'full';
    public function table(Table $table): Table
    {
        return $table
            ->query(OrderResource::getEloquentQuery())
            ->defaultPaginationPageOption(5)
            ->defaultSort('created_at', 'desc')
            ->columns([
                Tables\Columns\TextColumn::make('id')
                    ->label('Order ID')
                    ->searchable(),
                Tables\Columns\TextColumn::make('status')
                    ->badge()
                    ->color(fn(string $state):string => match ($state) {
                        'new' => 'info',
                        'processing' => 'warning',
                        'shipped' => 'success',
                        'completed' => 'success',
                        'canceled' => 'danger',
                    })
                    ->icon(fn(string $state):string => match ($state) {
                        'new' => 'heroicon-m-sparkles',
                        'processing' => 'heroicon-m-cog',
                        'shipped' => 'heroicon-m-truck',
                        'completed' => 'heroicon-m-check-badge',
                        'canceled' => 'heroicon-m-x-circle',
                    }),

                Tables\Columns\TextColumn::make('payment_method')
                    ->sortable()
                    ->searchable(),

                Tables\Columns\TextColumn::make('payment_status')
                    ->sortable()
                    ->badge()
                    ->color('warning')
                    ->searchable(),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Order Date')
                    ->dateTime('d-M-Y'),
            ]);
    }
}
