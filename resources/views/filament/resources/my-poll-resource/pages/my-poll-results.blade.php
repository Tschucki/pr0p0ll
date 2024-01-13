<x-filament-panels::page>
    <x-filament-widgets::widgets
            :columns="$this->getColumns()"
            :widgets="$this->getVisibleWidgets()"
    />
</x-filament-panels::page>
