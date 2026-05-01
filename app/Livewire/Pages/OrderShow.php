<?php

declare(strict_types=1);

namespace App\Livewire\Pages;

use App\Models\Order;
use App\Workflow\WorkflowDescriptor;
use Livewire\Attributes\Layout;
use Livewire\Attributes\On;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('components.layouts.app')]
#[Title('Order — Symflow Multi-Workflow Demo')]
class OrderShow extends Component
{
    public Order $order;

    public function mount(Order $order): void
    {
        $this->order = $order->load(['customer', 'auditLogs.actor']);
    }

    #[On('order-updated')]
    public function refreshOrder(): void
    {
        $this->order->refresh()->load(['auditLogs.actor']);
    }

    public function render()
    {
        return view('livewire.pages.order-show', [
            'workflows' => WorkflowDescriptor::all(),
        ]);
    }
}
