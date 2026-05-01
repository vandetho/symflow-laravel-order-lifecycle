<?php

namespace Database\Seeders;

use App\Enums\Role;
use App\Models\Order;
use App\Models\OrderAuditLog;
use App\Models\User;
use App\Workflow\WorkflowReasonContext;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Laraflow\Contracts\WorkflowRegistryInterface;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $users = $this->seedUsers();
        $this->seedOrders($users);
    }

    /**
     * @return array<string, User>
     */
    private function seedUsers(): array
    {
        $roster = [
            ['ada',       'Ada Lovelace',     'ada@orders.test',     Role::Customer],
            ['grace',     'Grace Hopper',     'grace@orders.test',   Role::Customer],
            ['warehouse', 'Margaret Hamilton','margaret@orders.test',Role::Warehouse],
            ['finance',   'Marie Curie',      'marie@orders.test',   Role::Finance],
            ['manager',   'Linus Torvalds',   'linus@orders.test',   Role::Manager],
        ];

        $users = [];

        foreach ($roster as [$key, $name, $email, $role]) {
            $users[$key] = User::query()->updateOrCreate(
                ['email' => $email],
                [
                    'name' => $name,
                    'password' => Hash::make('password'),
                    'role' => $role,
                    'email_verified_at' => now(),
                ],
            );
        }

        return $users;
    }

    /**
     * Each step is a tuple: [workflow_name, transition, actor_key, reason?]
     *
     * @param  array<string, User>  $users
     */
    private function seedOrders(array $users): void
    {
        $registry = app(WorkflowRegistryInterface::class);

        $samples = [
            [
                'reference' => 'ORD-1001',
                'customer' => 'ada',
                'item_summary' => '1× standing desk converter',
                'total' => 295.00,
                'steps' => [],
            ],
            [
                'reference' => 'ORD-1002',
                'customer' => 'grace',
                'item_summary' => '3× books, 1× backpack',
                'total' => 184.50,
                'steps' => [
                    ['order_lifecycle',   'place',             'grace'],
                    ['order_payment',     'authorize',         'grace'],
                    ['order_fulfillment', 'start_fulfillment', 'warehouse'],
                    ['order_fulfillment', 'pack',              'warehouse'],
                ],
            ],
            [
                'reference' => 'ORD-1003',
                'customer' => 'ada',
                'item_summary' => '1× espresso machine',
                'total' => 850.00,
                'steps' => [
                    ['order_lifecycle',   'place',             'ada'],
                    ['order_payment',     'authorize',         'ada'],
                    ['order_fulfillment', 'start_fulfillment', 'warehouse'],
                    ['order_fulfillment', 'pick',              'warehouse'],
                    ['order_fulfillment', 'pack',              'warehouse'],
                    ['order_fulfillment', 'finalize',          'warehouse'],
                ],
            ],
            [
                'reference' => 'ORD-1004',
                'customer' => 'grace',
                'item_summary' => '2× monitors, 1× keyboard',
                'total' => 1240.00,
                'steps' => [
                    ['order_lifecycle',   'place',             'grace'],
                    ['order_payment',     'authorize',         'grace'],
                    ['order_fulfillment', 'start_fulfillment', 'warehouse'],
                    ['order_fulfillment', 'pick',              'warehouse'],
                    ['order_fulfillment', 'pack',              'warehouse'],
                    ['order_fulfillment', 'finalize',          'warehouse'],
                    ['order_payment',     'capture',           'finance'],
                    ['order_lifecycle',   'ship',              'warehouse'],
                ],
            ],
            [
                'reference' => 'ORD-1005',
                'customer' => 'ada',
                'item_summary' => '1× ergonomic chair',
                'total' => 590.00,
                'steps' => [
                    ['order_lifecycle',   'place',             'ada'],
                    ['order_payment',     'authorize',         'ada'],
                    ['order_fulfillment', 'start_fulfillment', 'warehouse'],
                    ['order_fulfillment', 'pick',              'warehouse'],
                    ['order_fulfillment', 'pack',              'warehouse'],
                    ['order_fulfillment', 'finalize',          'warehouse'],
                    ['order_payment',     'capture',           'finance'],
                    ['order_lifecycle',   'ship',              'warehouse'],
                    ['order_lifecycle',   'deliver',           'warehouse'],
                ],
            ],
            [
                'reference' => 'ORD-1006',
                'customer' => 'grace',
                'item_summary' => '1× novelty mug',
                'total' => 18.50,
                'steps' => [
                    ['order_lifecycle', 'cancel_cart', 'grace', 'Found a cheaper one elsewhere.'],
                    ['order_payment',   'fail',        'grace'],
                ],
            ],
            [
                'reference' => 'ORD-1007',
                'customer' => 'ada',
                'item_summary' => '1× headphones',
                'total' => 320.00,
                'steps' => [
                    ['order_lifecycle',   'place',             'ada'],
                    ['order_payment',     'authorize',         'ada'],
                    ['order_fulfillment', 'start_fulfillment', 'warehouse'],
                    ['order_fulfillment', 'pick',              'warehouse'],
                    ['order_fulfillment', 'pack',              'warehouse'],
                    ['order_fulfillment', 'finalize',          'warehouse'],
                    ['order_payment',     'capture',           'finance'],
                    ['order_lifecycle',   'ship',              'warehouse'],
                    ['order_lifecycle',   'deliver',           'warehouse'],
                    ['order_payment',     'refund',            'finance', 'Customer reported defective unit; full refund issued.'],
                ],
            ],
        ];

        foreach ($samples as $sample) {
            $customer = $users[$sample['customer']];

            $order = Order::query()->create([
                'reference' => $sample['reference'],
                'customer_id' => $customer->id,
                'item_summary' => $sample['item_summary'],
                'total' => $sample['total'],
                'currency' => 'USD',
                'lifecycle' => 'cart',
                'payment' => 'unpaid',
                'fulfillment_marking' => 'queued',
            ]);

            foreach ($sample['steps'] as $step) {
                [$workflowName, $transition, $actorKey] = $step;
                $reason = $step[3] ?? null;

                Auth::login($users[$actorKey]);
                WorkflowReasonContext::set($reason);

                $registry->get($workflowName)->apply($order, $transition);

                if ($workflowName === 'order_lifecycle' && $transition === 'place') {
                    $order->placed_at = now();
                }
                if ($workflowName === 'order_lifecycle' && $transition === 'ship') {
                    $order->shipped_at = now();
                }
                if ($workflowName === 'order_lifecycle' && $transition === 'deliver') {
                    $order->delivered_at = now();
                }

                $order->save();
            }

            Auth::logout();
        }

        OrderAuditLog::query()->orderBy('id')->get()->each(function (OrderAuditLog $log, int $i) {
            $log->occurred_at = now()->subMinutes((60 - $i) * 11);
            $log->save();
        });
    }
}
