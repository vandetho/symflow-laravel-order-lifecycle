@php
    $accentMap = [
        'sky'     => ['ring' => 'ring-sky-200',     'text' => 'text-sky-700',     'bg' => 'bg-sky-50',     'btn' => 'bg-sky-600 hover:bg-sky-700',     'dot' => 'bg-sky-500'],
        'emerald' => ['ring' => 'ring-emerald-200', 'text' => 'text-emerald-700', 'bg' => 'bg-emerald-50', 'btn' => 'bg-emerald-600 hover:bg-emerald-700', 'dot' => 'bg-emerald-500'],
        'orange'  => ['ring' => 'ring-orange-200',  'text' => 'text-orange-700',  'bg' => 'bg-orange-50',  'btn' => 'bg-orange-600 hover:bg-orange-700',  'dot' => 'bg-orange-500'],
    ];
    $a = $accentMap[$accent] ?? $accentMap['sky'];
@endphp

<div class="overflow-hidden rounded-2xl border border-zinc-200 bg-white shadow-xs">
    {{-- Header --}}
    <div class="flex items-center justify-between border-b border-zinc-100 px-6 py-4 {{ $a['bg'] }}/40">
        <div class="flex items-center gap-3">
            <span class="size-2.5 rounded-full {{ $a['dot'] }}"></span>
            <div>
                <h2 class="text-sm font-semibold tracking-tight {{ $a['text'] }}">{{ $label }}</h2>
                <p class="mt-0.5 text-[11px] text-zinc-500">{{ $description }}</p>
            </div>
        </div>
        <div class="flex flex-wrap justify-end gap-1">
            @forelse ($activePlaces as $place)
                <code class="rounded bg-white px-1.5 py-0.5 font-mono text-[11px] font-semibold {{ $a['text'] }} ring-1 {{ $a['ring'] }}">{{ $place }}</code>
            @empty
                <span class="text-xs text-zinc-400">no active places</span>
            @endforelse
        </div>
    </div>

    {{-- Body: diagram + actions side-by-side --}}
    <div class="grid gap-6 p-6 lg:grid-cols-5">
        <div class="lg:col-span-3">
            <livewire:components.workflow-diagram
                :workflow-name="$workflowName"
                :active-places="$activePlaces"
                :accent="$accent"
                :key="'diagram-'.$workflowName.'-'.$order->id.'-'.implode('-', $activePlaces)" />
        </div>

        <div class="space-y-4 lg:col-span-2">
            @if (! $currentUser)
                <div class="rounded-lg bg-amber-50 px-3 py-2 text-xs text-amber-800 ring-1 ring-amber-200">
                    <strong class="font-semibold">Sign in</strong> to fire role-guarded transitions.
                </div>
            @endif

            <div>
                <label class="block text-[10px] font-semibold uppercase tracking-wider text-zinc-500">Reason / note</label>
                <textarea wire:model="reason" rows="2" placeholder="Captured in the audit log…"
                          class="mt-1 w-full rounded-lg border border-zinc-200 bg-white px-3 py-2 text-sm shadow-xs placeholder:text-zinc-400 focus:outline-hidden focus:ring-2 focus:ring-zinc-500/20"></textarea>
            </div>

            @if (count($grouped['available']) > 0)
                <div>
                    <h3 class="mb-1.5 flex items-center gap-1.5 text-[10px] font-semibold uppercase tracking-wider text-emerald-700">
                        <span class="size-1.5 rounded-full bg-emerald-500"></span>Available now
                    </h3>
                    <div class="grid gap-1.5">
                        @foreach ($grouped['available'] as $row)
                            @php
                                $btn = match ($row['intent']) {
                                    'destructive' => 'bg-rose-600 text-white hover:bg-rose-700',
                                    'success'     => 'bg-emerald-600 text-white hover:bg-emerald-700',
                                    default       => $a['btn'] . ' text-white',
                                };
                            @endphp
                            <button type="button" wire:click="fire('{{ $row['transition']->name }}')"
                                    class="flex flex-col gap-0.5 rounded-md px-3 py-2 text-left text-xs font-semibold transition {{ $btn }}">
                                <span class="flex items-center gap-1.5">
                                    {{ $row['transition']->name }}
                                    @if ($row['transition']->guard)
                                        <span class="rounded bg-white/20 px-1.5 py-0.5 font-mono text-[9px]">{{ $row['transition']->guard }}</span>
                                    @endif
                                </span>
                                <span class="text-[10px] font-normal opacity-80">
                                    {{ implode(', ', $row['transition']->froms) }} → {{ implode(', ', $row['transition']->tos) }}
                                </span>
                            </button>
                        @endforeach
                    </div>
                </div>
            @endif

            @if (count($grouped['awaiting']) > 0)
                <div>
                    <h3 class="mb-1.5 flex items-center gap-1.5 text-[10px] font-semibold uppercase tracking-wider text-amber-700">
                        <span class="size-1.5 rounded-full bg-amber-500"></span>Awaiting another actor
                    </h3>
                    <ul class="space-y-1">
                        @foreach ($grouped['awaiting'] as $row)
                            <li class="rounded-md border border-amber-200 bg-amber-50/60 px-3 py-1.5">
                                <div class="flex items-center gap-1.5 text-xs font-semibold text-amber-900">
                                    {{ $row['transition']->name }}
                                    @if ($row['transition']->guard)
                                        <span class="rounded bg-white/70 px-1 py-0.5 font-mono text-[9px] text-amber-800">{{ $row['transition']->guard }}</span>
                                    @endif
                                </div>
                                <div class="text-[10px] text-amber-800/80">{{ $row['reason'] }}</div>
                            </li>
                        @endforeach
                    </ul>
                </div>
            @endif

            @if (count($grouped['inactive']) > 0)
                <div x-data="{ open: false }">
                    <button type="button" @click="open = !open" class="flex w-full items-center justify-between gap-2 rounded text-left">
                        <span class="flex items-center gap-1.5 text-[10px] font-semibold uppercase tracking-wider text-zinc-500">
                            <span class="size-1.5 rounded-full bg-zinc-300"></span>Not in this state ({{ count($grouped['inactive']) }})
                        </span>
                        <svg class="size-3.5 text-zinc-400 transition" :class="{ 'rotate-180': open }" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M5.23 7.21a.75.75 0 0 1 1.06.02L10 11.06l3.71-3.83a.75.75 0 1 1 1.08 1.04l-4.25 4.39a.75.75 0 0 1-1.08 0L5.21 8.27a.75.75 0 0 1 .02-1.06z" clip-rule="evenodd"/></svg>
                    </button>
                    <ul x-show="open" x-cloak x-transition class="mt-1 space-y-0.5 text-[10px] text-zinc-500">
                        @foreach ($grouped['inactive'] as $row)
                            <li class="rounded bg-zinc-50 px-2 py-1">
                                <code class="font-mono font-semibold text-zinc-700">{{ $row['transition']->name }}</code>
                                — {{ $row['reason'] }}
                            </li>
                        @endforeach
                    </ul>
                </div>
            @endif
        </div>
    </div>

    {{-- Compact per-workflow audit footer --}}
    @if ($auditLogs->isNotEmpty())
        <div class="border-t border-zinc-100 bg-zinc-50/50 px-6 py-3">
            <div class="text-[10px] font-semibold uppercase tracking-wider text-zinc-500">Recent in this workflow</div>
            <ul class="mt-1 space-y-1">
                @foreach ($auditLogs as $log)
                    <li class="flex flex-wrap items-center gap-1.5 text-[11px] text-zinc-600">
                        <span class="font-medium text-zinc-800">{{ $log->actor?->name ?? 'System' }}</span>
                        <span class="text-zinc-400">fired</span>
                        <code class="rounded bg-white px-1.5 py-0.5 font-mono text-[10px] text-zinc-800 ring-1 ring-zinc-200">{{ $log->transition }}</code>
                        @foreach ((array) $log->marking_after as $p)
                            <code class="rounded {{ $a['bg'] }} px-1 py-0.5 font-mono text-[10px] {{ $a['text'] }} ring-1 {{ $a['ring'] }}">{{ $p }}</code>
                        @endforeach
                        <span class="ml-auto text-[10px] text-zinc-400">{{ $log->occurred_at->diffForHumans() }}</span>
                    </li>
                @endforeach
            </ul>
        </div>
    @endif
</div>
