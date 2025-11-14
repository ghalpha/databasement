@props(['variant' => 'default'])

@php
$classes = 'inline-flex rounded-full px-2 py-1 text-xs font-semibold uppercase';
$variantClasses = match($variant) {
    'default' => 'bg-zinc-100 text-zinc-800 dark:bg-zinc-700 dark:text-zinc-200',
    default => 'bg-zinc-100 text-zinc-800 dark:bg-zinc-700 dark:text-zinc-200',
};
@endphp

<span {{ $attributes->merge(['class' => $classes . ' ' . $variantClasses]) }}>
    {{ $slot }}
</span>
