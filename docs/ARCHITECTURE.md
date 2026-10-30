# Arquitetura — Estado Atual e Direção

**Base:** evolução do SaaSMaluco (não um projeto greenfield paralelo)  
**Auditoria:** Etapa 0 — 2026-10-21

---

## 1. Arquitetura atual

### Layout do repositório

```
sindigig/                          # raiz do monorepo de desenvolvimento
├── .env.example                   # variáveis do Compose (APP_*, DB_*, REDIS_*)
├── .gitignore
├── compose.yaml                   # app, nginx, mysql, redis
├── Dockerfile                     # php:8.4-fpm + extensões + Composer
├── nginx/default.conf             # root → /var/www/html/public
├── docs/                          # documentação do produto (esta pasta)
└── app/                           # aplicação Laravel (código principal)
    ├── app/
    │   ├── Http/Controllers/Controller.php
    │   ├── Models/User.php
    │   └── Providers/AppServiceProvider.php
    ├── bootstrap/app.php          # routing, middleware, exceptions
    ├── config/                    # auth, database, session, queue, etc.
    ├── database/migrations/       # users, cache, jobs
    ├── public/                    # document root HTTP
    ├── resources/
    │   ├── css/app.css            # Tailwind 4
    │   ├── js/app.js
    │   └── views/welcome.blade.php
    ├── routes/web.php             # GET /
    ├── routes/console.php
    ├── tests/                     # PHPUnit (smoke)
    ├── composer.json              # laravel/framework ^13.17
    └── package.json               # Vite 8 + Tailwind 4
```

Não há `routes/api.php` registrado. Não há Policies, Middleware customizado, Livewire, Inertia, Sanctum, Jetstream, Breeze ou Spatie.

### Componentes principais

| Componente | Tecnologia | Versão / observação |
|------------|------------|---------------------|
| Framework | Laravel | **13.37.0** (`composer.lock`), require `^13.17` |
| PHP (projeto) | `^8.3` | Docker usa **8.4-fpm** |
| ORM | Eloquent | padrão Laravel |
| Frontend | Blade + Vite + Tailwind CSS 4 | sem SPA |
| Auth | Guard `web` (session) + **Laravel Breeze (Blade)** | cadastro, login, logout, reset, verificação opcional |
| Queue / Cache / Session (defaults app) | `database` | Redis disponível no Compose |
| Banco (Compose) | MySQL **8.4** | |
| Proxy HTTP (Compose) | nginx:alpine → porta **8080** | |
| Testes | PHPUnit **12** | SQLite in-memory nos testes |
| CI (branch `develop`) | GitHub Actions | PHP 8.4 + `pint` + build frontend |

### Fluxo de requisições (atual)

```
Browser
  → nginx:8080 (ou Apache/XAMPP apontando para app/public)
    → PHP-FPM / mod_php
      → public/index.php
        → bootstrap/app.php
          → routes/web.php
            → view welcome
```

Health check: `GET /up`.

Exceções: respostas JSON quando path é `api/*` ou `expectsJson()` — preparação útil para API futura, sem rotas API ainda.

### Banco de dados (schema atual)

| Tabela | Função |
|--------|--------|
| `users` | id, name, email (unique), email_verified_at, password, remember_token, timestamps |
| `password_reset_tokens` | email, token, created_at |
| `sessions` | sessões persistidas |
| `cache` / `cache_locks` | cache em DB |
| `jobs` / `job_batches` / `failed_jobs` | filas |

Único model de domínio: `App\Models\User` (fillable: name, email, password; password hashed).

### Autenticação

- Provider Eloquent → `User`
- Password broker configurado (expire 60 min)
- Sem Sanctum/Passport
- Sem roles/permissions
- Welcome page só exibe links de login se `Route::has('login')` (hoje não há)

### Dependências importantes

**PHP (`app/composer.json`):**

- `laravel/framework` ^13.17
- `laravel/tinker` ^3.0
- Dev: phpunit, pint, collision, faker, mockery, pail, pao

**Frontend (`app/package.json`):**

- `vite` ^8.0.0
- `laravel-vite-plugin` ^3.1
- `tailwindcss` / `@tailwindcss/vite` ^4.0.0
- `concurrently` ^10.0.3

**Presentes além do skeleton:** Breeze (web auth), Sanctum (API tokens, Etapa 13).

**Ausentes (não instalar sem necessidade):** Cashier, Spatie Permission, Livewire, Inertia, Horizon, Telescope.

---

## 2. Direção arquitetural recomendada (produto)

Manter **monólito modular Laravel** na pasta `app/`, com:

| Camada | Responsabilidade |
|--------|------------------|
| `routes/web.php` + `routes/api.php` | Web + API versionada (`/api/v1/`, Sanctum) |
| Controllers finos / Form Requests | Validação de entrada |
| Policies / Gates | Autorização por recurso |
| Services / Actions | Casos de uso e regras de negócio |
| Eloquent Models + Migrations | Persistência e integridade |
| `InAppNotifier` + `app_notifications` | Notificações in-app idempotentes (Etapa 11); e-mail opcional (Etapa 17); push fora |
| `/admin` + `AdminAuditor` | Moderação MVP (Etapa 12); `is_platform_admin` ≠ perfil profissional |
| PHPUnit Feature/Unit | Fluxos críticos do marketplace |

### Separação de identidades (produto)

```
User (conta autenticada)
  ├── ArtistProfile (1..n possíveis, se confirmado)
  ├── VenueProfile (1..n possíveis, se confirmado)
  └── Admin (papel de plataforma — separado)
```

Perfis profissionais **não** substituem a conta. Bandas devem poder ter membros sem compartilhar credenciais pessoais (modelagem na Etapa 2–3).

### API (`/api/v1` — Etapa 13)

- Rotas em `routes/api.php`, prefixo `api/v1` em `bootstrap/app.php`
- Auth: **Laravel Sanctum** (personal access tokens / Bearer)
- Controllers em `App\Http\Controllers\Api\V1` + `JsonResource`
- Mesmas Policies/scopes da web; conta inativa → JSON 403
- Docs de superfície: `docs/API.md`

Não desenvolver Flutter no MVP.

---

## 3. Decisões arquiteturais registradas

| ID | Decisão | Status |
|----|---------|--------|
| A1 | Evoluir o skeleton SaaSMaluco; não trocar Laravel/Eloquent/Blade/Vite/Tailwind | Confirmada |
| A2 | Monólito modular; sem microserviços no MVP | Confirmada |
| A3 | Domínio de música ao vivo será modelado por migrations versionadas sobre o schema atual | Confirmada |
| A4 | Admin da plataforma ≠ perfil artista/estabelecimento | Confirmada (regra de negócio) |
| A5 | Preferir soluções locais sem serviços pagos obrigatórios | Confirmada |
| A6 | Ambiente de execução preferencial na Etapa 1 | **Pendente confirmação** (ver abaixo) |

### Decisão A6 — runtime local

**Confirmada (Etapa 1):** Docker Compose é o runtime oficial de desenvolvimento.

| Item | Estado na Etapa 1 |
|------|-------------------|
| Docker Desktop | Instalado (CLI 29.x) |
| WSL2 | Instalado; **requer reinício do Windows** antes do engine Linux estabilizar |
| Compose | `compose.yaml` com app, nginx, mysql (healthcheck), redis, node (profile `frontend`) |
| URL | http://localhost:8080 |
| Setup | `docs/LOCAL_SETUP.md`, `scripts/setup.ps1` |

XAMPP PHP 8.0 permanece **não suportado** para esta aplicação. Redis no Compose é mantido; a app pode continuar com cache/session/queue em `database`.

---

## 4. Branch `develop` vs `main`

Diferenças relevantes em `develop` (ainda não mergeadas no working tree `main`):

- Senhas de desenvolvimento preenchidas nos `.env.example`
- Serviço `node:22-alpine` no Compose (profile `frontend`)
- `package-lock.json` e workflow CI (testes + Pint + build)
- Ajustes de env da app para MySQL do Compose

**Recomendação:** incorporar o conteúdo útil de `develop` na Etapa 1 (preparação da base), sem alterar regras de negócio de produto.

---

## 5. Segurança (baseline atual)

Pontos positivos:

- `.env` no `.gitignore`
- Password cast `hashed` no model User
- CSRF/session defaults do Laravel quando houver formulários

Pontos de atenção:

- `APP_DEBUG=true` nos exemplos
- Senha DB vazia no `.env.example` da raiz em `main` (`develop` corrige com senha de dev)
- Superfície pequena hoje (poucas rotas); risco crescerá com uploads, mensagens e autorização
- Extensões `gd` e `intl` desabilitadas no `php.ini` do XAMPP (relevantes para imagens e i18n)

---

## 6. Compatibilidade XAMPP

| Item | Compatível? | Nota |
|------|-------------|------|
| PHP 8.0.30 | **Não** | Exige ≥ 8.3 |
| MariaDB 10.4 | Provável sim para schema atual | Validar SQL futuro |
| Apache → `app/public` | Sim, com vhost/Alias | Document root não deve ser a raiz do repo |
| Redis | Não obrigatório | Defaults usam database |
| Vite/Node | Sim (Node 24 presente) | Build local possível |
| Docker Compose do repo | Não sem instalar Docker | |

Documentação de setup operacional será detalhada na Etapa 1 após escolha do runtime.
