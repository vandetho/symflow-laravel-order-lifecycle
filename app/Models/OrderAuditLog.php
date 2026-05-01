<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OrderAuditLog extends Model
{
    protected $fillable = [
        'order_id',
        'actor_id',
        'workflow_name',
        'event',
        'transition',
        'marking_before',
        'marking_after',
        'reason',
        'occurred_at',
    ];

    protected function casts(): array
    {
        return [
            'marking_before' => 'array',
            'marking_after' => 'array',
            'occurred_at' => 'datetime',
        ];
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function actor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'actor_id');
    }
}
