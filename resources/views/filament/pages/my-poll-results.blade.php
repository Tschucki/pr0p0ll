<x-filament-panels::page>
    <x-filament::section :heading="'Filter'">
        {{ $this->form }}
    </x-filament::section>

    <x-results.evaluation :evaluation="$this->getEvaluation()" />
</x-filament-panels::page>
