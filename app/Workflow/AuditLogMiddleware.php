<?php

declare(strict_types=1);

namespace App\Workflow;

use App\Models\Order;
use App\Models\OrderAuditLog;
use Closure;
use Illuminate\Contracts\Auth\Factory as AuthFactory;
use Laraflow\Data\Marking;
use Laraflow\Data\SubjectMiddlewareContext;

final readonly class AuditLogMiddleware
{
    public function __construct(private AuthFactory $auth) {}

    public function __invoke(SubjectMiddlewareContext $context, Closure $next): Marking
    {
        $subject = $context->subject;
        $before = $context->marking->toArray();
        $after = $next();

        if ($subject instanceof Order) {
            OrderAuditLog::query()->create([
                'order_id' => $subject->getKey(),
                'actor_id' => $this->auth->guard()->id(),
                'workflow_name' => $context->workflowName,
                'event' => 'transition',
                'transition' => $context->transition->name,
                'marking_before' => $before,
                'marking_after' => $after->toArray(),
                'reason' => WorkflowReasonContext::pull(),
                'occurred_at' => now(),
            ]);
        }

        return $after;
    }
}
