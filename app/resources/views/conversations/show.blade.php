<x-app-layout>
    <x-slot name="header">
        <div>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">Negociação</h2>
            <p class="text-sm text-gray-500 mt-1">
                {{ $conversation->opportunity->title }}
                · {{ $conversation->artistProfile->publicName() }}
                · {{ $conversation->venueProfile->display_name }}
            </p>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8 space-y-6">
            @if (session('status'))
                <p class="text-sm text-green-700 bg-green-50 border border-green-100 px-4 py-2 rounded">Atualizado.</p>
            @endif
            @if ($errors->any())
                <p class="text-sm text-red-700 bg-red-50 border border-red-100 px-4 py-2 rounded">{{ $errors->first() }}</p>
            @endif

            <div class="flex gap-4 text-sm">
                <a href="{{ route('conversations.index') }}" class="text-indigo-600 hover:underline">← Negociações</a>
                <a href="{{ route('opportunities.show', $conversation->opportunity) }}" class="text-gray-600 hover:underline">Ver oportunidade</a>
            </div>

            <section class="bg-white shadow sm:rounded-lg p-6 space-y-4">
                <h3 class="font-medium text-gray-900">Propostas</h3>
                @if ($hasAcceptedProposal)
                    @php($acceptedProposal = $proposals->firstWhere('status', \App\Enums\ProposalStatus::Accepted))
                    <p class="text-sm text-green-700">
                        Há uma proposta aceita.
                        @if ($acceptedProposal?->booking)
                            <a href="{{ route('bookings.show', $acceptedProposal->booking) }}" class="underline">Ver booking</a>
                        @endif
                    </p>
                @endif

                <ul class="space-y-3">
                    @forelse ($proposals as $proposal)
                        <li class="border rounded p-3 text-sm space-y-2">
                            <div class="flex flex-wrap justify-between gap-2">
                                <p class="font-medium">
                                    v{{ $proposal->version }} · R$ {{ number_format((float) $proposal->amount, 2, ',', '.') }}
                                    · {{ $proposal->status->label() }}
                                </p>
                                <p class="text-gray-500">por {{ $proposal->createdBy->name }}</p>
                            </div>
                            @if ($proposal->conditions)
                                <p class="text-gray-700 whitespace-pre-line">{{ $proposal->conditions }}</p>
                            @endif
                            @if ($proposal->valid_until)
                                <p class="text-xs text-gray-500">Válida até {{ $proposal->valid_until->format('d/m/Y') }}</p>
                            @endif
                            @can('respond', $proposal)
                                <div class="flex gap-2">
                                    <form method="post" action="{{ route('conversations.proposals.respond', [$conversation, $proposal]) }}">
                                        @csrf
                                        <input type="hidden" name="decision" value="accepted">
                                        <button class="px-3 py-1.5 bg-gray-800 text-white text-xs rounded-md">Aceitar</button>
                                    </form>
                                    <form method="post" action="{{ route('conversations.proposals.respond', [$conversation, $proposal]) }}">
                                        @csrf
                                        <input type="hidden" name="decision" value="rejected">
                                        <button class="px-3 py-1.5 border border-gray-300 text-xs rounded-md">Recusar</button>
                                    </form>
                                </div>
                            @endcan
                        </li>
                    @empty
                        <li class="text-sm text-gray-500">Nenhuma proposta ainda.</li>
                    @endforelse
                </ul>

                @if ($canPropose)
                    <form method="post" action="{{ route('conversations.proposals.store', $conversation) }}" class="space-y-3 border-t pt-4">
                        @csrf
                        <h4 class="font-medium text-gray-800 text-sm">Nova proposta (BRL)</h4>
                        <p class="text-xs text-gray-500">Enviar uma nova versão substitui propostas “enviadas” anteriores (histórico preservado como substituída).</p>
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                            <div>
                                <label class="block text-xs text-gray-500 mb-1" for="amount">Valor (R$)</label>
                                <input id="amount" type="number" step="0.01" min="0" name="amount" required class="w-full border-gray-300 rounded-md text-sm">
                            </div>
                            <div>
                                <label class="block text-xs text-gray-500 mb-1" for="valid_until">Validade</label>
                                <input id="valid_until" type="date" name="valid_until" class="w-full border-gray-300 rounded-md text-sm">
                            </div>
                        </div>
                        <textarea name="conditions" rows="3" class="w-full border-gray-300 rounded-md text-sm" placeholder="Condições (opcional)"></textarea>
                        <x-primary-button>Enviar proposta</x-primary-button>
                    </form>
                @endif
            </section>

            <section class="bg-white shadow sm:rounded-lg p-6 space-y-4">
                <h3 class="font-medium text-gray-900">Mensagens</h3>
                <div class="space-y-3 max-h-[28rem] overflow-y-auto">
                    @forelse ($messages as $message)
                        <div class="text-sm {{ $message->user_id === auth()->id() ? 'text-right' : '' }}">
                            <p class="text-xs text-gray-500 mb-0.5">{{ $message->user->name }} · {{ $message->created_at->format('d/m/Y H:i') }}</p>
                            <p class="inline-block px-3 py-2 rounded-md {{ $message->user_id === auth()->id() ? 'bg-indigo-50 text-indigo-900' : 'bg-gray-100 text-gray-800' }} whitespace-pre-line text-left">{{ $message->body }}</p>
                        </div>
                    @empty
                        <p class="text-sm text-gray-500">Nenhuma mensagem ainda.</p>
                    @endforelse
                </div>
                <div>{{ $messages->links() }}</div>

                <form method="post" action="{{ route('conversations.messages.store', $conversation) }}" class="space-y-3 border-t pt-4">
                    @csrf
                    <textarea name="body" rows="3" required maxlength="5000" class="w-full border-gray-300 rounded-md text-sm" placeholder="Escreva uma mensagem…"></textarea>
                    <x-primary-button>Enviar mensagem</x-primary-button>
                </form>
            </section>
        </div>
    </div>
</x-app-layout>
