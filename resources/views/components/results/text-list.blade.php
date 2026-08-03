@props(['textAnswers' => [], 'color' => '#ee4d2e'])

@if (count($textAnswers) > 0)
    <div class="pr0eval__texts">
        @foreach ($textAnswers as $answer)
            <div class="pr0eval__text">
                {{ $answer['value'] }}
                @if (($answer['count'] ?? 1) > 1)
                    <span class="pr0eval__text-count">{{ $answer['count'] }}×</span>
                @endif
            </div>
        @endforeach
    </div>
@else
    <p class="pr0eval__empty">Keine Antworten.</p>
@endif
