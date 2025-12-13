<?php

namespace App\Filament\Resources\Users\Schemas;

use App\Enums\UserRole;
use Filament\Schemas\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;
use Illuminate\Support\Facades\Hash;

class UserForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Foydalanuvchi ma\'lumotlari')
                    ->description('Foydalanuvchining asosiy ma\'lumotlarini kiriting')
                    ->schema([
                        TextInput::make('name')
                            ->label('Ism familiya')
                            ->required()
                            ->maxLength(255)
                            ->placeholder('Ism familiyani kiriting'),
                        
                        TextInput::make('email')
                            ->label('Email')
                            ->email()
                            ->required()
                            ->unique(ignoreRecord: true)
                            ->maxLength(255)
                            ->placeholder('example@gmail.com'),
                        
                        Select::make('role')
                            ->label('Bo\'lim')
                            ->required()
                            ->options(UserRole::toSelectArray())
                            ->native(false)
                            ->placeholder('Bo\'limni tanlang')
                            ->columnSpanFull(),
                    ])
                    ->columns(2),
                
                Section::make('Xavfsizlik')
                    ->description('Foydalanuvchi paroli')
                    ->schema([
                        TextInput::make('password')
                            ->label('Parol')
                            ->password()
                            ->required(fn (string $context): bool => $context === 'create')
                            ->dehydrateStateUsing(fn ($state) => filled($state) ? Hash::make($state) : null)
                            ->dehydrated(fn ($state) => filled($state))
                            ->revealable()
                            ->maxLength(255)
                            ->placeholder('Parolni kiriting')
                            ->helperText('Kamida 8 ta belgi'),
                        
                        TextInput::make('password_confirmation')
                            ->label('Parolni tasdiqlang')
                            ->password()
                            ->required(fn (string $context): bool => $context === 'create')
                            ->dehydrated(false)
                            ->revealable()
                            ->same('password')
                            ->maxLength(255)
                            ->placeholder('Parolni qayta kiriting'),
                    ])
                    ->columns(2)
                    ->collapsible(),
            ]);
    }
}