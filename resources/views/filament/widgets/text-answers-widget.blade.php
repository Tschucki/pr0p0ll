<x-filament-widgets::widget>
    <x-filament::section>
        <div class="sm:flex gap-4 justify-center py-2 relative">
            <div class="w-full">
                <div style="color: {{$this->color}}"
                     class="filament-apex-charts-heading text-xl text-center font-semibold leading-6">
                    {{$this->getQuestionTitle()}}
                </div>
                @if($subHeading)
                    <div class="text-base text-center mt-2 font-medium tracking-tight text-gray-950 dark:text-white">
                        {{$subHeading}}
                    </div>
                @endif

                @foreach($this->getAnswers() as $key => $answer)
                    @if($data['display_answer_' . $key] ?? true)
                    <div class="text-left mt-2 text-sm tracking-tight text-gray-950 dark:text-white p-2 border border-gray-600 rounded-lg">
                        {{$answer}}
                    </div>
                    @endif
                @endforeach

                @if($footerText = $this->getFooterText())
                    <div class="relative text-center mt-2">
                        {{$footerText}}
                    </div>
                @endif
            </div>
        </div>
    </x-filament::section>
</x-filament-widgets::widget>
