@props(['demographics' => [], 'color' => '#ee4d2e'])

@php
    $d = $demographics;
    $fmtNum = fn ($n) => number_format((int) $n, 0, ',', '.');
    $fmtAge = fn ($n) => rtrim(rtrim(number_format((float) $n, 1, ',', '.'), '0'), ',');
@endphp

<section class="pr0eval__demo">
    <h3 class="pr0eval__demo-title">Teilnehmer-Informationen</h3>
    <p class="pr0eval__demo-sub">
        {{ $fmtNum($d['total'] ?? 0) }} Teilnehmer
        @if (! empty($d['averageAge']))
            · Ø Alter {{ $fmtAge($d['averageAge']) }} Jahre
        @endif
    </p>

    <div class="pr0eval__demo-grid">
        @if (! empty($d['gender']))
            <div>
                <h4 class="pr0eval__demo-block-title">Geschlecht</h4>
                <x-results.donut :options="$d['gender']" :color="$color" />
            </div>
        @endif
        @if (! empty($d['age']))
            <div>
                <h4 class="pr0eval__demo-block-title">Alter</h4>
                <x-results.bar :options="$d['age']" :color="$color" />
            </div>
        @endif
        @if (! empty($d['regions']))
            <div>
                <h4 class="pr0eval__demo-block-title">Top Regionen</h4>
                <x-results.bar :options="$d['regions']" :color="$color" />
            </div>
        @endif
        @if (! empty($d['nationalities']))
            <div>
                <h4 class="pr0eval__demo-block-title">Top Nationalitäten</h4>
                <x-results.bar :options="$d['nationalities']" :color="$color" />
            </div>
        @endif
    </div>
</section>
