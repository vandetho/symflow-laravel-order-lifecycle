<?php

declare(strict_types=1);

return [

    'workflows' => [

        // Workflow 1: order lifecycle (state machine)
        // cart → placed → shipped → delivered → completed, with cancel branches
        'order_lifecycle' => [
            'type' => 'state_machine',
            'marking_store' => [
                'type' => 'property',
                'property' => 'lifecycle',
            ],
            'supports' => App\Models\Order::class,
            'initial_marking' => ['cart'],
            'places' => [
                'cart'      => ['metadata' => ['description' => 'In the customer\'s cart']],
                'placed'    => ['metadata' => ['description' => 'Order placed']],
                'shipped'   => ['metadata' => ['description' => 'Out for delivery']],
                'delivered' => ['metadata' => ['description' => 'Received by customer']],
                'completed' => ['metadata' => ['description' => 'Closed without dispute']],
                'cancelled' => ['metadata' => ['description' => 'Cancelled before fulfillment']],
            ],
            'transitions' => [
                'place' => [
                    'from' => 'cart',
                    'to' => 'placed',
                    'guard' => 'role:customer',
                ],
                'ship' => [
                    'from' => 'placed',
                    'to' => 'shipped',
                    'guard' => 'role:warehouse',
                ],
                'deliver' => [
                    'from' => 'shipped',
                    'to' => 'delivered',
                    'guard' => 'role:warehouse',
                ],
                'complete' => [
                    'from' => 'delivered',
                    'to' => 'completed',
                ],
                'cancel_cart' => [
                    'from' => 'cart',
                    'to' => 'cancelled',
                    'guard' => 'role:customer',
                ],
                'cancel_placed' => [
                    'from' => 'placed',
                    'to' => 'cancelled',
                    'guard' => 'role:manager',
                ],
            ],
        ],

        // Workflow 2: payment (state machine)
        // unpaid → authorized → captured → refunded
        'order_payment' => [
            'type' => 'state_machine',
            'marking_store' => [
                'type' => 'property',
                'property' => 'payment',
            ],
            'supports' => App\Models\Order::class,
            'initial_marking' => ['unpaid'],
            'places' => [
                'unpaid'     => ['metadata' => ['description' => 'No payment attempted']],
                'authorized' => ['metadata' => ['description' => 'Hold placed on card']],
                'captured'   => ['metadata' => ['description' => 'Funds settled']],
                'refunded'   => ['metadata' => ['description' => 'Refunded to customer']],
                'failed'     => ['metadata' => ['description' => 'Payment failed']],
            ],
            'transitions' => [
                'authorize' => [
                    'from' => 'unpaid',
                    'to' => 'authorized',
                    'guard' => 'role:customer',
                ],
                'capture' => [
                    'from' => 'authorized',
                    'to' => 'captured',
                    'guard' => 'role:finance',
                ],
                'refund' => [
                    'from' => 'captured',
                    'to' => 'refunded',
                    'guard' => 'role:finance',
                ],
                'fail' => [
                    'from' => 'unpaid',
                    'to' => 'failed',
                ],
                'retry' => [
                    'from' => 'failed',
                    'to' => 'unpaid',
                    'guard' => 'role:customer',
                ],
            ],
        ],

        // Workflow 3: fulfillment (Petri net — picking and packing run in parallel)
        // queued → [picking ∥ packing] → [picked ∥ packed] → ready
        'order_fulfillment' => [
            'type' => 'workflow',
            'marking_store' => [
                'type' => 'property',
                'property' => 'fulfillment_marking',
            ],
            'supports' => App\Models\Order::class,
            'initial_marking' => ['queued'],
            'places' => [
                'queued'  => ['metadata' => ['description' => 'Awaiting warehouse']],
                'picking' => ['metadata' => ['description' => 'Picker pulling items']],
                'packing' => ['metadata' => ['description' => 'Packing supplies prepared']],
                'picked'  => ['metadata' => ['description' => 'Items picked']],
                'packed'  => ['metadata' => ['description' => 'Box packed']],
                'ready'   => ['metadata' => ['description' => 'Ready for shipping']],
            ],
            'transitions' => [
                'start_fulfillment' => [
                    'from' => 'queued',
                    'to' => ['picking', 'packing'],
                    'guard' => 'role:warehouse',
                ],
                'pick' => [
                    'from' => 'picking',
                    'to' => 'picked',
                    'guard' => 'role:warehouse',
                ],
                'pack' => [
                    'from' => 'packing',
                    'to' => 'packed',
                    'guard' => 'role:warehouse',
                ],
                'finalize' => [
                    'from' => ['picked', 'packed'],
                    'to' => 'ready',
                    'guard' => 'role:warehouse',
                ],
            ],
        ],

    ],

];
