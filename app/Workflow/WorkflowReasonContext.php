<?php

declare(strict_types=1);

namespace App\Workflow;

final class WorkflowReasonContext
{
    private static ?string $reason = null;

    public static function set(?string $reason): void
    {
        self::$reason = $reason;
    }

    public static function pull(): ?string
    {
        $value = self::$reason;
        self::$reason = null;

        return $value;
    }
}
