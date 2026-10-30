<x-app-layout>
    <x-slot name="header">
        <div>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">Booking</h2>
            <p class="text-sm text-gray-500 mt-1">{{ $booking->opportunity->title }} · {{ $booking->status->label() }}</p>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8 space-y-6">
            @if (session('status'))
                <p class="text-sm text-green-700 bg-green-50 border border-green-100 px-4 py-2 rounded">Atualizado.</p>
            @endif
            @if ($errors->any())
                <p class="text-sm text-red-700 bg-red-50 border border-red-100 px-4 py-2 rounded">{{ $errors->first() }}</p>
            @endif

            <div class="flex gap-4 text-sm">
                <a href="{{ route('bookings.index') }}" class="text-indigo-600 hover:underline">← Agenda</a>
                <a href="{{ route('conversations.show', $booking->conversation) }}" class="text-gray-600 hover:underline">Negociação</a>
            </div>

            <section class="bg-white shadow sm:rounded-lg p-6 space-y-3 text-sm">
                <dl class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    <div>
                        <dt class="text-gray-500">Artista</dt>
                        <dd>{{ $booking->artistProfile->publicName() }}</dd>
                    </div>
                    <div>
                        <dt class="text-gray-500">Estabelecimento</dt>
                        <dd>{{ $booking->venueProfile->display_name }}</dd>
                    </div>
                    <div>
                        <dt class="text-gray-500">Início</dt>
                        <dd>{{ $booking->starts_at->format('d/m/Y H:i') }}</dd>
                    </div>
                    <div>
                        <dt class="text-gray-500">Fim</dt>
                        <dd>{{ $booking->ends_at->format('d/m/Y H:i') }}</dd>
                    </div>
                    <div>
                        <dt class="text-gray-500">Valor</dt>
                        <dd>R$ {{ number_format((float) $booking->amount, 2, ',', '.') }}</dd>
                    </div>
                    <div>
                        <dt class="text-gray-500">Fuso</dt>
                        <dd>{{ $booking->timezone }}</dd>
                    </div>
                </dl>
                @if ($booking->conditions)
                    <div>
                        <p class="text-gray-500">Condições</p>
                        <p class="whitespace-pre-line">{{ $booking->conditions }}</p>
                    </div>
                @endif
            </section>

            @if ($booking->status === \App\Enums\BookingStatus::Confirmed)
                <section class="bg-white shadow sm:rounded-lg p-6 space-y-4">
                    <h3 class="font-medium text-gray-900">Conclusão do show</h3>
                    <p class="text-sm text-gray-600">Exige confirmação das duas partes. Não conclui só porque a data passou.</p>
                    <ul class="text-sm space-y-1">
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
                        <form method="post" action="{{ route('bookings.cancel', $booking) }}" class="space-y-2 border-t pt-4">
                            @csrf
                            <label class="block text-xs text-gray-500" for="cancellation_reason">Cancelar booking</label>
                            <input id="cancellation_reason" name="cancellation_reason" required maxlength="500" class="w-full border-gray-300 rounded-md text-sm" placeholder="Motivo">
                            <x-danger-button>Cancelar compromisso</x-danger-button>
                        </form>
                    @endcan
                </section>
            @endif

            @if ($booking->status === \App\Enums\BookingStatus::Completed)
                <section class="bg-white shadow sm:rounded-lg p-6 text-sm space-y-4">
                    <div class="space-y-2">
                        <p class="text-green-700 font-medium">Show concluído em {{ $booking->completed_at?->format('d/m/Y H:i') }}.</p>
                        @if ($booking->experience)
                            <p>Experiência verificada gerada no perfil do artista (origem: booking #{{ $booking->id }}).</p>
                        @endif
                    </div>

                    <div class="border-t pt-4 space-y-3">
                        <h3 class="font-medium text-gray-900">Avaliações</h3>
                        <p class="text-xs text-gray-500">Independentes (cada lado pode avaliar). Escala 1–5. Janela de {{ \App\Models\Review::WINDOW_DAYS }} dias. Sem edição após publicar.</p>
                        <ul class="space-y-2">
                            @foreach ($booking->reviews as $review)
                                <li class="border rounded p-2">
                                    {{ $review->author_side->label() }} · {{ $review->rating }}/5
                                    @if ($review->comment) — {{ $review->comment }} @endif
                                </li>
                            @endforeach
                        </ul>
                        @if ($canReview)
                            <form method="post" action="{{ route('bookings.reviews.store', $booking) }}" class="space-y-3">
                                @csrf
                                <div>
                                    <label class="block text-xs text-gray-500 mb-1" for="rating">Nota</label>
                                    <select id="rating" name="rating" required class="border-gray-300 rounded-md text-sm">
                                        @for ($i = 5; $i >= 1; $i--)
                                            <option value="{{ $i }}">{{ $i }}</option>
                                        @endfor
                                    </select>
                                </div>
                                <textarea name="comment" rows="3" maxlength="2000" class="w-full border-gray-300 rounded-md text-sm" placeholder="Comentário (opcional)"></textarea>
                                <x-primary-button>Publicar avaliação</x-primary-button>
                            </form>
                        @endif
                    </div>
                </section>
            @endif

            @if ($booking->status === \App\Enums\BookingStatus::Cancelled)
                <section class="bg-white shadow sm:rounded-lg p-6 text-sm space-y-1">
                    <p class="text-red-700 font-medium">Cancelado em {{ $booking->cancelled_at?->format('d/m/Y H:i') }}</p>
                    @if ($booking->cancelledBy)
                        <p>Por {{ $booking->cancelledBy->name }}</p>
                    @endif
                    <p>{{ $booking->cancellation_reason }}</p>
                </section>
            @endif
        </div>
    </div>
</x-app-layout>
