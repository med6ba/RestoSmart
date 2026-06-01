@props(['value', 'required' => false])

<label {{ $attributes->merge(['class' => 'block font-medium text-sm text-gray-700']) }}>
    <span>{{ $value !== null ? __($value) : $slot }}</span>
    @if ($required)
        <span class="ms-0.5 text-xs font-bold text-brand-600 dark:text-brand-400" aria-hidden="true">*</span>
        <span class="sr-only">{{ __('required') }}</span>
    @endif
</label>
