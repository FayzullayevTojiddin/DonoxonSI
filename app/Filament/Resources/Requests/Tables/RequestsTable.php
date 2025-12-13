<?php

namespace App\Filament\Resources\Requests\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Components\IconEntry;
use Filament\Schemas\Components\Section;
use App\Enums\UserRole;

class RequestsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('full_name')
                    ->label("F.I.Sh")
                    ->searchable()
                    ->sortable()
                    ->weight('medium'),

                TextColumn::make('where')
                    ->formatStateUsing(fn (string $state): string =>
                        UserRole::tryFrom($state)?->label() ?? $state
                    )
                    ->label('Kimga')
                    ->searchable()
                    ->sortable()
                    ->badge()
                    ->color('warning'),

                TextColumn::make('request')
                    ->label("So'rov matni")
                    ->searchable()
                    ->limit(50)
                    ->wrap()
                    ->tooltip(fn ($record) => $record->request),

                IconColumn::make('readed')
                    ->label('Holati')
                    ->boolean()
                    ->trueIcon('heroicon-o-check-circle')
                    ->falseIcon('heroicon-o-x-circle')
                    ->trueColor('success')
                    ->falseColor('danger')
                    ->sortable(),

                TextColumn::make('details_from.ip')
                    ->label('IP manzil')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('created_at')
                    ->label('Yuborilgan')
                    ->dateTime('d.m.Y H:i')
                    ->sortable()
                    ->toggleable(),

                TextColumn::make('updated_at')
                    ->label("Yangilangan")
                    ->dateTime('d.m.Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                TernaryFilter::make('readed')
                    ->label('Holat')
                    ->placeholder('Hammasi')
                    ->trueLabel("O'qilganlar")
                    ->falseLabel("O'qilmaganlar")
                    ->queries(
                        true: fn ($query) => $query->where('readed', true),
                        false: fn ($query) => $query->where('readed', false),
                    ),
            ])
            ->recordClasses(fn ($record) => !$record->readed ? 'bg-danger-50 dark:bg-danger-950' : null)
            ->recordActions([
                ViewAction::make()
                    ->button()
                    ->label("Ko‘rish")
                    ->icon('heroicon-o-eye')
                    ->modalWidth('3xl')
                    ->infolist([

                        TextEntry::make('full_name')
                            ->label("F.I.Sh"),

                        TextEntry::make('request')
                            ->label("So‘rov")
                            ->columnSpanFull(),

                        IconEntry::make('readed')
                            ->label("O‘qilgan")
                            ->boolean(),

                        Section::make("Yuborilgan ma’lumotlar")
                            ->schema([

                                TextEntry::make('where')
                                    ->label('Kimga')
                                    ->badge()
                                    ->color('warning')
                                    ->formatStateUsing(fn (string $state): string =>
                                        UserRole::tryFrom($state)?->label() ?? $state
                                    ),

                                TextEntry::make('details_from.phone_number')
                                    ->label("Telefon raqam"),

                                TextEntry::make('details_from.ip')
                                    ->label("IP manzil")
                                    ->copyable(),

                                TextEntry::make('details_from.submitted_at')
                                    ->label("Yuborilgan vaqt")
                                    ->dateTime('d.m.Y H:i'),

                                TextEntry::make('details_from.user_agent')
                                    ->label("Brauzer ma’lumoti")
                                    ->columnSpanFull()
                                    ->wrap(),
                            ])
                            ->columns(2),
                    ])

                    ->mountUsing(function ($record) {
                        if (! $record->readed) {
                            $record->update(['readed' => true]);
                        }
                    }),
            ])
            ->bulkActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make()
                        ->label("O'chirish"),
                ]),
            ])
            ->emptyStateHeading("So'rovlar topilmadi")
            ->emptyStateDescription("Hozircha hech qanday so'rov yo'q.")
            ->emptyStateIcon('heroicon-o-chat-bubble-left-right');
    }
}