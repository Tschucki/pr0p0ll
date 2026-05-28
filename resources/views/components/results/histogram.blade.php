@props(['histogram' => [], 'stats' => null, 'color' => '#ee4d2e'])

@php
    $fmtPct = fn ($n) => rtrim(rtrim(number_format((float) $n, 1, ',', '.'), '0'), ',');
    $fmtNum = fn ($n) => number_format((int) $n, 0, ',', '.');
    $fmtStat = fn ($n) => rtrim(rtrim(number_format((float) $n, 1, ',', '.'), '0'), ',');
@endphp

<div class="pr0eval__bars">
    @foreach ($histogram as $bucket)
        <div>
            <div class="pr0eval__bar-head">
                <span class="pr0eval__bar-label">{{ $bucket['label'] }}</span>
                <span class="pr0eval__bar-value">{{ $fmtNum($bucket['count']) }} ({{ $fmtPct($bucket['percentage']) }}%)</span>
            </div>
            <div class="pr0eval__track">
                <div class="pr0eval__fill"
                     style="width: {{ max((float) $bucket['percentage'], 0) }}%; min-width: {{ ($bucket['count'] ?? 0) > 0 ? '0.5rem' : '0' }}; background-color: {{ $bucket['color'] ?? $color }};"></div>
            </div>
        </div>
    @endforeach
</div>

@if ($stats)
    <div class="pr0eval__stats">
        @foreach (['min' => 'Minimum', 'avg' => 'Durchschnitt', 'median' => 'Median', 'max' => 'Maximum'] as $key => $label)
            <div class="pr0eval__stat">
                <div class="pr0eval__stat-label">{{ $label }}</div>
                <div class="pr0eval__stat-value">{{ $fmtStat($stats[$key]) }}</div>
            </div>
        @endforeach
    </div>
@endif
