<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Laraflow\Data\Marking;
use Laraflow\Eloquent\HasWorkflowTrait;
use Laraflow\Subject\Workflow;

class Order extends Model
{
    use HasWorkflowTrait;

    protected $fillable = [
        'reference',
        'customer_id',
        'item_summary',
        'total',
        'currency',
        'lifecycle',
        'payment',
        'fulfillment_marking',
        'placed_at',
        'shipped_at',
        'delivered_at',
    ];

    protected function casts(): array
    {
        return [
            'total' => 'decimal:2',
            'fulfillment_marking' => 'array',
            'placed_at' => 'datetime',
            'shipped_at' => 'datetime',
            'delivered_at' => 'datetime',
        ];
    }

    /**
     * Default workflow used when callers don't pass a name. The other two are
     * reached via the named helpers below or by passing the workflow name to
     * any HasWorkflowTrait method.
     */
    protected function getDefaultWorkflowName(): string
    {
        return 'order_lifecycle';
    }

    public function lifecycleWorkflow(): Workflow
    {
        return $this->workflow('order_lifecycle');
    }

    public function paymentWorkflow(): Workflow
    {
        return $this->workflow('order_payment');
    }

    public function fulfillmentWorkflow(): Workflow
    {
        return $this->workflow('order_fulfillment');
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'customer_id');
    }

    public function auditLogs(): HasMany
    {
        return $this->hasMany(OrderAuditLog::class)->orderByDesc('occurred_at');
    }

    /**
     * @return array<string>
     */
    public function activePlaces(string $workflowName): array
    {
        return $this->getWorkflowMarking($workflowName)->getActivePlaces();
    }

    public function lifecycleMarking(): Marking
    {
        return $this->getWorkflowMarking('order_lifecycle');
    }

    public function paymentMarking(): Marking
    {
        return $this->getWorkflowMarking('order_payment');
    }

    public function fulfillmentMarkingObj(): Marking
    {
        return $this->getWorkflowMarking('order_fulfillment');
    }

    /**
     * Headline status the dashboard derives by reading all three markings at once.
     */
    protected function status(): Attribute
    {
        return Attribute::make(
            get: function (): string {
                $life = $this->lifecycle ?: 'cart';

                if ($life === 'cancelled') {
                    return 'cancelled';
                }
                if ($this->payment === 'refunded') {
                    return 'refunded';
                }
                if ($life === 'completed') {
                    return 'completed';
                }
                if ($life === 'delivered') {
                    return 'delivered';
                }
                if ($life === 'shipped') {
                    return 'shipped';
                }
                if ($life === 'placed' && $this->fulfillmentMarkingObj()->get('ready') > 0) {
                    return 'ready_to_ship';
                }
                if ($life === 'placed') {
                    return 'in_fulfillment';
                }

                return 'cart';
            },
        );
    }
}
