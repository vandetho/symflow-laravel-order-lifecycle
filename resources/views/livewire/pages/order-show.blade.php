<div class="space-y-6">
    <div class="flex items-center gap-2 text-sm text-zinc-500">
        <a href="{{ route('dashboard') }}" wire:navigate class="hover:text-zinc-900">Orders</a>
        <svg class="size-4" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M7.21 14.77a.75.75 0 0 1 .02-1.06L11.168 10 7.23 6.29a.75.75 0 1 1 1.04-1.08l4.5 4.25a.75.75 0 0 1 0 1.08l-4.5 4.25a.75.75 0 0 1-1.06-.02z" clip-rule="evenodd"/></svg>
        <code class="font-mono font-medium text-zinc-700">{{ $order->reference }}</code>
    </div>

    {{-- Header --}}
    <div class="rounded-2xl border border-zinc-200 bg-white p-6 shadow-xs">
        <div class="flex flex-wrap items-start justify-between gap-4">
            <div>
                <div class="flex flex-wrap items-center gap-3">
                    <code class="font-mono text-sm font-semibold text-zinc-500">{{ $order->reference }}</code>
                    <h1 class="text-xl font-semibold tracking-tight text-zinc-900">{{ $order->item_summary }}</h1>
                    <x-status-pill :status="$order->status" />
                </div>
                <dl class="mt-4 grid grid-cols-2 gap-x-8 gap-y-3 sm:grid-cols-4">
                    <div>
                        <dt class="text-[11px] font-semibold uppercase tracking-wider text-zinc-500">Customer</dt>
                        <dd class="mt-1 flex items-center gap-2 text-sm text-zinc-900">
                            <span class="grid size-6 place-items-center rounded-full bg-zinc-200 text-[10px] font-semibold text-zinc-700">{{ $order->customer->initials() }}</span>
                            {{ $order->customer->name }}
                        </dd>
                    </div>
                    <div>
                        <dt class="text-[11px] font-semibold uppercase tracking-wider text-zinc-500">Total</dt>
                        <dd class="mt-1 font-mono text-lg font-semibold text-zinc-900">${{ number_format((float) $order->total, 2) }}</dd>
                    </div>
                    <div>
                        <dt class="text-[11px] font-semibold uppercase tracking-wider text-zinc-500">Placed</dt>
                        <dd class="mt-1 text-sm text-zinc-900">{{ optional($order->placed_at)->diffForHumans() ?? '—' }}</dd>
                    </div>
                    <div>
                        <dt class="text-[11px] font-semibold uppercase tracking-wider text-zinc-500">Delivered</dt>
                        <dd class="mt-1 text-sm text-zinc-900">{{ optional($order->delivered_at)->diffForHumans() ?? '—' }}</dd>
                    </div>
                </dl>
            </div>

            {{-- Three-state summary chips --}}
            <div class="flex flex-col gap-1.5">
                <div class="flex items-center justify-end gap-2">
                    <span class="text-[10px] font-semibold uppercase tracking-wider text-sky-700">Lifecycle</span>
                    <code class="rounded bg-sky-50 px-1.5 py-0.5 font-mono text-[11px] text-sky-700 ring-1 ring-sky-200">{{ $order->lifecycle }}</code>
                </div>
                <div class="flex items-center justify-end gap-2">
                    <span class="text-[10px] font-semibold uppercase tracking-wider text-emerald-700">Payment</span>
                    <code class="rounded bg-emerald-50 px-1.5 py-0.5 font-mono text-[11px] text-emerald-700 ring-1 ring-emerald-200">{{ $order->payment }}</code>
                </div>
                <div class="flex items-center justify-end gap-2">
                    <span class="text-[10px] font-semibold uppercase tracking-wider text-orange-700">Fulfillment</span>
                    <div class="flex flex-wrap gap-1">
                        @forelse ($order->fulfillmentMarkingObj()->getActivePlaces() as $p)
                            <code class="rounded bg-orange-50 px-1.5 py-0.5 font-mono text-[11px] text-orange-700 ring-1 ring-orange-200">{{ $p }}</code>
                        @empty
                            <span class="text-[11px] text-zinc-400">—</span>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- One section per workflow, stacked --}}
    @foreach ($workflows as $w)
        <livewire:components.workflow-section
            :order="$order"
            :workflow-name="$w['name']"
            :label="$w['label']"
            :description="$w['description']"
            :accent="$w['accent']"
            :symflowbuilder-share-id="$w['symflowbuilderShareId']"
            :key="'wf-'.$w['name'].'-'.$order->id" />
    @endforeach

    {{-- Combined audit timeline across all three workflows --}}
    <div class="rounded-2xl border border-zinc-200 bg-white shadow-xs">
        <div class="border-b border-zinc-100 px-6 py-4">
            <h2 class="text-sm font-semibold tracking-tight text-zinc-900">Combined audit timeline</h2>
            <p class="mt-0.5 text-xs text-zinc-500">Every transition fired on this order, across all three workflows. Written by the same <code class="font-mono">AuditLogMiddleware</code>.</p>
        </div>
        <ul class="divide-y divide-zinc-100">
            @forelse ($order->auditLogs as $log)
                @php
                    $tone = match ($log->workflow_name) {
                        'order_lifecycle' => ['bg' => 'bg-sky-50', 'text' => 'text-sky-700', 'ring' => 'ring-sky-200', 'dot' => 'bg-sky-500'],
                        'order_payment' => ['bg' => 'bg-emerald-50', 'text' => 'text-emerald-700', 'ring' => 'ring-emerald-200', 'dot' => 'bg-emerald-500'],
                        'order_fulfillment' => ['bg' => 'bg-orange-50', 'text' => 'text-orange-700', 'ring' => 'ring-orange-200', 'dot' => 'bg-orange-500'],
                        default => ['bg' => 'bg-zinc-50', 'text' => 'text-zinc-700', 'ring' => 'ring-zinc-200', 'dot' => 'bg-zinc-500'],
                    };
                @endphp
                <li class="flex gap-4 px-6 py-4">
                    <div class="flex-none pt-1">
                        <span class="grid size-8 place-items-center rounded-full bg-zinc-100 text-[11px] font-semibold text-zinc-700">{{ $log->actor?->initials() ?? '··' }}</span>
                    </div>
                    <div class="min-w-0 flex-1">
                        <div class="flex flex-wrap items-baseline gap-x-2">
                            <span class="text-sm font-semibold text-zinc-900">{{ $log->actor?->name ?? 'System' }}</span>
                            <span class="text-xs text-zinc-500">fired</span>
                            <code class="rounded bg-zinc-100 px-1.5 py-0.5 font-mono text-[11px] text-zinc-800">{{ $log->transition }}</code>
                            <span class="inline-flex items-center gap-1 rounded-full px-2 py-0.5 text-[10px] font-semibold ring-1 ring-inset {{ $tone['bg'] }} {{ $tone['text'] }} {{ $tone['ring'] }}">
                                <span class="size-1.5 rounded-full {{ $tone['dot'] }}"></span>{{ $log->workflow_name }}
                            </span>
                            <span class="ml-auto text-xs text-zinc-400">{{ $log->occurred_at->diffForHumans() }}</span>
                        </div>
                        <div class="mt-1 flex flex-wrap items-center gap-1 text-[11px] text-zinc-500">
                            @foreach ((array) $log->marking_before as $p)
                                <code class="rounded bg-zinc-50 px-1 py-0.5 font-mono text-zinc-500 ring-1 ring-zinc-200">{{ $p }}</code>
                            @endforeach
                            <svg class="size-3 text-zinc-400" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M7.21 14.77a.75.75 0 0 1 .02-1.06L11.168 10 7.23 6.29a.75.75 0 1 1 1.04-1.08l4.5 4.25a.75.75 0 0 1 0 1.08l-4.5 4.25a.75.75 0 0 1-1.06-.02z" clip-rule="evenodd"/></svg>
                            @foreach ((array) $log->marking_after as $p)
                                <code class="rounded {{ $tone['bg'] }} px-1 py-0.5 font-mono {{ $tone['text'] }} ring-1 {{ $tone['ring'] }}">{{ $p }}</code>
                            @endforeach
                        </div>
                        @if ($log->reason)
                            <p class="mt-2 rounded-md bg-zinc-50 px-3 py-2 text-sm italic text-zinc-700">"{{ $log->reason }}"</p>
                        @endif
                    </div>
                </li>
            @empty
                <li class="px-6 py-12 text-center text-sm text-zinc-500">No transitions recorded yet.</li>
            @endforelse
        </ul>
    </div>
</div>
