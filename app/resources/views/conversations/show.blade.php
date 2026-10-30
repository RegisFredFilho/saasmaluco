<x-app-layout>
    <x-slot name="header">
        <x-page-header
            title="Negociação"
            :lead="$conversation->opportunity->title.' · '.$conversation->artistProfile->publicName().' · '.$conversation->venueProfile->display_name"
        >
            <x-slot name="actions">
                <a href="{{ route('conversations.index') }}" class="sg-btn-ghost">← Negociações</a>
                <a href="{{ route('opportunities.show', $conversation->opportunity) }}" class="sg-btn-secondary">Ver oportunidade</a>
            </x-slot>
        </x-page-header>
    </x-slot>

    <div class="mx-auto max-w-4xl space-y-6">
        @if (session('status'))
            <p class="sg-alert-ok">Atualizado.</p>
        @endif
        @if ($errors->any())
            <p class="sg-alert-err">{{ $errors->first() }}</p>
        @endif

        <section class="sg-panel space-y-4 p-5 md:p-6">
            <h3 class="font-semibold text-ink">Propostas</h3>
            @if ($hasAcceptedProposal)
                @php($acceptedProposal = $proposals->firstWhere('status', \App\Enums\ProposalStatus::Accepted))
                <p class="rounded-lg border sg-alert-ok">
                    Há uma proposta aceita.
                    @if ($acceptedProposal?->booking)
                        <a href="{{ route('bookings.show', $acceptedProposal->booking) }}" class="font-medium underline">Ver booking</a>
                    @endif
                </p>
            @endif

            <ul class="space-y-3">
                @forelse ($proposals as $proposal)
                    <li class="space-y-2 rounded-lg border border-canvas-line p-4 text-sm">
                        <div class="flex flex-wrap justify-between gap-2">
                            <p class="font-medium">
                                v{{ $proposal->version }} · R$ {{ number_format((float) $proposal->amount, 2, ',', '.') }}
                                · <span class="sg-badge-neutral">{{ $proposal->status->label() }}</span>
                            </p>
                            <p class="text-ink-muted">por {{ $proposal->createdBy->name }}</p>
                        </div>
                        @if ($proposal->conditions)
                            <p class="whitespace-pre-line text-ink-soft">{{ $proposal->conditions }}</p>
                        @endif
                        @if ($proposal->valid_until)
                            <p class="text-xs text-ink-muted">Válida até {{ $proposal->valid_until->format('d/m/Y') }}</p>
                        @endif
                        @can('respond', $proposal)
                            <div class="flex gap-2">
                                <form method="post" action="{{ route('conversations.proposals.respond', [$conversation, $proposal]) }}">
                                    @csrf
                                    <input type="hidden" name="decision" value="accepted">
                                    <button class="sg-btn-primary !px-3 !py-1.5 !text-xs">Aceitar</button>
                                </form>
                                <form method="post" action="{{ route('conversations.proposals.respond', [$conversation, $proposal]) }}">
                                    @csrf
                                    <input type="hidden" name="decision" value="rejected">
                                    <button class="sg-btn-secondary !px-3 !py-1.5 !text-xs">Recusar</button>
                                </form>
                            </div>
                        @endcan
                    </li>
                @empty
                    <li class="text-sm text-ink-muted">Nenhuma proposta ainda.</li>
                @endforelse
            </ul>

            @if ($canPropose)
                <form method="post" action="{{ route('conversations.proposals.store', $conversation) }}" class="space-y-3 border-t border-canvas-line pt-4">
                    @csrf
                    <h4 class="text-sm font-semibold text-ink">Nova proposta (BRL)</h4>
                    <p class="text-xs text-ink-muted">Enviar uma nova versão substitui propostas “enviadas” anteriores (histórico preservado como substituída).</p>
                    <div class="grid grid-cols-1 gap-3 sm:grid-cols-2">
                        <div>
                            <label class="sg-label" for="amount">Valor (R$)</label>
                            <input id="amount" type="number" step="0.01" min="0" name="amount" required class="sg-input">
                        </div>
                        <div>
                            <label class="sg-label" for="valid_until">Validade</label>
                            <input id="valid_until" type="date" name="valid_until" class="sg-input">
                        </div>
                    </div>
                    <textarea name="conditions" rows="3" class="sg-input" placeholder="Condições (opcional)"></textarea>
                    <x-primary-button>Enviar proposta</x-primary-button>
                </form>
            @endif
        </section>

        <section class="sg-panel space-y-4 p-5 md:p-6">
            <h3 class="font-semibold text-ink">Mensagens</h3>
            <div class="max-h-[28rem] space-y-3 overflow-y-auto">
                @forelse ($messages as $message)
                    <div class="text-sm {{ $message->user_id === auth()->id() ? 'text-right' : '' }}">
                        <p class="mb-0.5 text-xs text-ink-muted">{{ $message->user->name }} · {{ $message->created_at->format('d/m/Y H:i') }}</p>
                        <p class="inline-block rounded-xl px-3 py-2 text-left whitespace-pre-line {{ $message->user_id === auth()->id() ? 'bg-accent-soft text-accent-fg' : 'bg-canvas-mist text-ink' }}">{{ $message->body }}</p>
                    </div>
                @empty
                    <p class="text-sm text-ink-muted">Nenhuma mensagem ainda.</p>
                @endforelse
            </div>
            <div>{{ $messages->links() }}</div>

            <form method="post" action="{{ route('conversations.messages.store', $conversation) }}" class="space-y-3 border-t border-canvas-line pt-4">
                @csrf
                <textarea name="body" rows="3" required maxlength="5000" class="sg-input" placeholder="Escreva uma mensagem…"></textarea>
                <x-primary-button>Enviar mensagem</x-primary-button>
            </form>
        </section>
    </div>
</x-app-layout>
