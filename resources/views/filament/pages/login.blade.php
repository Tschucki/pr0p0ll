<x-filament-panels::page.simple>
    <div style="text-align: center; color: #9ca3af; font-size: 0.875rem; line-height: 1.6;">
        <p>
            Logge dich mit deinem pr0gramm-Konto ein. Diese Seite und ihre Inhalte stehen in keiner Verbindung mit
            <x-filament::link :target="'_blank'" :href="'https://pr0gramm.com'">pr0gramm.com</x-filament::link>.
        </p>
        <p style="margin-top: 1rem;">
            Mit der Anmeldung stimmst du den
            <x-filament::link :href="route('frontend.terms')">Nutzungsbedingungen</x-filament::link>
            zu.
        </p>
    </div>

    <form wire:submit="login">
        <div style="display: flex; flex-direction: column; align-items: stretch; gap: 0.75rem;">
            {{ $this->getAuthenticateFormAction() }}
            {{ $this->getBackAction() }}
        </div>
    </form>

    {{ \Filament\Support\Facades\FilamentView::renderHook('panels::auth.login.form.after') }}
</x-filament-panels::page.simple>
