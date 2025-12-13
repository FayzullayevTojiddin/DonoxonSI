<?php

namespace App\Enums;

enum UserRole: string
{
    case SUPER_ADMIN = 'super_admin';
    case COURT = 'court';
    case INTERNAL_AFFAIRS = 'internal';
    case FOREIGN_AFFAIRS = 'foreign';
    case ENVIRONMENT = 'environment';

    public function label(): string
    {
        return match ($this) {
            self::SUPER_ADMIN => 'Umumiy',
            self::COURT => 'Sud',
            self::INTERNAL_AFFAIRS => 'Ichki ishlar',
            self::FOREIGN_AFFAIRS => 'Tashqi ishlar',
            self::ENVIRONMENT => 'Tabiat atrof-muhit',
        };
    }

    public static function toArray(): array
    {
        return collect(self::cases())
            ->map(fn ($case) => [
                'value' => $case->value,
                'label' => $case->label(),
            ])
            ->toArray();
    }

    public static function toSelectArray(): array
    {
        return collect(self::cases())
            ->mapWithKeys(fn ($case) => [
                $case->value => $case->label(),
            ])
            ->toArray();
    }
}