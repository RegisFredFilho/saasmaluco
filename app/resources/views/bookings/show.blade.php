<x-app-layout>
    <x-slot name="header">
        <x-page-header
            title="Booking"
            :lead="$booking->opportunity->title.' · '.$booking->status->label()"
        >
            <x-slot name="actions">
                <a href="{{ route('bookings.index') }}" class="sg-btn-ghost">← Agenda</a>
                <a href="{{ route('conversations.show', $booking->conversation) }}" class="sg-btn-secondary">Negociação</a>
            </x-slot>
        </x-page-header>
    </x-slot>

    <div class="mx-auto max-w-3xl space-y-6">
        @if (session('status'))
            <p class="sg-alert-ok">Atualizado.</p>
        @endif
        @if ($errors->any())
            <p class="sg-alert-err">{{ $errors->first() }}</p>
        @endif

        <section class="sg-panel space-y-3 p-5 text-sm md:p-6">
            <dl class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                <div>
                    <dt class="text-ink-muted">Artista</dt>
                    <dd class="mt-0.5 font-medium">{{ $booking->artistProfile->publicName() }}</dd>
                </div>
                <div>
                    <dt class="text-ink-muted">Estabelecimento</dt>
                    <dd class="mt-0.5 font-medium">{{ $booking->venueProfile->display_name }}</dd>
                </div>
                <div>
                    <dt class="text-ink-muted">Início</dt>
                    <dd class="mt-0.5 font-medium">{{ $booking->starts_at->format('d/m/Y H:i') }}</dd>
                </div>
                <div>
                    <dt class="text-ink-muted">Fim</dt>
                    <dd class="mt-0.5 font-medium">{{ $booking->ends_at->format('d/m/Y H:i') }}</dd>
                </div>
                <div>
                    <dt class="text-ink-muted">Valor</dt>
                    <dd class="mt-0.5 font-medium">R$ {{ number_format((float) $booking->amount, 2, ',', '.') }}</dd>
                </div>
                <div>
                    <dt class="text-ink-muted">Fuso</dt>
                    <dd class="mt-0.5 font-medium">{{ $booking->timezone }}</dd>
                </div>
            </dl>
            @if ($booking->conditions)
                <div class="border-t border-canvas-line pt-3">
                    <p class="text-ink-muted">Condições</p>
                    <p class="mt-1 whitespace-pre-line">{{ $booking->conditions }}</p>
                </div>
            @endif
        </section>

        @if ($booking->status === \App\Enums\BookingStatus::Confirmed)
            <section class="sg-panel space-y-4 p-5 md:p-6">
                <h3 class="font-semibold text-ink">Conclusão do show</h3>
                <p class="text-sm text-ink-muted">Exige confirmação das duas partes. Não conclui só porque a data passou.</p>
                <ul class="space-y-1 text-sm">
                    <li>Artista: {{ $booking->artist_completed_at ? 'confirmou em '.$booking->artist_completed_at->format('d/m/Y H:i') : 'pendente' }}</li>
                    <li>Estabelecimento: {{ $booking->venue_completed_at ? 'confirmou em '.$booking->venue_completed_at->format('d/m/Y H:i') : 'pendente' }}</li>
                </ul>

                @can('complete', $booking)
                    @php
                        $already = ($isArtistSide && $booking->artist_completed_at) || ($isVenueSide && $booking->venue_completed_at);
                    @endphp
                    @unless ($already)
                        <form method="post" action="{{ route('bookings.complete', $booking) }}">
                            @csrf
                            <x-primary-button>Confirmar conclusão do meu lado</x-primary-button>
                        </form>
                    @endunless
                @endcan

                @can('cancel', $booking)
                    <form method="post" action="{{ route('bookings.cancel', $booking) }}" class="space-y-2 border-t border-canvas-line pt-4">
                        @csrf
                        <label class="sg-label" for="cancellation_reason">Cancelar booking</label>
                        <input id="cancellation_reason" name="cancellation_reason" required maxlength="500" class="sg-input" placeholder="Motivo">
                        <x-danger-button>Cancelar compromisso</x-danger-button>
                    </form>
                @endcan
            </section>
        @endif

        @if ($booking->status === \App\Enums\BookingStatus::Completed)
            <section class="sg-panel space-y-4 p-5 text-sm md:p-6">
                <div class="space-y-2">
                    <p class="font-medium text-emerald-300">Show concluído em {{ $booking->completed_at?->format('d/m/Y H:i') }}.</p>
                    @if ($booking->experience)
                        <p class="text-ink-soft">Experiência verificada gerada no perfil do artista (origem: booking #{{ $booking->id }}).</p>
                    @endif
                </div>

                <div class="space-y-3 border-t border-canvas-line pt-4">
                    <h3 class="font-semibold text-ink">Avaliações</h3>
                    <p class="text-xs text-ink-muted">Independentes (cada lado pode avaliar). Escala 1–5. Janela de {{ \App\Models\Review::WINDOW_DAYS }} dias. Sem edição após publicar.</p>
                    <ul class="space-y-2">
                        @foreach ($booking->reviews as $review)
                            <li class="rounded-lg border border-canvas-line p-3">
                                {{ $review->author_side->label() }} · {{ $review->rating }}/5
                                @if ($review->comment) — {{ $review->comment }} @endif
                            </li>
                        @endforeach
                    </ul>
                    @if ($canReview)
                        <form method="post" action="{{ route('bookings.reviews.store', $booking) }}" class="space-y-3">
                            @csrf
                            <div>
                                <label class="sg-label" for="rating">Nota</label>
                                <select id="rating" name="rating" required class="sg-input">
                                    @for ($i = 5; $i >= 1; $i--)
                                        <option value="{{ $i }}">{{ $i }}</option>
                                    @endfor
                                </select>
                            </div>
                            <textarea name="comment" rows="3" maxlength="2000" class="sg-input" placeholder="Comentário (opcional)"></textarea>
                            <x-primary-button>Publicar avaliação</x-primary-button>
                        </form>
                    @endif
                </div>
            </section>
        @endif

        @if ($booking->status === \App\Enums\BookingStatus::Cancelled)
            <section class="sg-panel space-y-1 p-5 text-sm md:p-6">
                <p class="font-medium text-red-700">Cancelado em {{ $booking->cancelled_at?->format('d/m/Y H:i') }}</p>
                @if ($booking->cancelledBy)
                    <p class="text-ink-muted">Por {{ $booking->cancelledBy->name }}</p>
                @endif
                <p>{{ $booking->cancellation_reason }}</p>
            </section>
        @endif
    </div>
</x-app-layout>
