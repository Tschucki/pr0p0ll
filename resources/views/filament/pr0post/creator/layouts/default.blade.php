@php
    $data = $this->data;
    $questions = $this->getResults();
@endphp

<div class="bg-[#161618] py-1" id="pr0post">
    <h1 class="text-center text-3xl">pr0p0ll Umfrageauswertung</h1>
    <h1 class="text-center text-3xl mt-2">Thema: {{$data['title']}}</span></h1>
    @if($data['description'])
        <p class="text-center text-xl mt-2">{{$data['description']}}</span></p>
    @endif

    <div class="space-y-3 mt-6">
        @foreach($questions as $key => $question)
            @livewire($question->widget, ['answerData' => $question->properties['answerData']], key($key))
        @endforeach
    </div>
</div>

