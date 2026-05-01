<?php

declare(strict_types=1);

namespace App\Providers;

use App\Models\Order;
use App\Workflow\AuditLogMiddleware;
use App\Workflow\RoleGuardEvaluator;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\ServiceProvider;
use Laraflow\Contracts\GuardEvaluatorInterface;
use Laraflow\Contracts\WorkflowRegistryInterface;
use Laraflow\Data\Place;
use Laraflow\Data\SubjectEvent;
use Laraflow\Data\Transition;
use Laraflow\Data\WorkflowDefinition;
use Laraflow\Enums\MarkingStoreType;
use Laraflow\Enums\WorkflowEventType;
use Laraflow\Enums\WorkflowType;
use Laraflow\Registry\WorkflowRegistry;
use Laraflow\Subject\MethodMarkingStore;
use Laraflow\Subject\PropertyMarkingStore;
use Laraflow\Subject\Workflow;

class WorkflowServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(GuardEvaluatorInterface::class, RoleGuardEvaluator::class);

        $this->app->singleton(WorkflowRegistryInterface::class, function ($app): WorkflowRegistry {
            $registry = new WorkflowRegistry();
            $config = $app['config']->get('laraflow.workflows', []);
            $guard = $app->make(GuardEvaluatorInterface::class);

            foreach ($config as $name => $cfg) {
                $registry->register((string) $name, $this->buildWorkflow((string) $name, $cfg, $guard));
            }

            return $registry;
        });
    }

    public function boot(): void
    {
        $registry = $this->app->make(WorkflowRegistryInterface::class);
        $audit = $this->app->make(AuditLogMiddleware::class);

        // Same audit middleware + listener attached to every registered workflow —
        // the middleware captures the workflow name from SubjectMiddlewareContext
        // so a single audit table works across all three.
        foreach ($registry->all() as $workflow) {
            $workflow->use($audit);

            $workflow->on(WorkflowEventType::Entered, function (SubjectEvent $event): void {
                if (! $event->subject instanceof Order) {
                    return;
                }

                Log::info('order.entered', [
                    'order_id' => $event->subject->getKey(),
                    'reference' => $event->subject->reference,
                    'workflow' => $event->workflowName,
                    'transition' => $event->transition->name,
                    'now_in' => $event->marking->getActivePlaces(),
                ]);
            });
        }
    }

    /**
     * @param  array<string, mixed>  $cfg
     */
    private function buildWorkflow(string $name, array $cfg, GuardEvaluatorInterface $guard): Workflow
    {
        $places = [];
        $placesRaw = $cfg['places'] ?? [];

        if (array_is_list($placesRaw)) {
            foreach ($placesRaw as $placeName) {
                $places[] = new Place(name: (string) $placeName);
            }
        } else {
            foreach ($placesRaw as $placeName => $value) {
                $places[] = new Place(
                    name: (string) $placeName,
                    metadata: is_array($value) ? ($value['metadata'] ?? null) : null,
                );
            }
        }

        $transitions = [];

        foreach ($cfg['transitions'] ?? [] as $tName => $tCfg) {
            $transitions[] = new Transition(
                name: (string) $tName,
                froms: array_map('strval', (array) ($tCfg['from'] ?? [])),
                tos: array_map('strval', (array) ($tCfg['to'] ?? [])),
                guard: $tCfg['guard'] ?? null,
                metadata: $tCfg['metadata'] ?? null,
                consumeWeight: isset($tCfg['consumeWeight']) ? (int) $tCfg['consumeWeight'] : null,
                produceWeight: isset($tCfg['produceWeight']) ? (int) $tCfg['produceWeight'] : null,
            );
        }

        $definition = new WorkflowDefinition(
            name: $name,
            type: WorkflowType::from($cfg['type'] ?? 'workflow'),
            places: $places,
            transitions: $transitions,
            initialMarking: array_map('strval', (array) ($cfg['initial_marking'] ?? [])),
        );

        $store = $cfg['marking_store'] ?? [];
        $storeType = MarkingStoreType::from($store['type'] ?? 'property');

        $markingStore = match ($storeType) {
            MarkingStoreType::Property => new PropertyMarkingStore($store['property'] ?? 'status'),
            MarkingStoreType::Method => new MethodMarkingStore(
                getter: $store['getter'] ?? 'getMarking',
                setter: $store['setter'] ?? 'setMarking',
            ),
        };

        return new Workflow($definition, $markingStore, $guard);
    }
}
