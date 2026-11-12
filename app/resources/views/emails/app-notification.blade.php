<x-mail::message>
# {{ $title }}

{{ $body }}

@if ($actionUrl)
<x-mail::button :url="$actionUrl">
Abrir no SindiGig
</x-mail::button>
@endif

Você recebe este e-mail porque habilitou alertas por e-mail na conta. Desative em **Minha conta** se preferir só notificações internas.

Thanks,<br>
{{ config('app.name') }}
</x-mail::message>
