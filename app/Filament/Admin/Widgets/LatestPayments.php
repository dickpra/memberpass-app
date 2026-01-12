<?php

namespace App\Filament\Admin\Widgets;

use App\Models\Payment;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;

class LatestPayments extends BaseWidget
{
    protected static ?int $sort = 3; // Urutan ketiga
    protected int | string | array $columnSpan = 'full'; // Lebar penuh
    protected static ?string $heading = 'Transaksi Terbaru';

    public function table(Table $table): Table
    {
        return $table
            ->query(
                // Ambil 5 payment terakhir
                Payment::query()->latest()->limit(5)
            )
            ->columns([
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Tanggal')
                    ->date('d M Y H:i')
                    ->sortable(),
                
                Tables\Columns\TextColumn::make('user.name')
                    ->label('Member')
                    ->weight('bold'),
                
                Tables\Columns\TextColumn::make('amount')
                    ->money('IDR')
                    ->label('Jumlah'),

                Tables\Columns\TextColumn::make('status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'approved' => 'success',
                        'waiting_verification' => 'warning',
                        'rejected' => 'danger',
                        default => 'gray',
                    }),
            ])
            ->actions([
                // Tombol pintas untuk lihat detail payment
                Tables\Actions\Action::make('view')
                    ->label('Proses')
                    ->url(fn (Payment $record): string => route('filament.admin.resources.payments.edit', $record))
                    ->icon('heroicon-m-eye'),
            ]);
    }
}