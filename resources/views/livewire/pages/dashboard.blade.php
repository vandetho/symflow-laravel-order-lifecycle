<div class="space-y-8">
    <div class="flex flex-wrap items-end justify-between gap-4">
        <div>
            <h1 class="text-2xl font-semibold tracking-tight text-zinc-900">Orders</h1>
            <p class="mt-1 max-w-2xl text-sm text-zinc-500">
                A single <code class="rounded bg-zinc-100 px-1 py-0.5 font-mono text-[11px]">Order</code> model driven by
                <strong class="text-sky-700">three concurrent workflows</strong> — lifecycle (state machine), payment (state machine), fulfillment (Petri net).
                Each row shows all three states at once.
            </p>
        </div>
    </div>

    @php
        $stats = [
            ['label' => 'Total orders', 'value' => $totals['count'], 'fmt' => 'int', 'tone' => null],
            ['label' => 'Catalog value', 'value' => $totals['value'], 'fmt' => 'money', 'tone' => null],
            ['label' => 'In flight',     'value' => $totals['in_flight'], 'fmt' => 'int', 'tone' => 'orange'],
            ['label' => 'Delivered',     'value' => $totals['delivered'], 'fmt' => 'int', 'tone' => 'emerald'],
        ];
    @endphp
    <dl class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
        @foreach ($stats as $s)
            <div class="rounded-xl border border-zinc-200 bg-white p-5 shadow-xs">
                <dt class="text-xs font-semibold uppercase tracking-wider text-zinc-500">{{ $s['label'] }}</dt>
                <dd class="mt-2 text-2xl font-semibold tracking-tight {{ $s['tone'] === 'orange' ? 'text-orange-700' : ($s['tone'] === 'emerald' ? 'text-emerald-700' : 'text-zinc-900') }}">
                    @if ($s['fmt'] === 'money')
                        ${{ number_format((float) $s['value'], 2) }}
                    @else
                        {{ number_format((int) $s['value']) }}
                    @endif
                </dd>
            </div>
        @endforeach
    </dl>

    <div class="relative max-w-md">
        <svg class="pointer-events-none absolute left-3 top-1/2 size-4 -translate-y-1/2 text-zinc-400" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M9 3.5a5.5 5.5 0 1 0 3.41 9.83l3.13 3.13a.75.75 0 1 0 1.06-1.06l-3.13-3.13A5.5 5.5 0 0 0 9 3.5zM5 9a4 4 0 1 1 8 0 4 4 0 0 1-8 0z" clip-rule="evenodd"/></svg>
        <input type="search" wire:model.live.debounce.300ms="search" placeholder="Search reference or item…"
               class="w-full rounded-lg border border-zinc-200 bg-white py-2 pl-9 pr-3 text-sm shadow-xs placeholder:text-zinc-400 focus:border-orange-500 focus:outline-hidden focus:ring-2 focus:ring-orange-500/20"/>
    </div>

    <div class="overflow-hidden rounded-xl border border-zinc-200 bg-white shadow-xs">
        <table class="min-w-full divide-y divide-zinc-200">
            <thead class="bg-zinc-50">
                <tr class="text-left text-xs font-semibold uppercase tracking-wider text-zinc-500">
                    <th class="px-4 py-3">Reference</th>
                    <th class="px-4 py-3">Customer</th>
                    <th class="px-4 py-3">Items</th>
                    <th class="px-4 py-3 text-right">Total</th>
                    <th class="px-4 py-3"><span class="inline-flex items-center gap-1.5"><span class="size-1.5 rounded-full bg-sky-500"></span>Lifecycle</span></th>
                    <th class="px-4 py-3"><span class="inline-flex items-center gap-1.5"><span class="size-1.5 rounded-full bg-emerald-500"></span>Payment</span></th>
                    <th class="px-4 py-3"><span class="inline-flex items-center gap-1.5"><span class="size-1.5 rounded-full bg-orange-500"></span>Fulfillment</span></th>
                </tr>
            </thead>
            <tbody class="divide-y divide-zinc-100">
                @forelse ($orders as $order)
                    @php
                        $ff = $order->fulfillmentMarkingObj()->getActivePlaces();
                    @endphp
                    <tr class="cursor-pointer transition hover:bg-zinc-50" onclick="window.location='{{ route('orders.show', $order) }}'">
                        <td class="px-4 py-3">
                            <a href="{{ route('orders.show', $order) }}" wire:navigate class="font-mono text-sm font-semibold text-zinc-900 hover:text-orange-700">{{ $order->reference }}</a>
                        </td>
                        <td class="px-4 py-3">
                            <div class="flex items-center gap-2">
                                <span class="grid size-6 place-items-center rounded-full bg-zinc-200 text-[10px] font-semibold text-zinc-700">{{ $order->customer->initials() }}</span>
                                <span class="text-sm text-zinc-700">{{ $order->customer->name }}</span>
                            </div>
                        </td>
                        <td class="px-4 py-3 text-sm text-zinc-600">{{ $order->item_summary }}</td>
                        <td class="px-4 py-3 text-right font-mono text-sm font-semibold text-zinc-900">${{ number_format((float) $order->total, 2) }}</td>
                        <td class="px-4 py-3">
                            <code class="rounded bg-sky-50 px-1.5 py-0.5 font-mono text-[11px] text-sky-700 ring-1 ring-sky-200">{{ $order->lifecycle }}</code>
                        </td>
                        <td class="px-4 py-3">
                            <code class="rounded bg-emerald-50 px-1.5 py-0.5 font-mono text-[11px] text-emerald-700 ring-1 ring-emerald-200">{{ $order->payment }}</code>
                        </td>
                        <td class="px-4 py-3">
                            <div class="flex flex-wrap gap-1">
                                @forelse ($ff as $p)
                                    <code class="rounded bg-orange-50 px-1.5 py-0.5 font-mono text-[11px] text-orange-700 ring-1 ring-orange-200">{{ $p }}</code>
                                @empty
                                    <span class="text-[11px] text-zinc-400">—</span>
                                @endforelse
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="7" class="px-4 py-12 text-center text-sm text-zinc-500">No orders match.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
