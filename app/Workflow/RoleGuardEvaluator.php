<?php

declare(strict_types=1);

namespace App\Workflow;

use App\Models\User;
use Illuminate\Contracts\Auth\Factory as AuthFactory;
use Laraflow\Contracts\GuardEvaluatorInterface;
use Laraflow\Data\GuardResult;
use Laraflow\Data\Marking;
use Laraflow\Data\Transition;

final readonly class RoleGuardEvaluator implements GuardEvaluatorInterface
{
    public function __construct(private AuthFactory $auth) {}

    public function evaluate(string $expression, Marking $marking, Transition $transition): bool|GuardResult
    {
        if (! str_starts_with($expression, 'role:')) {
            return new GuardResult(allowed: false, code: 'unknown_guard',
                reason: "Unknown guard expression: {$expression}");
        }

        $required = substr($expression, 5);
        $user = $this->auth->guard()->user();

        if (! $user instanceof User) {
            return new GuardResult(allowed: false, code: 'not_authenticated',
                reason: 'You must be signed in to perform this action.');
        }

        if (! $user->hasRole($required)) {
            return new GuardResult(allowed: false, code: 'wrong_role',
                reason: "Requires the {$required} role.");
        }

        return new GuardResult(allowed: true);
    }
}
