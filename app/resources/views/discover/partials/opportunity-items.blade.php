@foreach ($opportunities as $opportunity)
    <li>
        <a href="{{ route('opportunities.show', $opportunity) }}" class="block bg-white border border-stone-200 rounded-lg p-4 hover:border-stone-400 transition-colors">
            <h2 class="font-medium text-stone-900">{{ $opportunity->title }}</h2>
            <p class="text-sm text-stone-600 mt-1">
                {{ $opportunity->venueProfile->display_name }}
                @if ($opportunity->venueProfile->city || $opportunity->venueProfile->state)
                    · {{ collect([$opportunity->venueProfile->city, $opportunity->venueProfile->state])->filter()->join('/') }}
                @endif
                @if ($opportunity->event_date)
                    · {{ $opportunity->event_date->format('d/m/Y') }}
                @endif
            </p>
            @if ($opportunity->genres->isNotEmpty())
                <p class="text-xs text-stone-500 mt-1 truncate">{{ $opportunity->genres->pluck('name')->join(', ') }}</p>
            @endif
        </a>
    </li>
@endforeach
