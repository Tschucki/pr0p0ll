@props(['evaluation' => []])

@php
    $header = $evaluation['header'] ?? ['title' => '', 'description' => null, 'color' => '#ee4d2e'];
    $color = $header['color'] ?? '#ee4d2e';
    $footer = $evaluation['footer'] ?? [];
    $fmtNum = fn ($n) => number_format((int) $n, 0, ',', '.');
@endphp

@once
    <style>
        .pr0eval { background:#161618; color:#fff; border-radius:1rem; overflow:hidden; width:100%; max-width:1052px; margin-left:auto; margin-right:auto; line-height:1.4; box-sizing:border-box; }
        .pr0eval *, .pr0eval *::before, .pr0eval *::after { box-sizing:border-box; }
        .pr0eval__body { padding:1.5rem; display:flex; flex-direction:column; gap:2rem; }
        .pr0eval__header { text-align:center; }
        .pr0eval__eyebrow { font-size:.75rem; font-weight:600; text-transform:uppercase; letter-spacing:.2em; margin:0; }
        .pr0eval__title { font-size:1.5rem; font-weight:700; word-break:break-word; margin:.25rem 0 0; }
        .pr0eval__subtitle { color:rgba(255,255,255,.7); margin:.5rem auto 0; max-width:42rem; word-break:break-word; }
        .pr0eval__questions { display:flex; flex-direction:column; gap:1.25rem; }
        .pr0eval__card, .pr0eval__demo { border:1px solid rgba(255,255,255,.1); background:rgba(255,255,255,.02); border-radius:.75rem; padding:1.25rem; }
        .pr0eval__q-title, .pr0eval__demo-title { text-align:center; font-weight:700; font-size:1.125rem; word-break:break-word; margin:0; }
        .pr0eval__q-desc, .pr0eval__demo-sub { text-align:center; color:rgba(255,255,255,.6); font-size:.875rem; margin:.25rem 0 0; word-break:break-word; }
        .pr0eval__q-body { margin-top:1rem; }
        .pr0eval__footer-note { text-align:center; font-size:.75rem; color:rgba(255,255,255,.4); margin:1rem 0 0; }
        .pr0eval__empty { text-align:center; font-size:.875rem; color:rgba(255,255,255,.5); margin:0; }
        .pr0eval__bars { display:flex; flex-direction:column; gap:.75rem; }
        .pr0eval__bar-head { display:flex; align-items:baseline; justify-content:space-between; gap:.75rem; }
        .pr0eval__bar-label { font-size:.9rem; font-weight:500; color:rgba(255,255,255,.9); word-break:break-word; }
        .pr0eval__bar-value { flex-shrink:0; font-size:.875rem; font-weight:600; color:rgba(255,255,255,.6); font-variant-numeric:tabular-nums; }
        .pr0eval__bar-note { font-size:.8rem; line-height:1.35; color:rgba(255,255,255,.5); margin-top:.15rem; word-break:break-word; }
        .pr0eval__track { height:.75rem; width:100%; background:rgba(255,255,255,.1); border-radius:9999px; overflow:hidden; margin-top:.35rem; }
        .pr0eval__fill { height:100%; border-radius:9999px; transition:width .3s ease; }
        .pr0eval__donut { display:flex; flex-direction:column; align-items:center; gap:1.25rem; }
        .pr0eval__donut-wrap { position:relative; flex-shrink:0; }
        .pr0eval__donut-svg { width:12rem; height:12rem; transform:rotate(-90deg); }
        .pr0eval__donut-center { position:absolute; inset:0; display:flex; flex-direction:column; align-items:center; justify-content:center; }
        .pr0eval__donut-total { font-size:1.6rem; font-weight:700; font-variant-numeric:tabular-nums; }
        .pr0eval__donut-cap { font-size:.65rem; text-transform:uppercase; letter-spacing:.05em; color:rgba(255,255,255,.5); }
        .pr0eval__legend { width:100%; max-width:30rem; margin:0 auto; display:flex; flex-direction:column; gap:.5rem; }
        .pr0eval__legend-item { display:flex; align-items:flex-start; gap:.5rem; font-size:.875rem; }
        .pr0eval__swatch { width:.75rem; height:.75rem; border-radius:.15rem; flex-shrink:0; margin-top:.28rem; }
        .pr0eval__legend-label { color:rgba(255,255,255,.9); word-break:break-word; }
        .pr0eval__legend-note { display:block; font-size:.78rem; color:rgba(255,255,255,.45); }
        .pr0eval__legend-value { margin-left:auto; flex-shrink:0; font-weight:600; color:rgba(255,255,255,.6); font-variant-numeric:tabular-nums; }
        .pr0eval__stats { margin-top:1rem; display:grid; grid-template-columns:repeat(2,1fr); gap:.5rem; }
        .pr0eval__stat { border:1px solid rgba(255,255,255,.1); background:rgba(255,255,255,.05); border-radius:.5rem; padding:.5rem .75rem; text-align:center; }
        .pr0eval__stat-label { font-size:.65rem; text-transform:uppercase; letter-spacing:.05em; color:rgba(255,255,255,.5); }
        .pr0eval__stat-value { font-size:1rem; font-weight:700; font-variant-numeric:tabular-nums; }
        .pr0eval__texts { display:grid; grid-template-columns:1fr; gap:.5rem; }
        .pr0eval__text { border:1px solid rgba(255,255,255,.1); background:rgba(255,255,255,.05); border-radius:.5rem; padding:.75rem; font-size:.875rem; line-height:1.5; color:rgba(255,255,255,.9); word-break:break-word; }
        .pr0eval__demo-grid { margin-top:1.25rem; display:grid; grid-template-columns:1fr; gap:1.5rem; }
        .pr0eval__demo-block-title { font-size:.75rem; font-weight:600; text-transform:uppercase; letter-spacing:.05em; color:rgba(255,255,255,.5); margin:0 0 .75rem; }
        .pr0eval__footer { border-top:1px solid rgba(255,255,255,.1); padding:1.25rem 1.5rem; }
        .pr0eval__meta { display:flex; flex-wrap:wrap; justify-content:center; gap:.4rem 1.25rem; text-align:center; font-size:.8rem; color:rgba(255,255,255,.6); }
        .pr0eval__meta strong { color:rgba(255,255,255,.85); font-weight:600; }
        .pr0eval__brand { margin-top:1rem; display:flex; align-items:center; justify-content:center; gap:.5rem; }
        .pr0eval__brand img { height:1.5rem; width:auto; }
        .pr0eval__brand span { font-size:.875rem; font-weight:500; color:rgba(255,255,255,.8); }
        @media (min-width:640px) {
            .pr0eval__body { padding:2.5rem; }
            .pr0eval__card, .pr0eval__demo { padding:1.5rem; }
            .pr0eval__title { font-size:1.875rem; }
            .pr0eval__q-title, .pr0eval__demo-title { font-size:1.25rem; }
            .pr0eval__stats { grid-template-columns:repeat(4,1fr); }
            .pr0eval__texts { grid-template-columns:repeat(2,1fr); }
            .pr0eval__demo-grid { grid-template-columns:repeat(2,1fr); }
            .pr0eval__footer { padding:1.25rem 2.5rem; }
        }
    </style>
@endonce

<div {{ $attributes->merge(['class' => 'pr0eval']) }}>
    <div class="pr0eval__body">
        <header class="pr0eval__header">
            <p class="pr0eval__eyebrow" style="color: {{ $color }};">pr0p0ll Umfrageauswertung</p>
            <h1 class="pr0eval__title">{{ $header['title'] }}</h1>
            @if (! empty($header['description']))
                <p class="pr0eval__subtitle">{{ $header['description'] }}</p>
            @endif
        </header>

        <div class="pr0eval__questions">
            @forelse ($evaluation['questions'] ?? [] as $q)
                <x-results.question :q="$q" :color="$color" />
            @empty
                <p class="pr0eval__empty">Es wurden keine Fragen zur Anzeige ausgewählt.</p>
            @endforelse
        </div>

        @if (! empty($evaluation['demographics']))
            <x-results.demographics :demographics="$evaluation['demographics']" :color="$color" />
        @endif
    </div>

    <footer class="pr0eval__footer">
        <div class="pr0eval__meta">
            <span><strong>Zeitraum:</strong> {{ $footer['period'] ?? '' }}</span>
            <span><strong>Teilnehmer:</strong> {{ $fmtNum($footer['participants'] ?? 0) }}</span>
            <span><strong>Fragen:</strong> {{ $fmtNum($footer['questionCount'] ?? 0) }}</span>
            @if (! empty($footer['category']))
                <span><strong>Kategorie:</strong> {{ $footer['category'] }}</span>
            @endif
            @if (! empty($footer['author']))
                <span><strong>Erstellt von:</strong> {{ $footer['author'] }}</span>
            @endif
            <span><strong>ID:</strong> {{ $footer['pollId'] ?? '' }}</span>
        </div>
        <div class="pr0eval__brand">
            <img src="{{ asset('pr0p0ll.png') }}" alt="pr0p0ll">
            <span>pr0p0ll.com</span>
        </div>
    </footer>
</div>
