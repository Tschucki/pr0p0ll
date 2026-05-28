@props(['textAnswers' => [], 'color' => '#ee4d2e'])

@if (count($textAnswers) > 0)
    <div class="pr0eval__texts">
        @foreach ($textAnswers as $answer)
            <div class="pr0eval__text">{{ $answer['value'] }}</div>
        @endforeach
    </div>
@else
    <p class="pr0eval__empty">Keine Antworten.</p>
@endif
