<?php

declare(strict_types=1);

namespace App\Enums;

enum Role: string
{
    case Customer = 'customer';
    case Warehouse = 'warehouse';
    case Finance = 'finance';
    case Manager = 'manager';

    public function label(): string
    {
        return match ($this) {
            self::Customer => 'Customer',
            self::Warehouse => 'Warehouse',
            self::Finance => 'Finance',
            self::Manager => 'Manager',
        };
    }

    public function badgeClasses(): string
    {
        return match ($this) {
            self::Customer => 'bg-zinc-100 text-zinc-700 ring-zinc-200',
            self::Warehouse => 'bg-orange-50 text-orange-700 ring-orange-200',
            self::Finance => 'bg-emerald-50 text-emerald-700 ring-emerald-200',
            self::Manager => 'bg-violet-50 text-violet-700 ring-violet-200',
        };
    }
}
