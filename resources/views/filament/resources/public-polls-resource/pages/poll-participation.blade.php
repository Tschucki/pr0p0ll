<x-filament-panels::page>
    @if($description = $this->getDescription())
        <p>
            {{ $description }}
        </p>
    @endif
    <x-filament-panels::form wire:submit="participate">
        <x-filament::section>
            {{$this->form}}
        </x-filament::section>
        <x-filament-panels::form.actions
                :actions="$this->getCachedFormActions()"
                :full-width="$this->hasFullWidthFormActions()"
        />
    </x-filament-panels::form>
</x-filament-panels::page>
