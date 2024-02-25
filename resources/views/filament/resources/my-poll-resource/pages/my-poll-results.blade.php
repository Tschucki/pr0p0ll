<x-filament-panels::page>
    <x-filament::section :heading="'Filter'">
        {{ $this->form }}
    </x-filament::section>
    <x-filament-widgets::widgets
            :columns="$this->getColumns()"
            :widgets="$this->getVisibleWidgets()"
    />
</x-filament-panels::page>
