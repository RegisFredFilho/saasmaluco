# Backup e restauração (MVP local / produção inicial)

## O que preservar

| Recurso | Onde | Notas |
|---------|------|--------|
| Banco MySQL | volume Docker `mysql_data` | Contém usuários, perfis, bookings, reviews, auditoria |
| Uploads | `app/storage/app/public` | Avatares, capas, galerias |
| Env de produção | fora do git | `.env` nunca versionado |

Redis do Compose é opcional/cache — não é fonte da verdade do MVP.

## Backup local (Docker)

```powershell
# Dump SQL
docker compose exec -T mysql mysqldump -u"$env:DB_USERNAME" -p"$env:DB_PASSWORD" "$env:DB_DATABASE" > backup-$(Get-Date -Format yyyyMMdd).sql

# Uploads
Compress-Archive -Path app\storage\app\public\* -DestinationPath storage-public-$(Get-Date -Format yyyyMMdd).zip
```

Ajuste usuário/senha/database conforme `app/.env`.

## Restauração

```powershell
Get-Content backup-YYYYMMDD.sql | docker compose exec -T mysql mysql -u"$env:DB_USERNAME" -p"$env:DB_PASSWORD" "$env:DB_DATABASE"
# Extrair zip de storage para app/storage/app/public e rodar storage:link se necessário
```

## Produção (checklist mínimo)

1. Backup automatizado do MySQL (diário) com retenção ≥ 7 dias
2. Backup do disco/objeto de `storage/app/public`
3. Testar restore em staging ao menos uma vez por trimestre
4. Não usar `docker compose down -v` em ambientes com dados reais sem autorização

## Admin da plataforma

Flag `users.is_platform_admin` **não** é concedida no cadastro. Promover:

```powershell
docker compose exec app php artisan tinker
>>> \App\Models\User::where('email', 'voce@exemplo.com')->first()->forceFill(['is_platform_admin' => true])->save();
```
