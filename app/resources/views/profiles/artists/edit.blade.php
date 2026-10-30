<x-app-layout>
    <x-slot name="header">
        <x-page-header title="Editar artista">
            <p class="sg-page-lead">
                Completude: {{ $profile->completionPercent() }}% ·
                <a href="{{ route('artists.show', $profile) }}" class="text-accent-fg hover:underline" target="_blank">Ver página pública</a>
            </p>
        </x-page-header>
    </x-slot>

    <div class="mx-auto max-w-4xl space-y-6">
            @if (session('status'))
                <p class="sg-alert-ok">Alterações registradas.</p>
            @endif

            <div class="sg-panel p-6">
                <h3 class="font-medium text-ink mb-4">Dados principais</h3>
                <form method="post" action="{{ route('artist-profiles.update', $profile) }}" enctype="multipart/form-data" class="space-y-5">
                    @csrf
                    @method('put')

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <x-input-label for="display_name" value="Nome do artista / banda" />
                            <x-text-input id="display_name" name="display_name" class="mt-1 block w-full" :value="old('display_name', $profile->display_name)" required />
                            <x-input-error :messages="$errors->get('display_name')" class="mt-2" />
                        </div>
                        <div>
                            <x-input-label for="stage_name" value="Nome artístico (opcional)" />
                            <x-text-input id="stage_name" name="stage_name" class="mt-1 block w-full" :value="old('stage_name', $profile->stage_name)" />
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <x-input-label for="kind" value="Tipo" />
                            <select id="kind" name="kind" class="sg-input mt-1" required>
                                @foreach ($kinds as $kind)
                                    <option value="{{ $kind->value }}" @selected(old('kind', $profile->kind->value) === $kind->value)>{{ $kind->label() }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <x-input-label for="presentation_type" value="Tipo de apresentação" />
                            <select id="presentation_type" name="presentation_type" class="sg-input mt-1">
                                <option value="">—</option>
                                @foreach ($presentationTypes as $type)
                                    <option value="{{ $type->value }}" @selected(old('presentation_type', $profile->presentation_type?->value) === $type->value)>{{ $type->label() }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <div>
                        <x-input-label for="bio" value="Biografia" />
                        <textarea id="bio" name="bio" rows="4" class="sg-input mt-1">{{ old('bio', $profile->bio) }}</textarea>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <div>
                            <x-input-label for="city" value="Cidade" />
                            <x-text-input id="city" name="city" class="mt-1 block w-full" :value="old('city', $profile->city)" />
                        </div>
                        <div>
                            <x-input-label for="state" value="Estado" />
                            <x-text-input id="state" name="state" class="mt-1 block w-full" :value="old('state', $profile->state)" />
                        </div>
                        <div>
                            <x-input-label for="region" value="Região de atuação" />
                            <x-text-input id="region" name="region" class="mt-1 block w-full" :value="old('region', $profile->region)" />
                        </div>
                    </div>

                    <div>
                        <x-input-label value="Gêneros musicais" />
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

                    <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                        <div>
                            <x-input-label for="members_count" value="Integrantes" />
                            <x-text-input id="members_count" name="members_count" type="number" min="1" max="50" class="mt-1 block w-full" :value="old('members_count', $profile->members_count)" />
                        </div>
                        <div>
                            <x-input-label for="fee_min" value="Cachê mín. (R$)" />
                            <x-text-input id="fee_min" name="fee_min" type="number" step="0.01" min="0" class="mt-1 block w-full" :value="old('fee_min', $profile->fee_min)" />
                        </div>
                        <div>
                            <x-input-label for="fee_max" value="Cachê máx. (R$)" />
                            <x-text-input id="fee_max" name="fee_max" type="number" step="0.01" min="0" class="mt-1 block w-full" :value="old('fee_max', $profile->fee_max)" />
                            <x-input-error :messages="$errors->get('fee_max')" class="mt-2" />
                        </div>
                        <div>
                            <x-input-label for="typical_duration_minutes" value="Duração (min)" />
                            <x-text-input id="typical_duration_minutes" name="typical_duration_minutes" type="number" min="15" max="600" class="mt-1 block w-full" :value="old('typical_duration_minutes', $profile->typical_duration_minutes)" />
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <x-input-label for="own_equipment" value="Equipamentos próprios" />
                            <textarea id="own_equipment" name="own_equipment" rows="3" class="sg-input mt-1">{{ old('own_equipment', $profile->own_equipment) }}</textarea>
                        </div>
                        <div>
                            <x-input-label for="required_tech" value="Estrutura técnica necessária" />
                            <textarea id="required_tech" name="required_tech" rows="3" class="sg-input mt-1">{{ old('required_tech', $profile->required_tech) }}</textarea>
                        </div>
                    </div>

                    <div>
                        <x-input-label for="repertoire" value="Repertório" />
                        <textarea id="repertoire" name="repertoire" rows="3" class="sg-input mt-1">{{ old('repertoire', $profile->repertoire) }}</textarea>
                    </div>

                    <div>
                        <x-input-label for="availability_notes" value="Indicação de disponibilidade" />
                        <textarea id="availability_notes" name="availability_notes" rows="2" class="sg-input mt-1">{{ old('availability_notes', $profile->availability_notes) }}</textarea>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <x-input-label for="photo" value="Foto de perfil" />
                            @if ($profile->photoUrl())
                                <img src="{{ $profile->photoUrl() }}" alt="" class="h-16 w-16 rounded-full object-cover mb-2">
                            @endif
                            <input id="photo" name="photo" type="file" accept="image/jpeg,image/png,image/webp" class="mt-1 block w-full text-sm">
                            <p class="text-xs text-ink-muted mt-1">JPEG/PNG/WebP · máx. 5 MB</p>
                            <x-input-error :messages="$errors->get('photo')" class="mt-2" />
                        </div>
                        <div>
                            <x-input-label for="cover" value="Imagem de capa" />
                            @if ($profile->coverUrl())
                                <img src="{{ $profile->coverUrl() }}" alt="" class="h-16 w-full max-w-xs object-cover mb-2 rounded">
                            @endif
                            <input id="cover" name="cover" type="file" accept="image/jpeg,image/png,image/webp" class="mt-1 block w-full text-sm">
                            <p class="text-xs text-ink-muted mt-1">JPEG/PNG/WebP · máx. 5 MB</p>
                            <x-input-error :messages="$errors->get('cover')" class="mt-2" />
                        </div>
                    </div>

                    <div>
                        <x-input-label value="Links externos" />
                        <p class="text-xs text-ink-muted mb-2">Até 15 links (Spotify, Instagram, site, etc.)</p>
                        @php($linkRows = old('links', $profile->links->map(fn ($l) => ['label' => $l->label, 'url' => $l->url])->values()->all()))
                        @for ($i = 0; $i < max(3, count($linkRows)); $i++)
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-2 mb-2">
                                <x-text-input name="links[{{ $i }}][label]" placeholder="Rótulo" class="block w-full" :value="$linkRows[$i]['label'] ?? ''" />
                                <x-text-input name="links[{{ $i }}][url]" placeholder="https://" class="block w-full" :value="$linkRows[$i]['url'] ?? ''" />
                            </div>
                        @endfor
                        <x-input-error :messages="$errors->get('links.0.url')" class="mt-2" />
                    </div>

                    <label class="inline-flex items-center gap-2 text-sm text-ink-soft">
                        <input type="checkbox" name="is_public" value="1" class="rounded border-canvas-line" @checked(old('is_public', $profile->is_public))>
                        Perfil público (visível em /artistas/{{ $profile->slug }})
                    </label>

                    <div>
                        <x-primary-button>Salvar perfil</x-primary-button>
                    </div>
                </form>
            </div>

            <div class="sg-panel p-6 space-y-4">
                <h3 class="font-medium text-ink">Portfólio (fotos e vídeos)</h3>
                <div class="grid grid-cols-2 md:grid-cols-4 gap-3">
                    @foreach ($profile->media as $item)
                        <div class="border rounded p-2 text-sm">
                            @if ($item->type->value === 'photo')
                                <img src="{{ $item->url() }}" alt="" class="h-24 w-full object-cover mb-2">
                            @else
                                <a href="{{ $item->url() }}" class="mb-2 flex h-24 items-center justify-center bg-ink text-xs text-white" target="_blank">Vídeo</a>
                            @endif
                            <p class="truncate text-ink-muted">{{ $item->caption ?: $item->type->value }}</p>
                            <form method="post" action="{{ route('artist-profiles.media.destroy', [$profile, $item]) }}" class="mt-1">
                                @csrf
                                @method('delete')
                                <button class="text-xs text-red-600 hover:underline">Remover</button>
                            </form>
                        </div>
                    @endforeach
                </div>
                <form method="post" action="{{ route('artist-profiles.media.store', $profile) }}" enctype="multipart/form-data" class="space-y-3 border-t pt-4">
                    @csrf
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-3">
                        <div>
                            <x-input-label for="type" value="Tipo" />
                            <select name="type" id="type" class="sg-input mt-1" required>
                                <option value="photo">Foto</option>
                                <option value="video_link">Vídeo (YouTube/Vimeo)</option>
                            </select>
                        </div>
                        <div>
                            <x-input-label for="media_photo" value="Arquivo (foto)" />
                            <input id="media_photo" name="photo" type="file" accept="image/jpeg,image/png,image/webp" class="mt-1 block w-full text-sm">
                        </div>
                        <div>
                            <x-input-label for="external_url" value="URL do vídeo" />
                            <x-text-input id="external_url" name="external_url" class="mt-1 block w-full" placeholder="https://youtube.com/..." />
                        </div>
                    </div>
                    <x-text-input name="caption" class="block w-full" placeholder="Legenda (opcional)" />
                    <x-input-error :messages="$errors->get('external_url')" class="mt-1" />
                    <x-primary-button>Adicionar mídia</x-primary-button>
                </form>
            </div>

            <div class="sg-panel p-6 space-y-4">
                <h3 class="font-medium text-ink">Experiência profissional</h3>
                <p class="text-xs text-ink-muted">Experiências manuais ficam como <strong>não verificadas</strong>. Selo verificado só virá de bookings elegíveis.</p>
                <ul class="space-y-2 text-sm">
                    @foreach ($profile->experiences as $experience)
                        <li class="flex justify-between gap-3 border-b pb-2">
                            <div>
                                <span class="font-medium">{{ $experience->title }}</span>
                                <span class="text-ink-muted"> · {{ $experience->is_verified ? 'Verificada' : 'Não verificada' }}</span>
                            </div>
                            @if ($experience->isManual())
                                <form method="post" action="{{ route('artist-profiles.experiences.destroy', [$profile, $experience]) }}">
                                    @csrf
                                    @method('delete')
                                    <button class="text-red-600 text-xs hover:underline">Remover</button>
                                </form>
                            @endif
                        </li>
                    @endforeach
                </ul>
                <form method="post" action="{{ route('artist-profiles.experiences.store', $profile) }}" class="space-y-3 border-t pt-4">
                    @csrf
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                        <x-text-input name="title" class="block w-full" placeholder="Título *" required />
                        <x-text-input name="venue_name" class="block w-full" placeholder="Local / casa" />
                        <x-text-input name="city" class="block w-full" placeholder="Cidade" />
                        <div class="grid grid-cols-2 gap-2">
                            <input type="date" name="started_on" class="border-canvas-line rounded-md shadow-sm">
                            <input type="date" name="ended_on" class="border-canvas-line rounded-md shadow-sm">
                        </div>
                    </div>
                    <textarea name="description" rows="2" class="sg-input" placeholder="Descrição"></textarea>
                    <x-primary-button>Adicionar experiência</x-primary-button>
                </form>
            </div>

            @if ($canManageMembers ?? false)
                <div class="sg-panel p-6 space-y-4">
                    <h3 class="font-medium text-ink">Integrantes da banda</h3>
                    <p class="text-xs text-ink-muted">Convide contas existentes por e-mail. Elas precisam aceitar o convite — sem compartilhar senha.</p>

                    <ul class="text-sm space-y-2">
                        @foreach ($profile->members as $member)
                            <li class="flex items-center justify-between gap-3 border-b border-canvas-line pb-2">
                                <span>
                                    {{ $member->user?->name }}
                                    <span class="text-ink-muted">({{ $member->user?->email }})</span>
                                    — {{ $member->role->label() }}
                                </span>
                                @if ($member->role->value !== 'owner' && (int) $member->user_id !== (int) $profile->owner_user_id)
                                    <form method="post" action="{{ route('artist-profiles.members.destroy', [$profile, $member]) }}">
                                        @csrf
                                        @method('delete')
                                        <button class="text-red-600 text-xs hover:underline">Remover</button>
                                    </form>
                                @endif
                            </li>
                        @endforeach
                    </ul>

                    @if ($pendingInvites->isNotEmpty())
                        <div class="border-t pt-3 space-y-2">
                            <p class="text-sm font-medium text-ink">Convites pendentes</p>
                            @foreach ($pendingInvites as $invite)
                                <div class="flex justify-between gap-3 text-sm items-center">
                                    <span>{{ $invite->invitedUser?->email }} — {{ $invite->role->label() }}</span>
                                    <form method="post" action="{{ route('invites.cancel', $invite) }}">
                                        @csrf
                                        @method('delete')
                                        <button class="text-red-600 text-xs hover:underline">Cancelar</button>
                                    </form>
                                </div>
                            @endforeach
                        </div>
                    @endif

                    <form method="post" action="{{ route('artist-profiles.invites.store', $profile) }}" class="grid grid-cols-1 md:grid-cols-3 gap-3 border-t pt-4 items-end">
                        @csrf
                        <div>
                            <x-input-label for="invite_email" value="E-mail da conta" />
                            <x-text-input id="invite_email" name="email" type="email" class="mt-1 block w-full" required />
                            <x-input-error :messages="$errors->get('email')" class="mt-2" />
                        </div>
                        <div>
                            <x-input-label for="invite_role" value="Papel" />
                            <select id="invite_role" name="role" class="sg-input mt-1" required>
                                <option value="member">Integrante</option>
                                <option value="admin">Admin</option>
                            </select>
                        </div>
                        <x-primary-button>Convidar</x-primary-button>
                    </form>
                </div>
            @endif

            <div class="sg-panel p-6 space-y-4">
                <h3 class="font-medium text-ink">Datas de disponibilidade</h3>
                <ul class="text-sm space-y-1">
                    @foreach ($profile->availabilities as $slot)
                        <li class="flex justify-between">
                            <span>{{ $slot->date->format('d/m/Y') }} — {{ $slot->is_available ? 'disponível' : 'indisponível' }}</span>
                            <form method="post" action="{{ route('artist-profiles.availabilities.destroy', [$profile, $slot]) }}">
                                @csrf
                                @method('delete')
                                <button class="text-red-600 text-xs hover:underline">Remover</button>
                            </form>
                        </li>
                    @endforeach
                </ul>
                <form method="post" action="{{ route('artist-profiles.availabilities.store', $profile) }}" class="grid grid-cols-1 md:grid-cols-4 gap-3 border-t pt-4 items-end">
                    @csrf
                    <div>
                        <x-input-label for="date" value="Data" />
                        <input id="date" type="date" name="date" class="sg-input mt-1" required>
                    </div>
                    <label class="inline-flex items-center gap-2 text-sm pb-2">
                        <input type="checkbox" name="is_available" value="1" class="rounded border-canvas-line" checked>
                        Disponível
                    </label>
                    <x-text-input name="note" class="block w-full" placeholder="Nota" />
                    <x-primary-button>Salvar data</x-primary-button>
                </form>
            </div>
    </div>
</x-app-layout>
