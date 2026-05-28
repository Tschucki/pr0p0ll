@props(['q' => [], 'color' => '#ee4d2e'])

@php
    $chart = $q['chart'] ?? 'bar';
    $total = (int) ($q['totalAnswers'] ?? 0);
@endphp

<section class="pr0eval__card">
    <h3 class="pr0eval__q-title">{{ $q['title'] ?? '' }}</h3>
    @if (! empty($q['description']))
        <p class="pr0eval__q-desc">{{ $q['description'] }}</p>
    @endif

    <div class="pr0eval__q-body">
        @if ($total === 0 && $chart !== 'text')
            <p class="pr0eval__empty">Noch keine Antworten.</p>
        @else
            @switch($chart)
                @case('donut')
                    <x-results.donut :options="$q['options'] ?? []" :color="$color" />
                    @break
                @case('histogram')
                    <x-results.histogram :histogram="$q['histogram'] ?? []" :stats="$q['stats'] ?? null" :color="$color" />
                    @break
                @case('text')
                    <x-results.text-list :text-answers="$q['textAnswers'] ?? []" :color="$color" />
                    @break
                @case('unsupported')
                    <p class="pr0eval__empty">Dieser Fragetyp wird in der Auswertung noch nicht dargestellt.</p>
                    @break
                @default
                    <x-results.bar :options="$q['options'] ?? []" :color="$color" />
            @endswitch
        @endif
    </div>

    @if (! empty($q['footerText']))
        <p class="pr0eval__footer-note">{{ $q['footerText'] }}</p>
    @endif
</section>
