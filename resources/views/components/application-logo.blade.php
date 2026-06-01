@props(['alt' => config('app.name', 'RestoSmart')])

<span {{ $attributes->merge(['class' => 'inline-flex overflow-hidden rounded-lg']) }}>
    <img src="{{ asset('images/logo-light.png') }}" alt="{{ $alt }}" class="h-full w-full object-cover dark:hidden">
    <img src="{{ asset('images/logo-dark.png') }}" alt="{{ $alt }}" class="hidden h-full w-full object-cover dark:block">
</span>
