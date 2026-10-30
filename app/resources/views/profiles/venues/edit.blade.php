<x-app-layout>
    <x-slot name="header">
        <x-page-header title="Gerenciar estabelecimento">
            <p class="sg-page-lead">
                Completude: {{ $profile->completionPercent() }}% ·
                <a href="{{ route('venues.show', $profile) }}" class="text-accent-fg hover:underline" target="_blank">Ver página pública</a>
            </p>
        </x-page-header>
    </x-slot>

    <div class="mx-auto max-w-4xl space-y-6">
            @if (session('status'))
                <p class="sg-alert-ok">Alterações registradas.</p>
            @endif
            @if ($errors->any())
                <div class="sg-alert-err">
                    {{ $errors->first() }}
                </div>
            @endif

            <div class="sg-panel p-6">
                <h3 class="font-medium text-ink mb-4">Dados do espaço</h3>
                <form method="post" action="{{ route('venue-profiles.update', $profile) }}" enctype="multipart/form-data" class="space-y-5">
                    @csrf
                    @method('put')

                    <div>
                        <x-input-label for="display_name" value="Nome do estabelecimento" />
                        <x-text-input id="display_name" name="display_name" class="mt-1 block w-full" :value="old('display_name', $profile->display_name)" required />
                    </div>

                    <div>
                        <x-input-label for="description" value="Descrição" />
                        <textarea id="description" name="description" rows="4" class="sg-input mt-1">{{ old('description', $profile->description) }}</textarea>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <x-input-label for="address_line" value="Endereço" />
                            <x-text-input id="address_line" name="address_line" class="mt-1 block w-full" :value="old('address_line', $profile->address_line)" />
                        </div>
                        <div>
                            <x-input-label for="neighborhood" value="Bairro" />
                            <x-text-input id="neighborhood" name="neighborhood" class="mt-1 block w-full" :value="old('neighborhood', $profile->neighborhood)" />
                        </div>
                        <div>
                            <x-input-label for="city" value="Cidade" />
                            <x-text-input id="city" name="city" class="mt-1 block w-full" :value="old('city', $profile->city)" />
                        </div>
                        <div>
                            <x-input-label for="state" value="Estado" />
                            <x-text-input id="state" name="state" class="mt-1 block w-full" :value="old('state', $profile->state)" />
                        </div>
                        <div>
                            <x-input-label for="postal_code" value="CEP" />
                            <x-text-input id="postal_code" name="postal_code" class="mt-1 block w-full" :value="old('postal_code', $profile->postal_code)" />
                        </div>
                        <div>
                            <x-input-label for="capacity" value="Capacidade aproximada" />
                            <x-text-input id="capacity" name="capacity" type="number" min="1" class="mt-1 block w-full" :value="old('capacity', $profile->capacity)" />
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <div>
                            <x-input-label for="event_type" value="Tipo de evento" />
                            <select id="event_type" name="event_type" class="sg-input mt-1">
                                <option value="">—</option>
                                @foreach ($eventTypes as $type)
                                    <option value="{{ $type->value }}" @selected(old('event_type', $profile->event_type?->value) === $type->value)>{{ $type->label() }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <x-input-label for="budget_min" value="Orçamento mín. (R$)" />
                            <x-text-input id="budget_min" name="budget_min" type="number" step="0.01" min="0" class="mt-1 block w-full" :value="old('budget_min', $profile->budget_min)" />
                        </div>
                        <div>
                            <x-input-label for="budget_max" value="Orçamento máx. (R$)" />
                            <x-text-input id="budget_max" name="budget_max" type="number" step="0.01" min="0" class="mt-1 block w-full" :value="old('budget_max', $profile->budget_max)" />
                        </div>
                    </div>

                    <div>
                        <x-input-label value="Gêneros desejados" />
                        <div class="mt-2 grid grid-cols-2 md:grid-cols-3 gap-2">
                            @foreach ($genres as $genre)
                                <label class="inline-flex items-center gap-2 text-sm text-ink-soft">
                                    <input type="checkbox" name="genre_ids[]" value="{{ $genre->id }}" class="rounded border-canvas-line"
                                        @checked(collect(old('genre_ids', $profile->genres->pluck('id')->all()))->contains($genre->id))>
                                    {{ $genre->name }}
                                </label>
                            @endforeach
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <div>
                            <x-input-label for="stage_structure" value="Estrutura de palco" />
                            <textarea id="stage_structure" name="stage_structure" rows="3" class="sg-input mt-1">{{ old('stage_structure', $profile->stage_structure) }}</textarea>
                        </div>
                        <div>
                            <x-input-label for="equipment" value="Equipamentos" />
                            <textarea id="equipment" name="equipment" rows="3" class="sg-input mt-1">{{ old('equipment', $profile->equipment) }}</textarea>
                        </div>
                        <div>
                            <x-input-label for="technical_info" value="Informações técnicas" />
                            <textarea id="technical_info" name="technical_info" rows="3" class="sg-input mt-1">{{ old('technical_info', $profile->technical_info) }}</textarea>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <div>
                            <x-input-label for="contact_name" value="Contato profissional (nome)" />
                            <x-text-input id="contact_name" name="contact_name" class="mt-1 block w-full" :value="old('contact_name', $profile->contact_name)" />
                        </div>
                        <div>
                            <x-input-label for="contact_email" value="E-mail profissional" />
                            <x-text-input id="contact_email" name="contact_email" type="email" class="mt-1 block w-full" :value="old('contact_email', $profile->contact_email)" />
                        </div>
                        <div>
                            <x-input-label for="contact_phone" value="Telefone profissional" />
                            <x-text-input id="contact_phone" name="contact_phone" class="mt-1 block w-full" :value="old('contact_phone', $profile->contact_phone)" />
                        </div>
                    </div>

                    <div>
                        <x-input-label for="cover" value="Imagem de capa" />
                        @if ($profile->coverUrl())
                            <img src="{{ $profile->coverUrl() }}" alt="" class="h-20 w-full max-w-md object-cover rounded mb-2">
                        @endif
                        <input id="cover" name="cover" type="file" accept="image/jpeg,image/png,image/webp" class="mt-1 block w-full text-sm">
                    </div>

                    <div>
                        <x-input-label value="Gestores adicionais (e-mails de contas existentes)" />
                        <p class="text-xs text-ink-muted mb-2">Não compartilhe senha. O proprietário permanece sempre como gestor.</p>
                        @php($managerEmails = old('manager_emails', $profile->managers->where('user_id', '!=', $profile->owner_user_id)->map(fn ($m) => $m->user?->email)->filter()->values()->all()))
                        @for ($i = 0; $i < max(2, count($managerEmails)); $i++)
                            <x-text-input name="manager_emails[]" type="email" class="block w-full mb-2" :value="$managerEmails[$i] ?? ''" placeholder="gestor@exemplo.com" />
                        @endfor
                    </div>

                    <label class="inline-flex items-center gap-2 text-sm text-ink-soft">
                        <input type="checkbox" name="is_public" value="1" class="rounded border-canvas-line" @checked(old('is_public', $profile->is_public))>
                        Perfil público (/estabelecimentos/{{ $profile->slug }})
                    </label>

                    <div>
                        <x-primary-button>Salvar estabelecimento</x-primary-button>
                    </div>
                </form>
            </div>

            <div class="sg-panel p-6 space-y-4">
                <h3 class="font-medium text-ink">Galeria de fotos</h3>
                <div class="grid grid-cols-2 md:grid-cols-4 gap-3">
                    @foreach ($profile->media as $item)
                        <div class="border rounded p-2 text-sm">
                            <img src="{{ $item->url() }}" alt="" class="h-24 w-full object-cover mb-2">
                            <form method="post" action="{{ route('venue-profiles.media.destroy', [$profile, $item]) }}">
                                @csrf
                                @method('delete')
                                <button class="text-xs text-red-600 hover:underline">Remover</button>
                            </form>
                        </div>
                    @endforeach
                </div>
                <form method="post" action="{{ route('venue-profiles.media.store', $profile) }}" enctype="multipart/form-data" class="space-y-3 border-t pt-4">
                    @csrf
                    <input type="file" name="photo" accept="image/jpeg,image/png,image/webp" required class="block w-full text-sm">
                    <x-text-input name="caption" class="block w-full" placeholder="Legenda (opcional)" />
                    <x-primary-button>Adicionar foto</x-primary-button>
                </form>
            </div>

            <div class="sg-panel p-6 space-y-4">
                <h3 class="font-medium text-ink">Oportunidades</h3>
                <ul class="space-y-3 text-sm">
                    @foreach ($profile->opportunities as $opportunity)
                        <li class="border rounded p-3">
                            <div class="flex justify-between gap-3 mb-2">
                                <div>
                                    <a href="{{ route('opportunities.show', $opportunity) }}" class="font-medium text-accent-fg hover:underline">{{ $opportunity->title }}</a>
                                    <p class="text-ink-muted">{{ $opportunity->status->label() }} @if($opportunity->event_date) · {{ $opportunity->event_date->format('d/m/Y') }} @endif</p>
                                </div>
                            </div>
                            <form method="post" action="{{ route('venue-profiles.opportunities.status', [$profile, $opportunity]) }}" class="grid grid-cols-1 md:grid-cols-3 gap-2">
                                @csrf
                                @method('patch')
                                @php($allowed = array_merge([$opportunity->status], $opportunity->status->allowedTransitions()))
                                <select name="status" class="border-canvas-line rounded-md shadow-sm text-sm">
                                    @foreach ($allowed as $status)
                                        <option value="{{ $status->value }}" @selected($opportunity->status === $status)>{{ $status->label() }}</option>
                                    @endforeach
                                </select>
                                <x-primary-button class="text-xs">Atualizar status</x-primary-button>
                            </form>
                        </li>
                    @endforeach
                </ul>

                <form method="post" action="{{ route('venue-profiles.opportunities.store', $profile) }}" class="space-y-3 border-t pt-4">
                    @csrf
                    <h4 class="font-medium text-ink">Nova oportunidade</h4>
                    <x-text-input name="title" class="block w-full" placeholder="Título *" required />
                    <textarea name="description" rows="3" class="sg-input" placeholder="Descrição"></textarea>
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-3">
                        <input type="date" name="event_date" class="border-canvas-line rounded-md shadow-sm">
                        <input type="time" name="starts_at" class="border-canvas-line rounded-md shadow-sm">
                        <input type="time" name="ends_at" class="border-canvas-line rounded-md shadow-sm">
                    </div>
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-3">
                        <x-text-input name="location_label" class="block w-full" placeholder="Local" />
                        <x-text-input name="budget_min" type="number" step="0.01" class="block w-full" placeholder="Orçamento mín." />
                        <x-text-input name="budget_max" type="number" step="0.01" class="block w-full" placeholder="Orçamento máx." />
                    </div>
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-3">
                        <x-text-input name="duration_minutes" type="number" class="block w-full" placeholder="Duração (min)" />
                        <x-text-input name="slots_count" type="number" value="1" class="block w-full" placeholder="Vagas" />
                        <input type="date" name="application_deadline" class="border-canvas-line rounded-md shadow-sm" title="Prazo candidatura">
                    </div>
                    <textarea name="available_structure" rows="2" class="sg-input" placeholder="Estrutura disponível"></textarea>
                    <div class="grid grid-cols-2 md:grid-cols-3 gap-2">
                        @foreach ($genres as $genre)
                            <label class="inline-flex items-center gap-2 text-xs text-ink-soft">
                                <input type="checkbox" name="genre_ids[]" value="{{ $genre->id }}" class="rounded border-canvas-line">
                                {{ $genre->name }}
                            </label>
                        @endforeach
                    </div>
                    <select name="status" class="sg-input" required>
                        <option value="draft">Rascunho</option>
                        <option value="open">Aberta</option>
                    </select>
                    <x-primary-button>Publicar / salvar oportunidade</x-primary-button>
                </form>
            </div>
    </div>
</x-app-layout>
