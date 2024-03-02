@php
    $user = filament()->auth()->user();
@endphp

<x-filament-widgets::widget class="fi-account-widget">
    <x-filament::section>
        <div class="flex items-center gap-x-3">
            <div class="flex-1">
                <h2
                        class="grid flex-1 text-base font-semibold leading-6 text-gray-950 dark:text-white"
                >
                    Daten aktualisieren
                </h2>

                <p class="text-sm text-gray-500 dark:text-gray-400">
                    Du solltest Deine persönlichen Daten aktualisieren, damit an passenden Umfragen teilnehmen kannst.
                </p>
            </div>

            <div
                    class="my-auto"
            >
                <x-filament::button
                        color="gray"
                        :href="route('filament.pr0p0ll.pages.benutzer-einstellungen')"
                        tag="a"
                >
                    Meine Daten
                </x-filament::button>
            </div>
        </div>
    </x-filament::section>
</x-filament-widgets::widget>
