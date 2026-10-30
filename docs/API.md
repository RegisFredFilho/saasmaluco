# API SindiGig (`/api/v1`)

Base JSON para clientes externos (mobile futuro). **Mesmas regras de negócio da web** via Policies/scopes. Auth por **Laravel Sanctum** (Bearer token).

URL local: `http://localhost:8080/api/v1`

## Auth

| Método | Path | Auth | Descrição |
|--------|------|------|-----------|
| POST | `/auth/login` | — | Body: `email`, `password`, `device_name?` → `{ token, token_type, user }` |
| GET | `/auth/me` | Bearer | Conta autenticada |
| PATCH | `/auth/me` | Bearer | Atualiza conta (`name`, `email`, geo, `notify_email`, `avatar`) |
| POST | `/auth/logout` | Bearer | Revoga o token atual |

Contas desativadas não logam; token ativo de conta suspensa recebe `403` JSON.

## Público (descoberta)

| Método | Path | Filtros |
|--------|------|---------|
| GET | `/health` | — |
| GET | `/artists` | `q`, `city`, `state`, `region`, `kind`, `presentation_type`, `genre_id` |
| GET | `/artists/{slug}` | — |
| GET | `/venues` | `q`, `city`, `state`, `genre_id` |
| GET | `/venues/{slug}` | — |
| GET | `/opportunities` | `q`, `city`, `state`, `genre_id`, `event_from`, `event_to` |
| GET | `/opportunities/{id}` | — |

Listagens paginadas (12/página). Perfis privados / oportunidades não descobertas seguem as mesmas regras da web.

## Autenticado — notificações

| Método | Path |
|--------|------|
| GET | `/notifications` |
| POST | `/notifications/read-all` |
| POST | `/notifications/{id}/read` |

## Autenticado — candidaturas (Etapa 14)

| Método | Path | Body / notas |
|--------|------|----------------|
| GET | `/applications` | Minhas candidaturas (artista) |
| POST | `/opportunities/{id}/applications` | `artist_profile_id`, `message?` |
| PATCH | `/opportunities/{id}/applications/{application}` | `status`, `venue_notes?` (venue) |
| DELETE | `/opportunities/{id}/applications/{application}` | Retirada (artista) |

Aceitar candidatura pode devolver `conversation_id` (negociação aberta).

## Autenticado — agenda (Etapa 14)

| Método | Path | Body |
|--------|------|------|
| GET | `/bookings` | — |
| GET | `/bookings/{id}` | — |
| POST | `/bookings/{id}/complete` | Conclusão dual (mesmo fluxo web) |
| POST | `/bookings/{id}/cancel` | `cancellation_reason` |

## Autenticado — rede (Etapa 14)

| Método | Path |
|--------|------|
| POST/DELETE | `/artists/{slug}/follow` |
| POST/DELETE | `/artists/{slug}/favorite` |
| POST/DELETE | `/venues/{slug}/follow` |
| POST/DELETE | `/venues/{slug}/favorite` |

## Exemplo

```http
POST /api/v1/auth/login
Content-Type: application/json

{"email":"voce@exemplo.com","password":"secret","device_name":"ios"}
```

```http
GET /api/v1/auth/me
Authorization: Bearer {token}
Accept: application/json
```

## Autenticado — negociações (Etapa 15)

| Método | Path | Body / notas |
|--------|------|----------------|
| GET | `/conversations` | Lista paginada |
| GET | `/conversations/{id}` | `data` + `messages` (paginado) + `proposals` + `meta` |
| POST | `/conversations/{id}/messages` | `body` |
| POST | `/conversations/{id}/proposals` | `amount`, `conditions?`, `valid_until?` |
| POST | `/conversations/{id}/proposals/{proposal}/respond` | `decision`: `accepted` \| `rejected` |

Aceitar proposta devolve `booking` e dispara as mesmas notificações da web.

## Admin (Etapa 18) — requer `is_platform_admin`

| Método | Path | Body |
|--------|------|------|
| GET | `/admin` | Stats + auditoria recente |
| GET | `/admin/users` | `q?` |
| POST | `/admin/users/{id}/suspend` | `deactivation_reason` |
| POST | `/admin/users/{id}/reactivate` | — |
| GET | `/admin/reports` | `filter=open\|resolved\|all` |
| POST | `/admin/reports/{id}/hide` | `hidden_reason` |
| POST | `/admin/reports/{id}/dismiss` | `resolution_note` |
| POST | `/admin/reviews/{id}/unhide` | — |

Mesmas regras e auditoria do painel web `/admin`.

## Autenticado — meus perfis (Etapa 19)

| Método | Path | Notas |
|--------|------|--------|
| GET/POST | `/me/artists` | Listar / criar |
| PATCH | `/me/artists/{id}` | Mesmas regras de `UpdateArtistProfileRequest` |
| GET/POST | `/me/venues` | Listar / criar |
| PATCH | `/me/venues/{id}` | Mesmas regras de `UpdateVenueProfileRequest` |

Uploads (`avatar`, `photo`, `cover`) via `multipart/form-data`.

## Fora do escopo atual

Convites de banda via API, push, mídia/experiências via API. Sem app Flutter no MVP.
