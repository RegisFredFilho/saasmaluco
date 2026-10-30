# Setup inicial do SindiGig (Docker Compose)
# Uso: na raiz do repo, .\scripts\setup.ps1

$ErrorActionPreference = "Stop"
$Root = Split-Path -Parent $PSScriptRoot
Set-Location $Root

$env:Path = [System.Environment]::GetEnvironmentVariable("Path", "Machine") + ";" +
    [System.Environment]::GetEnvironmentVariable("Path", "User")

function Assert-Docker {
    $job = Start-Job {
        $env:Path = [System.Environment]::GetEnvironmentVariable("Path", "Machine") + ";" +
            [System.Environment]::GetEnvironmentVariable("Path", "User")
        docker info 1>$null 2>$null
        exit $LASTEXITCODE
    }
    Wait-Job $job -Timeout 20 | Out-Null
    if ($job.State -eq "Running") {
        Stop-Job $job
        Remove-Job $job -Force
        throw "Docker engine nao respondeu a tempo. Abra o Docker Desktop e aguarde Running (WSL2)."
    }
    $code = (Receive-Job $job -ErrorAction SilentlyContinue; $job.ChildJobs[0].Output)
    Remove-Job $job -Force -ErrorAction SilentlyContinue
    docker info 1>$null 2>$null
    if ($LASTEXITCODE -ne 0) {
        throw "Docker engine indisponivel. Abra o Docker Desktop e aguarde o status Running."
    }
}

Write-Host "==> Verificando Docker..."
Assert-Docker

if (-not (Test-Path "$Root\.env")) {
    Copy-Item "$Root\.env.example" "$Root\.env"
    Write-Host "Criado .env (raiz)"
}

if (-not (Test-Path "$Root\app\.env")) {
    Copy-Item "$Root\app\.env.example" "$Root\app\.env"
    Write-Host "Criado app\.env"
}

Write-Host "==> Subindo containers..."
docker compose up -d --build
if ($LASTEXITCODE -ne 0) { throw "docker compose up falhou" }

Write-Host "==> Aguardando MySQL ficar healthy..."
$ready = $false
for ($i = 0; $i -lt 40; $i++) {
    $health = docker inspect --format='{{.State.Health.Status}}' "$(docker compose ps -q mysql)" 2>$null
    if ($health -eq "healthy") {
        $ready = $true
        break
    }
    Start-Sleep -Seconds 3
}
if (-not $ready) {
    Write-Host "Aviso: MySQL ainda nao reportou healthy; tentando seguir."
}

Write-Host "==> Composer install..."
docker compose exec -T app composer install --no-interaction
if ($LASTEXITCODE -ne 0) { throw "composer install falhou" }

$appEnv = Get-Content "$Root\app\.env" -Raw
if ($appEnv -notmatch "APP_KEY=base64:") {
    Write-Host "==> Gerando APP_KEY..."
    docker compose exec -T app php artisan key:generate --force
}

Write-Host "==> Migrations..."
docker compose exec -T app php artisan migrate --force
if ($LASTEXITCODE -ne 0) { throw "migrate falhou" }

$useHostNode = $null -ne (Get-Command npm -ErrorAction SilentlyContinue)
if ($useHostNode) {
    Write-Host "==> Frontend (npm no host)..."
    Push-Location "$Root\app"
    npm ci
    npm run build
    Pop-Location
} else {
    Write-Host "==> Frontend (profile compose frontend)..."
    docker compose --profile frontend run --rm node npm ci
    docker compose --profile frontend run --rm node npm run build
}

Write-Host "==> Testes..."
docker compose exec -T app php artisan test
if ($LASTEXITCODE -ne 0) { throw "testes falharam" }

Write-Host ""
Write-Host "Setup concluido. Acesse http://localhost:8080"
Write-Host "Health: http://localhost:8080/up"
