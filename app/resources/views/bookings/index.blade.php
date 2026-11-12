<x-app-layout>
    <x-slot name="header">
        <x-page-header
            title="Agenda"
            lead="Shows e compromissos confirmados."
        />
    </x-slot>

    <div class="mx-auto max-w-4xl space-y-4">
        @forelse ($bookings as $booking)
            <a href="{{ route('bookings.show', $booking) }}" class="sg-panel flex flex-col overflow-hidden transition hover:border-accent/40 sm:flex-row">
                <div class="flex w-full shrink-0 flex-col items-center justify-center bg-accent-soft px-6 py-5 text-center sm:w-28">
                    <p class="text-xs font-semibold uppercase tracking-wider text-accent-fg">{{ $booking->starts_at->translatedFormat('M') }}</p>
                    <p class="font-display text-3xl font-semibold text-ink">{{ $booking->starts_at->format('d') }}</p>
                    <p class="text-xs text-ink-muted">{{ $booking->starts_at->format('H:i') }}</p>
                </div>
                <div class="flex flex-1 flex-wrap items-start justify-between gap-3 p-4">
                    <div>
                        <p class="font-semibold text-ink">{{ $booking->opportunity->title }}</p>
                        <p class="mt-1 text-sm text-ink-muted">
                            {{ $booking->artistProfile->publicName() }}
                            · {{ $booking->venueProfile->display_name }}
                        </p>
                        <p class="mt-1 text-xs text-ink-muted">
                            até {{ $booking->ends_at->format('H:i') }} · {{ $booking->timezone }}
                        </p>
                    </div>
                    <span class="sg-badge-accent">{{ $booking->status->label() }}</span>
                </div>
            </a>
        @empty
            <x-empty-state title="Nenhum booking">Aceite uma proposta na negociação para confirmar.</x-empty-state>
        @endforelse
        <div>{{ $bookings->links() }}</div>
    </div>
</x-app-layout>
