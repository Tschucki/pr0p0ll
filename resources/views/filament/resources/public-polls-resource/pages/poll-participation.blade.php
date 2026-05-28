<x-filament-panels::page>
    @if($description = $this->getDescription())
        <p>
            {{ $description }}
        </p>
    @endif

    <form wire:submit="participate" class="space-y-6">
        {{ $this->form }}

        <div class="fi-form-actions">
            <div class="fi-form-actions-content flex flex-wrap items-center gap-3">
                @foreach($this->getFormActions() as $action)
                    {{ $action }}
                @endforeach
            </div>
        </div>
    </form>
</x-filament-panels::page>
