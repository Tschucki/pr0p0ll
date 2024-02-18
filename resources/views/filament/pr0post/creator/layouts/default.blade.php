@php
    $data = $this->data;
    $color = $data['color'];
    $questions = $this->getResults();
@endphp

<div class="bg-[#161618] py-2 fi-section rounded-xl shadow-sm ring-1 ring-gray-950/5 dark:bg-gray-900 dark:ring-white/10"
     id="pr0post">
    <h1 class="text-center text-3xl font-medium" @if($data['color'])style="color: {{$data['color']}}"@endif>pr0p0ll
        Umfrageauswertung</h1>
    <h1 class="text-center text-3xl mt-2 font-medium">Thema: <span
                @if($data['color'])style="color: {{$data['color']}}"@endif>{{$data['title']}}</span></h1>
    @if($data['description'])
        <p class="text-center text-xl mt-2 font-medium">{{$data['description']}}</span></p>
    @endif

    <div class="space-y-3 mt-6">
        @foreach($questions as $key => $question)
            @php
                $newKey = $key . $data['description_' . $question->properties['answerData']['questionId']] . $data['color']
            @endphp
            @if($data['display_' . $question->properties['answerData']['questionId']])
                @livewire($question->widget, [
                    'subHeading' => $data['description_' . $question->properties['answerData']['questionId']],
                    'answerData' => $question->properties['answerData'],
                    'color' => $data['color'],
                ], key($newKey))
            @endif
        @endforeach
    </div>
    <div class="border-t border-white/10 p-4 grid grid-cols-2 gap-2">
        <div class="grid grid-cols-2">
            <div>Teilnehmer:</div>
            <div>
                {{ $this->participants }}
            </div>

        </div>
        <div class="grid grid-cols-2">
            <div>Fragen:</div>
            <div>{{ count($questions) }}</div>
        </div>
        <div class="grid grid-cols-2">
            <div>Umfrageende:</div>
            <div>{{ \App\Enums\ClosesAfter::from($this->record->closes_after)->getLabel() }}</div>
        </div>
        <div class="grid grid-cols-2">
            <div>ID:</div>
            <div>{{$this->record->getKey()}}</div>
        </div>


    </div>
    <div class="border-t border-white/10 pt-2 px-2">
        <div class="flex items-center justify-center flex-col">
            <div style="height: auto;" class="fi-logo inline-flex">
                <div class="flex items-center gap-2 font-medium text-lg sm:h-8 h-6">
                    <img src="{{asset('pr0p0ll.png')}}" title="Pr0p0ll Logo" class="w-auto sm:h-8 h-6 aspect-square">
                </div>
            </div>
            <div>
                <a class="text-sm font-medium">pr0p0ll.com</a>
            </div>
        </div>
    </div>
</div>

<script src="https://html2canvas.hertzen.com/dist/html2canvas.js"></script>

<script>
    const delay = ms => new Promise(res => setTimeout(res, ms));
    downloadImage = () => {
        html2canvas(document.getElementById('pr0post'), {
            scrollX: 0,
            scrollY: 0,
            windowWidth: 1052,
            backgroundColor: '#161618',
            logging: true,
            useCORS: true,
            onclone: function (document) {
                const post = document.getElementById('pr0post')
                post.style.backgroundColor = '#161618';
                post.style.borderRadius = 0;
                post.style.boxShadow = 'none';
            }
        }).then(function (canvas) {
            let a = document.createElement('a');
            a.href = canvas.toDataURL('image/png');
            a.download = '{{$data['title']}}' + '.png';
            a.click();
        });
    }
</script>

<style>
    #pr0post .fi-section {
        box-shadow: none;
        background-color: #161618;
    }

    #pr0post {
        background-color: #161618;
    }
</style>

