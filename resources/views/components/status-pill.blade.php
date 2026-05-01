@props(['status' => 'cart'])

@php
    $map = [
        'cart'           => ['label' => 'Cart',           'class' => 'bg-zinc-100 text-zinc-700 ring-zinc-200'],
        'in_fulfillment' => ['label' => 'In fulfillment', 'class' => 'bg-orange-50 text-orange-700 ring-orange-200'],
        'ready_to_ship'  => ['label' => 'Ready to ship',  'class' => 'bg-amber-50 text-amber-700 ring-amber-200'],
        'shipped'        => ['label' => 'Shipped',        'class' => 'bg-sky-50 text-sky-700 ring-sky-200'],
        'delivered'      => ['label' => 'Delivered',      'class' => 'bg-emerald-50 text-emerald-700 ring-emerald-200'],
        'completed'      => ['label' => 'Completed',      'class' => 'bg-emerald-100 text-emerald-800 ring-emerald-300'],
        'cancelled'      => ['label' => 'Cancelled',      'class' => 'bg-rose-50 text-rose-700 ring-rose-200'],
        'refunded'       => ['label' => 'Refunded',       'class' => 'bg-violet-50 text-violet-700 ring-violet-200'],
    ];
    $entry = $map[$status] ?? $map['cart'];
@endphp

<span {{ $attributes->merge(['class' => 'inline-flex items-center gap-1 rounded-full px-2.5 py-0.5 text-xs font-semibold ring-1 ring-inset ' . $entry['class']]) }}>
    {{ $entry['label'] }}
</span>
