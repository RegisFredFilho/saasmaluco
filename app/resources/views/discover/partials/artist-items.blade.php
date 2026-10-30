@foreach ($artists as $artist)
    <li>
        <a href="{{ route('artists.show', $artist) }}" class="flex gap-4 bg-white border border-stone-200 rounded-lg p-4 hover:border-stone-400 transition-colors">
            @if ($artist->photoUrl())
                <img src="{{ $artist->photoUrl() }}" alt="" class="h-16 w-16 rounded-full object-cover shrink-0">
            @else
                <div class="h-16 w-16 rounded-full bg-stone-200 shrink-0 flex items-center justify-center font-semibold text-stone-600">
                    {{ strtoupper(mb_substr($artist->publicName(), 0, 1)) }}
                </div>
            @endif
            <div class="min-w-0">
                <h2 class="font-medium text-stone-900 truncate">{{ $artist->publicName() }}</h2>
                <p class="text-sm text-stone-600">
                    {{ $artist->kind->label() }}
                    @if ($artist->city || $artist->state)
                        · {{ collect([$artist->city, $artist->state])->filter()->join('/') }}
                    @endif
                    @if ($artist->region)
                        · {{ $artist->region }}
                    @endif
                </p>
                @if ($artist->genres->isNotEmpty())
                    <p class="text-xs text-stone-500 mt-1 truncate">{{ $artist->genres->pluck('name')->join(', ') }}</p>
                @endif
            </div>
        </a>
    </li>
@endforeach
