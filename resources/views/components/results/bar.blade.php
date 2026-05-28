@props(['options' => [], 'color' => '#ee4d2e'])

@php
    $fmtPct = fn ($n) => rtrim(rtrim(number_format((float) $n, 1, ',', '.'), '0'), ',');
    $fmtNum = fn ($n) => number_format((int) $n, 0, ',', '.');
@endphp

<div class="pr0eval__bars">
    @foreach ($options as $option)
        <div>
            <div class="pr0eval__bar-head">
                <span class="pr0eval__bar-label">{{ $option['label'] }}</span>
                <span class="pr0eval__bar-value">{{ $fmtNum($option['count']) }} ({{ $fmtPct($option['percentage']) }}%)</span>
            </div>
            @if (! empty($option['helperText']))
                <div class="pr0eval__bar-note">{{ $option['helperText'] }}</div>
            @endif
            <div class="pr0eval__track">
                <div class="pr0eval__fill"
                     style="width: {{ max((float) $option['percentage'], 0) }}%; min-width: {{ ($option['count'] ?? 0) > 0 ? '0.5rem' : '0' }}; background-color: {{ $option['color'] ?? $color }};"></div>
            </div>
        </div>
    @endforeach
</div>
