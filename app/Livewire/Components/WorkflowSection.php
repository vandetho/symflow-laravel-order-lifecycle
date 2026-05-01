<?php

declare(strict_types=1);

namespace App\Livewire\Components;

use App\Models\Order;
use App\Workflow\WorkflowReasonContext;
use Illuminate\Support\Facades\Auth;
use Laraflow\Contracts\WorkflowRegistryInterface;
use Livewire\Attributes\On;
use Livewire\Component;

/**
 * One workflow's slice of the order detail page: header, diagram, grouped
 * transition buttons, and a per-workflow audit feed. Reused three times on
 * OrderShow, once per registered workflow.
 */
class WorkflowSection extends Component
{
    public Order $order;

    public string $workflowName;

    public string $label;

    public string $description;

    public string $accent = 'sky';

    public string $reason = '';

    public function mount(Order $order, string $workflowName, string $label, string $description, string $accent = 'sky'): void
    {
        $this->order = $order;
        $this->workflowName = $workflowName;
        $this->label = $label;
        $this->description = $description;
        $this->accent = $accent;
    }

    public function fire(string $transition): void
    {
        $workflow = app(WorkflowRegistryInterface::class)->get($this->workflowName);
        $result = $workflow->can($this->order, $transition);

        if (! $result->allowed) {
            $messages = collect($result->blockers)->map(fn ($b) => $b->message)->implode(' / ');
            session()->flash('flash.error', "[{$this->workflowName}] Can't fire \"{$transition}\": {$messages}");

            return;
        }

        WorkflowReasonContext::set($this->reason !== '' ? $this->reason : null);

        try {
            $workflow->apply($this->order, $transition);
        } catch (\Throwable $e) {
            session()->flash('flash.error', $e->getMessage());

            return;
        }

        // Convenience timestamp updates per transition.
        $now = now();
        if ($this->workflowName === 'order_lifecycle') {
            if ($transition === 'place')   $this->order->placed_at = $now;
            if ($transition === 'ship')    $this->order->shipped_at = $now;
            if ($transition === 'deliver') $this->order->delivered_at = $now;
        }

        $this->order->save();
        $this->reason = '';
        $this->order->refresh();

        $this->dispatch('order-updated', orderId: $this->order->id);
        session()->flash('flash.success', "[{$this->workflowName}] fired \"{$transition}\".");
    }

    #[On('user-changed')]
    public function onUserChanged(): void
    {
        $this->order->refresh();
    }

    #[On('order-updated')]
    public function onOrderUpdated(): void
    {
        $this->order->refresh();
    }

    /**
     * @return array{available: array<int, mixed>, awaiting: array<int, mixed>, inactive: array<int, mixed>}
     */
    public function getGroupedTransitionsProperty(): array
    {
        $workflow = app(WorkflowRegistryInterface::class)->get($this->workflowName);
        $groups = ['available' => [], 'awaiting' => [], 'inactive' => []];

        foreach ($workflow->definition->transitions as $transition) {
            $result = $workflow->can($this->order, $transition->name);
            $blocker = $result->blockers[0] ?? null;

            $intent = match (true) {
                in_array($transition->name, ['cancel_cart', 'cancel_placed', 'fail', 'refund'], true) => 'destructive',
                in_array($transition->name, ['complete', 'deliver', 'capture'], true) => 'success',
                default => 'primary',
            };

            $row = [
                'transition' => $transition,
                'allowed' => $result->allowed,
                'reason' => $blocker?->message,
                'code' => $blocker?->code,
                'intent' => $intent,
            ];

            if ($result->allowed) {
                $groups['available'][] = $row;
            } elseif (in_array($blocker?->code, ['not_authenticated', 'wrong_role', 'guard_blocked', 'unknown_guard'], true)) {
                $groups['awaiting'][] = $row;
            } else {
                $groups['inactive'][] = $row;
            }
        }

        return $groups;
    }

    public function render()
    {
        return view('livewire.components.workflow-section', [
            'grouped' => $this->groupedTransitions,
            'activePlaces' => $this->order->activePlaces($this->workflowName),
            'currentUser' => Auth::user(),
            'auditLogs' => $this->order->auditLogs()->where('workflow_name', $this->workflowName)->limit(8)->get(),
        ]);
    }
}
