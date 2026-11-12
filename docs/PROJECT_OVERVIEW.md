# Visão do Projeto — Plataforma de Música ao Vivo

**Nome de trabalho:** SindiGig  
**Base técnica:** [RegisFredFilho/saasmaluco](https://github.com/RegisFredFilho/saasmaluco)  
**Data da auditoria (Etapa 0):** 2026-10-21  
**Branch auditada:** `main` (commit `d853689` — estrutura inicial Laravel e Docker)  
**Branch com tooling adicional:** `develop` (CI, Node no Compose, `package-lock.json`, senhas de desenvolvimento no `.env.example`)

---

## Objetivo do produto

Plataforma profissional que conecta músicos, bandas, DJs, casas de show, bares, restaurantes, produtores e organizadores de eventos, digitalizando o relacionamento desde a descoberta até a realização do show.

A experiência combina conceitos de:

- Rede profissional (perfis, portfólio, reputação, networking)
- Descoberta e páginas detalhadas (filtros, localização, disponibilidade)
- Marketplace (oportunidades, candidaturas, propostas, contratação)
- Agenda profissional (disponibilidade, compromissos, histórico)

O produto **não** é apenas um calendário ou um mural de anúncios. É uma **rede profissional da música ao vivo**.

---

## Público-alvo

### Prioridade do MVP

1. **Artistas** (individuais, bandas, DJs — com o mesmo núcleo de perfil artístico)
2. **Estabelecimentos** (casas de show, bares, restaurantes com música ao vivo)

### Públicos cobertos pelos mesmos fundamentos (sem complexidade extra no MVP)

3. Produtores e organizadores de eventos
4. Outros tipos que reutilizem identidade + perfil profissional

### Papel separado

- **Administrador da plataforma** — independente dos perfis profissionais (artista/estabelecimento)

---

## Problema que o produto resolve

Hoje, contratação de shows costuma depender de WhatsApp, indicações informais e páginas soltas. Isso gera:

- Dificuldade para encontrar artistas e espaços compatíveis
- Falta de histórico profissional verificável
- Negociações sem registro estruturado
- Agenda e reputação fragmentadas
- Pouca transparência sobre condições, estrutura técnica e disponibilidade

A plataforma centraliza descoberta, candidatura, negociação, booking e reputação em um fluxo único.

---

## Escopo inicial do MVP

### Incluído (etapas 1–12)

| Área | Escopo |
|------|--------|
| Identidade | Cadastro, login, logout, recuperação de senha, perfil básico, foto, localização |
| Perfis profissionais | Artista e estabelecimento (páginas públicas editáveis pelo dono) |
| Descoberta | Busca/filtros de artistas, estabelecimentos e oportunidades |
| Rede | Seguir, favoritar, contagens, feed simples se couber |
| Marketplace | Oportunidades, candidaturas, mensagens, propostas |
| Contratação | Booking, agenda, conclusão explícita do show, experiência verificada |
| Reputação | Avaliações pós-booking elegível |
| Notificações | Internas persistidas (e-mail/push depois) |
| Admin | Moderação, suspensão, indicadores básicos, auditoria |
| API futura | Preparar `/api/v1/` para apps mobile (sem app Flutter no MVP) |

### Explicitamente fora do MVP

- Venda de ingressos própria
- Pagamentos / comissão automática / assinaturas
- Contratos digitais complexos
- Integrações obrigatórias (Spotify, Instagram, WhatsApp)
- IA de recomendação
- Streaming de áudio/vídeo
- Marketplace de equipamentos
- Gestão completa de festivais
- Microserviços
- App Flutter completo
- Infraestrutura de produção complexa

---

## Estado atual do repositório (pós-auditoria)

O workspace local estava **vazio**. A base obrigatória `saasmaluco` foi clonada para `C:\xampp\htdocs\sindigig`.

O repositório de referência **não** contém um SaaS de domínio implementado. É um **esqueleto Laravel 13** com:

- Página welcome padrão
- Model `User` e migrations de users/sessions/cache/jobs
- Empacotamento Docker (nginx + PHP-FPM 8.4 + MySQL 8.4 + Redis)
- Frontend Blade + Vite + Tailwind CSS 4
- Sem autenticação de produto, sem admin, sem multi-tenant, sem billing

Toda a lógica de música ao vivo será evolução incremental deste skeleton, preservando a arquitetura Laravel encontrada.

---

## Ambiente local (desenvolvimento)

| Ferramenta | Status |
|------------|--------|
| Runtime oficial | **Docker Compose** (PHP 8.4-FPM, nginx, MySQL 8.4, Redis) |
| Docker Desktop | Instalado na Etapa 1 |
| WSL2 | Instalado; reinício do Windows necessário na primeira vez |
| URL | http://localhost:8080 |
| Node.js (host, opcional) | v24+ para build Vite sem o profile `frontend` |
| PHP XAMPP 8.0 | Presente na máquina, **não usado** pelo projeto |

Setup: `docs/LOCAL_SETUP.md`. Arquitetura: `ARCHITECTURE.md`.
