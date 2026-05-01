<?php

declare(strict_types=1);

namespace App\Workflow;

/**
 * Static metadata describing each registered workflow — used by the UI to
 * render per-workflow sections (label, accent color, marking column).
 */
final class WorkflowDescriptor
{
    /**
     * @return array<int, array{
     *     name: string,
     *     label: string,
     *     description: string,
     *     property: string,
     *     accent: string,
     *     icon: string,
     *     symflowbuilderShareId: string
     * }>
     */
    public static function all(): array
    {
        return [
            [
                'name' => 'order_lifecycle',
                'label' => 'Lifecycle',
                'description' => 'cart → placed → shipped → delivered → completed',
                'property' => 'lifecycle',
                'accent' => 'sky',
                'icon' => 'package',
                'symflowbuilderShareId' => 'f9fb48bd55b047f3',
            ],
            [
                'name' => 'order_payment',
                'label' => 'Payment',
                'description' => 'unpaid → authorized → captured → refunded',
                'property' => 'payment',
                'accent' => 'emerald',
                'icon' => 'credit-card',
                'symflowbuilderShareId' => '17fc978fa8ba7163',
            ],
            [
                'name' => 'order_fulfillment',
                'label' => 'Fulfillment',
                'description' => 'queued → [picking ∥ packing] → ready (Petri net)',
                'property' => 'fulfillment_marking',
                'accent' => 'orange',
                'icon' => 'box',
                'symflowbuilderShareId' => 'e06b4f2adc828b0b',
            ],
        ];
    }
}
