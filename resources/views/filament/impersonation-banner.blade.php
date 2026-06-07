@php
    $impersonationActive = \App\Services\ImpersonationService::isImpersonating() && auth()->check();
    $impersonationAdmin = $impersonationActive ? \App\Services\ImpersonationService::originalAdmin() : null;
@endphp

@if ($impersonationActive)
    <div style="background-color: #ee4d2e; color: #ffffff; padding: 0.5rem 1rem; display: flex; align-items: center; justify-content: center; gap: 1rem; font-size: 0.875rem; position: sticky; top: 0; z-index: 50;">
        <span>
            Du agierst als <strong>{{ auth()->user()->name }}</strong>@if ($impersonationAdmin) (Admin: {{ $impersonationAdmin->name }})@endif — die Sitzung endet automatisch nach {{ \App\Services\ImpersonationService::MAX_DURATION_MINUTES }} Minuten.
        </span>
        <form method="POST" action="{{ route('impersonation.leave') }}">
            @csrf
            <button type="submit" style="background-color: #ffffff; color: #ee4d2e; border: none; border-radius: 0.375rem; padding: 0.25rem 0.75rem; font-weight: 600; cursor: pointer;">
                Beenden
            </button>
        </form>
    </div>
@endif
