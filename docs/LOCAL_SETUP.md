# Setup Local — Docker

Ambiente oficial de desenvolvimento do SindiGig (base SaaSMaluco).

## Requisitos

| Ferramenta | Versão / nota |
|------------|----------------|
| Docker Desktop | Com engine Linux (WSL2 no Windows) |
| Git | Qualquer recente |
| (Opcional) Node.js 22+ | Alternativa ao profile Compose `frontend` |

Não é necessário PHP/Composer no host para o fluxo Docker — ambos estão na imagem `app`.

## Primeira configuração

Na raiz do repositório (`sindigig/`):

```powershell
# 1. Variáveis de ambiente (não versionadas)
Copy-Item .env.example .env
Copy-Item app\.env.example app\.env

# 2. Subir a stack
docker compose up -d --build

# 3. Dependências PHP e chave da aplicação
docker compose exec app composer install
docker compose exec app php artisan key:generate

# 4. Migrations
docker compose exec app php artisan migrate

# 5. Frontend (escolha uma opção)
# Opção A — Node no host:
Set-Location app; npm ci; npm run build; Set-Location ..

# Opção B — container Node (profile frontend):
docker compose --profile frontend run --rm node npm ci
docker compose --profile frontend run --rm node npm run build
```

Atalho PowerShell (após Docker pronto):

```powershell
.\scripts\setup.ps1
```

## URL e serviços

| Serviço | Endereço / uso |
|---------|----------------|
| Aplicação (nginx) | http://localhost:8080 |
| Health | http://localhost:8080/up |
| Cadastro / Login | http://localhost:8080/register · `/login` |
| Painel (auth) | http://localhost:8080/dashboard |
| Minha rede (auth) | http://localhost:8080/rede |
| Minhas candidaturas (auth) | http://localhost:8080/candidaturas |
| Negociações (auth) | http://localhost:8080/negociacoes |
| Agenda / bookings (auth) | http://localhost:8080/agenda |
| Notificações in-app (auth) | http://localhost:8080/notificacoes |
| Convites de banda (auth) | http://localhost:8080/convites |
| Admin da plataforma | http://localhost:8080/admin (requer `is_platform_admin`) |
| API v1 | http://localhost:8080/api/v1/health · ver `docs/API.md` |
| Busca de artistas | http://localhost:8080/artistas |
| Página pública artista | http://localhost:8080/artistas/{slug} |
| Busca de estabelecimentos | http://localhost:8080/estabelecimentos |
| Página pública estabelecimento | http://localhost:8080/estabelecimentos/{slug} |
| Busca de oportunidades | http://localhost:8080/oportunidades |
| Oportunidade pública | http://localhost:8080/oportunidades/{id} |
| Gêneros (seed) | `docker compose exec app php artisan db:seed --class=GenreSeeder` |
| MySQL | host Docker `mysql:3306` (rede interna) |
| Redis | host Docker `redis:6379` |
| PHP-FPM | serviço `app` |
| Avatares | `storage/app/public` via `php artisan storage:link` |

Recuperação de senha, verificação de e-mail e **alertas opcionais do SindiGig** usam `MAIL_MAILER=log` (conteúdo em `app/storage/logs`). Opt-out: Minha conta → notificações por e-mail.

Porta HTTP configurável via `APP_PORT` no `.env` da raiz.

## Comandos do dia a dia

```powershell
docker compose up -d
docker compose down
docker compose logs -f nginx app
docker compose exec app php artisan migrate
docker compose exec app php artisan test
docker compose exec app vendor/bin/pint
docker compose exec app php artisan tinker
```

## Testes

- Framework: PHPUnit (`app/phpunit.xml`)
- DB de teste: SQLite `:memory:`
- Execução: `docker compose exec app php artisan test`

## Extensões PHP na imagem

Definidas no `Dockerfile`: `pdo_mysql`, `mbstring`, `bcmath`, `intl`, `zip`, `gd`, `opcache`.

## Banco de dados

- Engine Compose: **MySQL 8.4**
- Credenciais de desenvolvimento: ver `.env.example` (raiz e `app/`)
- **Não** use as senhas de exemplo em produção
- Volumes: `mysql_data` (persiste dados entre `down` sem `-v`)

Para apagar dados do banco: `docker compose down -v` — **somente com autorização explícita**.

Backup/restore e promoção a admin: ver `docs/BACKUP.md`.

## Apache/XAMPP

O runtime oficial é Docker. O XAMPP da máquina **não** serve a app com PHP 8.0.
Não aponte o DocumentRoot do Apache para este projeto enquanto o fluxo Docker estiver ativo na porta 8080, para evitar confusão.

## Problemas comuns

1. **`docker` não encontrado** — instale/inicie o Docker Desktop e reabra o terminal.
2. **Engine não sobe** — aguarde o Docker Desktop ficar “Running”; no Windows confirme WSL2.
3. **Porta 8080 em uso** — altere `APP_PORT` no `.env` da raiz.
4. **500 após subir** — rode `key:generate` e `migrate`; confira `app/.env` (`DB_HOST=mysql`).
5. **Assets sem estilo** — execute `npm run build` (host ou profile `frontend`).
6. **`touch(): Utime failed` em views Blade** — no Windows o bind mount impede `utime` em `storage/framework/views`. O Compose usa `VIEW_COMPILED_PATH=/tmp/laravel-views` (volume `laravel_views`). Recrie o app: `docker compose up -d --force-recreate app`.

## CI

Workflow em `.github/workflows/ci.yml` (branch `develop`/`main`): testes Laravel + Pint + build frontend com Node 22.
