@props(['options' => [], 'color' => '#ee4d2e'])

@php
    $fmtPct = fn ($n) => rtrim(rtrim(number_format((float) $n, 1, ',', '.'), '0'), ',');
    $fmtNum = fn ($n) => number_format((int) $n, 0, ',', '.');
    $radius = 42;
    $circumference = 2 * M_PI * $radius;
    $offset = 0.0;
    $total = array_sum(array_map(fn ($o) => (int) ($o['count'] ?? 0), $options));
@endphp

<div class="pr0eval__donut">
    <div class="pr0eval__donut-wrap">
        <svg viewBox="0 0 100 100" class="pr0eval__donut-svg">
            <circle cx="50" cy="50" r="{{ $radius }}" fill="none" stroke="rgba(255,255,255,0.1)" stroke-width="14" />
            @foreach ($options as $option)
                @php $length = ((float) $option['percentage']) / 100 * $circumference; @endphp
                @if (($option['count'] ?? 0) > 0)
                    <circle cx="50" cy="50" r="{{ $radius }}" fill="none"
                            stroke="{{ $option['color'] ?? $color }}" stroke-width="14"
                            stroke-dasharray="{{ $length }} {{ $circumference }}"
                            stroke-dashoffset="{{ -$offset }}" />
                @endif
                @php $offset += $length; @endphp
            @endforeach
        </svg>
        <div class="pr0eval__donut-center">
            <span class="pr0eval__donut-total">{{ $fmtNum($total) }}</span>
            <span class="pr0eval__donut-cap">Antworten</span>
        </div>
    </div>
    <div class="pr0eval__legend">
        @foreach ($options as $option)
            <div class="pr0eval__legend-item">
                <span class="pr0eval__swatch" style="background-color: {{ $option['color'] ?? $color }};"></span>
                <span class="pr0eval__legend-label">{{ $option['label'] }}@if (! empty($option['helperText']))<span class="pr0eval__legend-note">{{ $option['helperText'] }}</span>@endif</span>
                <span class="pr0eval__legend-value">{{ $fmtNum($option['count']) }} ({{ $fmtPct($option['percentage']) }}%)</span>
            </div>
        @endforeach
    </div>
</div>
