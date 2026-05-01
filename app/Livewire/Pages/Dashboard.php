<?php

declare(strict_types=1);

namespace App\Livewire\Pages;

use App\Models\Order;
use Livewire\Attributes\Layout;
use Livewire\Attributes\On;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Component;

#[Layout('components.layouts.app')]
#[Title('Orders — Symflow Multi-Workflow Demo')]
class Dashboard extends Component
{
    #[Url(as: 'q')]
    public string $search = '';

    #[On('user-changed')]
    public function onUserChanged(): void {}

    public function render()
    {
        $query = Order::query()->with('customer')->latest();

        if ($this->search !== '') {
            $needle = '%' . $this->search . '%';
            $query->where(fn ($q) => $q
                ->where('reference', 'like', $needle)
                ->orWhere('item_summary', 'like', $needle));
        }

        $orders = $query->get();

        return view('livewire.pages.dashboard', [
            'orders' => $orders,
            'totals' => [
                'count' => $orders->count(),
                'value' => $orders->sum('total'),
                'in_flight' => $orders->whereIn('status', ['cart', 'in_fulfillment', 'ready_to_ship', 'shipped'])->count(),
                'delivered' => $orders->whereIn('status', ['delivered', 'completed'])->count(),
            ],
        ]);
    }
}
