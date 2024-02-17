@php
    $data = $this->data;
    $questions = $this->getResults();
@endphp

<div class="bg-[#161618] py-2 fi-section rounded-xl shadow-sm ring-1 ring-gray-950/5 dark:bg-gray-900 dark:ring-white/10"
     id="pr0post">
    <h1 class="text-center text-3xl">pr0p0ll Umfrageauswertung</h1>
    <h1 class="text-center text-3xl mt-2">Thema: {{$data['title']}}</span></h1>
    @if($data['description'])
        <p class="text-center text-xl mt-2">{{$data['description']}}</span></p>
    @endif

    <div class="space-y-3 mt-6">
        @foreach($questions as $key => $question)
            @if($data['display_' . $question->properties['answerData']['questionId']])
                @livewire($question->widget, ['answerData' => $question->properties['answerData']], key($key))
            @endif
        @endforeach
    </div>
    <div class="border-t border-white/10 py-2 px-4 grid grid-cols-2">
        <div>Teilnehmer: {{ $this->participants }}</div>
        <div>Fragen: {{ count($questions) }}</div>
        <div>Umfrageende: {{ \App\Enums\ClosesAfter::from($this->record->closes_after)->getLabel() }}</div>
    </div>
    <div class="border-t border-white/10 pt-2 px-2">
        <div class="flex items-center justify-center flex-col">
            <div style="height: auto;" class="fi-logo inline-flex">
                <div class="flex items-center gap-2 font-medium text-lg h-full">
                    <img src="{{asset('pr0p0ll.png')}}" title="Pr0p0ll Logo" class="w-auto sm:h-8 h-6 aspect-square">
                    <span>Pr0p0ll</span>
                </div>
            </div>
            <div>
                <a class="text-sm">pr0p0ll.com</a>
            </div>
        </div>
    </div>
</div>

<style>
    #pr0post .fi-section {
        box-shadow: none;
    }
</style>

