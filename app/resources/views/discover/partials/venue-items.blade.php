@foreach ($venues as $venue)
    <li>
        <a href="{{ route('venues.show', $venue) }}" class="block bg-white border border-stone-200 rounded-lg p-4 hover:border-stone-400 transition-colors">
            <h2 class="font-medium text-stone-900">{{ $venue->display_name }}</h2>
            <p class="text-sm text-stone-600 mt-1">
                @if ($venue->city || $venue->state)
                    {{ collect([$venue->city, $venue->state])->filter()->join('/') }}
                @else
                    Localização não informada
                @endif
                @if ($venue->event_type)
                    · {{ $venue->event_type->label() }}
                @endif
                @if ($venue->capacity)
                    · ~{{ $venue->capacity }} pessoas
                @endif
            </p>
            @if ($venue->genres->isNotEmpty())
                <p class="text-xs text-stone-500 mt-1 truncate">{{ $venue->genres->pluck('name')->join(', ') }}</p>
            @endif
        </a>
    </li>
@endforeach
