@props(['status'])

@if ($status)
    <div {{ $attributes->merge(['class' => 'font-medium text-sm text-red-700 dark:text-red-300']) }}>
        {{ $status }}
    </div>
@endif
