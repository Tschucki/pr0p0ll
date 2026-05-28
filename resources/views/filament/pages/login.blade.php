<x-filament-panels::page.simple>
    @if (filament()->hasRegistration())
        <x-slot name="subheading">
            {{ __('filament-panels::pages/auth/login.actions.register.before') }}

            {{ $this->registerAction }}
        </x-slot>
    @endif

    <div class="fi-simple-header-subheading mt-2 text-sm text-gray-500 dark:text-gray-400">
        Logge dich mit deinem pr0gramm-Konto ein.<br/>
        Diese Seite und dessen Inhalte stehen nicht in Verbindung mit
        <x-filament::link :target="'_blank'" :href="'https://pr0gramm.com'">
            pr0gramm.com
        </x-filament::link>
        <br/><br/>
        <h4 class="text-sm font-medium text-gray-950 dark:text-white">
            Nutzungsbedingungen
        </h4>
        Mit deiner Anmeldung stimmst du den
        <x-filament::link :href="route('frontend.terms')">
            Nutzungsbedingungen
        </x-filament::link>
        zu.
    </div>

    <form wire:submit="login">
        <div class="fi-form-actions">
            <div class="fi-form-actions-content flex flex-wrap justify-center gap-3">
                {{ $this->getAuthenticateFormAction() }}
            </div>
        </div>
    </form>

    <div class="fi-form-actions">
        <div class="fi-form-actions-content">
            {{ $this->getBackAction() }}
        </div>
    </div>

    {{ \Filament\Support\Facades\FilamentView::renderHook('panels::auth.login.form.after') }}
</x-filament-panels::page.simple>
