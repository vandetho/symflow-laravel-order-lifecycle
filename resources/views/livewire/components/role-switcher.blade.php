<div class="relative" x-data="{}" @keydown.escape.window="$wire.open = false" @click.outside="$wire.open = false">
    @if ($currentUser)
        <button type="button" wire:click="toggle"
                class="flex items-center gap-3 rounded-full border border-zinc-200 bg-white py-1 pl-1 pr-3 shadow-xs transition hover:border-zinc-300 hover:shadow-sm">
            <span class="grid size-7 place-items-center rounded-full bg-orange-600 text-xs font-semibold text-white">{{ $currentUser->initials() }}</span>
            <span class="flex flex-col items-start leading-tight">
                <span class="text-xs font-semibold text-zinc-900">{{ $currentUser->name }}</span>
                <span class="text-[10px] uppercase tracking-wider text-zinc-500">{{ $currentUser->role->label() }}</span>
            </span>
            <svg class="size-4 text-zinc-400" viewBox="0 0 20 20" fill="currentColor"><path d="M5.23 7.21a.75.75 0 0 1 1.06.02L10 11.06l3.71-3.83a.75.75 0 1 1 1.08 1.04l-4.25 4.39a.75.75 0 0 1-1.08 0L5.21 8.27a.75.75 0 0 1 .02-1.06z"/></svg>
        </button>
    @else
        <button type="button" wire:click="toggle"
                class="rounded-full bg-orange-600 px-4 py-1.5 text-sm font-semibold text-white shadow-xs transition hover:bg-orange-700">
            Sign in to demo
        </button>
    @endif

    @if ($open)
        <div x-transition.opacity
             class="absolute right-0 z-40 mt-2 w-72 origin-top-right rounded-xl border border-zinc-200 bg-white p-2 shadow-lg ring-1 ring-zinc-900/5">
            <div class="px-3 py-2 text-[11px] font-semibold uppercase tracking-wider text-zinc-500">Sign in as a demo user</div>
            <ul class="space-y-0.5">
                @foreach ($users as $user)
                    <li>
                        <button type="button" wire:click="signInAs({{ $user->id }})"
                                class="flex w-full items-center gap-3 rounded-lg px-3 py-2 text-left transition hover:bg-zinc-50 {{ $currentUser?->is($user) ? 'bg-zinc-50' : '' }}">
                            <span class="grid size-8 place-items-center rounded-full bg-zinc-100 text-xs font-semibold text-zinc-700">{{ $user->initials() }}</span>
                            <span class="flex flex-1 flex-col leading-tight">
                                <span class="text-sm font-medium text-zinc-900">{{ $user->name }}</span>
                                <span class="text-[11px] text-zinc-500">{{ $user->email }}</span>
                            </span>
                            <span class="rounded-full px-2 py-0.5 text-[10px] font-semibold uppercase tracking-wider ring-1 ring-inset {{ $user->role->badgeClasses() }}">
                                {{ $user->role->label() }}
                            </span>
                        </button>
                    </li>
                @endforeach
            </ul>
            @if ($currentUser)
                <div class="mt-1 border-t border-zinc-100 pt-1">
                    <button type="button" wire:click="signOut"
                            class="w-full rounded-lg px-3 py-2 text-left text-sm font-medium text-rose-600 transition hover:bg-rose-50">
                        Sign out
                    </button>
                </div>
            @endif
        </div>
    @endif
</div>
