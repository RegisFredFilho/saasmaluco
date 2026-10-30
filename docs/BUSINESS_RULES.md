# Regras de Negócio

Documento central de referência para implementações.  
**Versão inicial:** Etapa 0 (2026-10-21) — regras do produto + o que o código existente já implica.

Legenda de status:

- **Confirmada** — deve ser respeitada na implementação
- **Proposta** — recomendação aguardando decisão explícita
- **Pendente** — ambiguidade a resolver antes de modelar de forma irreversível
- **Herdada do código** — invariante do skeleton atual

---

## 1. Identidade e autorização

### Confirmadas

1. Cada conta possui uma identidade autenticada (`User`).
2. Perfis de artista e estabelecimento **não** substituem a identidade do usuário.
3. Ações privadas exigem autenticação e autorização no backend.
4. O usuário só edita recursos que controla ou para os quais possui permissão.
5. A administração da plataforma é **independente** dos perfis profissionais.
6. Credenciais pessoais não devem ser compartilhadas entre integrantes de banda/grupo; a gestão de membros deve usar vínculos autorizados.

### Herdadas do código

7. E-mail de usuário é único (`users.email` unique).
8. Senha é armazenada com hash (cast `hashed` no model).
9. Reset de senha usa `password_reset_tokens` (broker padrão Laravel).

### Decisões da Etapa 2

10. **D1 confirmada:** uma conta pode possuir **um ou mais** perfis profissionais (`artist_profiles` e `venue_profiles`), inclusive várias bandas/artistas.
11. Tipos iniciais: artista (`solo` / `band` / `dj`) e estabelecimento (`venue_profiles`). Admin da plataforma: flag `users.is_platform_admin` (nunca concedida no cadastro).
12. **D2 confirmada (MVP):** desativação soft via `users.deactivated_at` + motivo opcional; login bloqueado; sem hard-delete na UI. Retenção/anonimização administrativa fica para etapa futura.
13. **D3 (MVP):** `MustVerifyEmail` habilitado e rotas de verificação disponíveis; **não** bloqueia painel/perfil no MVP local (`MAIL_MAILER=log`). Pode tornar-se obrigatório depois.
14. **D4 (base):** banda/grupo usa `artist_members` com papéis `owner` / `admin` / `member`. O criador vira `owner` automaticamente — **sem compartilhar credenciais**.
15. **D4b confirmada (Etapa 16):** convite por e-mail de conta existente → aceite/recusa do convidado (`artist_member_invites`) + notificação in-app. Papéis convidáveis: `admin` / `member`. Proprietário não é removível.

### Pendentes remanescentes

| ID | Questão | Impacto |
|----|---------|---------|
| D3b | Quando exigir e-mail verificado em produção? | Middleware `verified` |

---

## 2. Oportunidades (gigs)

### Confirmadas

1. Uma oportunidade possui um estabelecimento responsável.
2. Oportunidades **encerradas** não recebem novas candidaturas.
3. Cada artista (ou entidade artística) candidata-se **apenas uma vez** à mesma oportunidade, salvo regra explícita em contrário.
4. Alterações de status seguem transições permitidas (máquina de estados).
5. Oportunidade **cancelada** não origina novas contratações.

### Status e transições (Etapa 4 — base)

```
draft → open → closed | cancelled | filled
draft → cancelled
```

- Estados terminais: `closed`, `cancelled`, `filled` (sem novas transições na Etapa 4).
- Página pública `/oportunidades/{id}`: visitantes veem `open`, `filled`, `closed`. Rascunhos só para gestores do venue.
- Candidaturas (Etapa 7) implementadas; só `open` aceita novas.

### Decisões

| ID | Decisão |
|----|---------|
| D5 | Orçamento opcional; se vazio, UI mostra “A combinar”. |
| D6 | `slots_count` define quantas aceites; ao atingir o limite a oportunidade vai para `filled`. |
| D7 | Prazo **hard**: após `application_deadline` não há novas candidaturas. |

---

## 3. Candidaturas

### Confirmadas

1. Somente artistas (entidades artísticas autorizadas — owner/admin do perfil) candidatam-se a oportunidades `open`.
2. Estabelecimento responsável visualiza e gerencia candidatos.
3. Cancelamento de candidatura pelo artista só enquanto o status permitir (`submitted` / `under_review` → `withdrawn`).
4. Unicidade: uma candidatura por `artist_profile` + `opportunity` (unique no DB).

### Status (Etapa 7)

```
submitted → under_review → accepted | rejected
submitted → accepted | rejected | withdrawn
under_review → withdrawn
```

### Decisões

| ID | Decisão |
|----|---------|
| D8 | Aceitar candidatura **só** muda status (e pode preencher a oportunidade). Conversa/proposta fica na Etapa 8. |

---

## 4. Propostas

### Confirmadas

1. Uma proposta pertence a uma negociação válida (ligada a oportunidade e/ou candidatura).
2. Registra criador, destinatário, valores, condições, validade e status.
3. Somente o destinatário autorizado aceita ou recusa.
4. Proposta aceita **não** pode ser silenciosamente alterada.
5. Mudanças posteriores geram nova versão/proposta (histórico preservado).
6. Validação de valores e status ocorre no backend.

### Status e decisões (Etapa 8)

```
sent → accepted | rejected | superseded
(nova proposta enviada marca a `sent` anterior como superseded)
```

| ID | Decisão |
|----|---------|
| D9 | Versão imutável + `supersedes_id` / `superseded_by_id`; nova proposta incrementa `version`. |
| D10 | Moeda única **BRL** no MVP. |
| — | Aceitar candidatura abre `Conversation` (1:1 com application). |
| — | Só destinatário (lado oposto ao criador) aceita/recusa. |
| — | Proposta aceita bloqueia novas propostas (booking na Etapa 9). |
| — | Mensagens paginadas; só participantes (gestores venue ↔ gestores artista). |

---

## 5. Booking e agenda

### Confirmadas

1. Booking confirmado possui data, horário, partes e condições acordadas.
2. Não se confirma compromisso incompatível para o mesmo artista no mesmo intervalo (concorrência no backend/DB).
3. Status segue transições válidas.
4. Cancelamentos registram responsável, data e motivo (quando aplicável).
5. Conclusão do show exige **processo explícito** (não só passagem da data).
6. Experiências **verificadas** exigem origem rastreável (booking elegível).
7. Experiências externas manuais são **não verificadas**.

### Status e decisões (Etapa 9)

```
confirmed → completed | cancelled
```

Disponibilidade informada ≠ booking confirmado.

| ID | Decisão |
|----|---------|
| D11 | Sem hold temporário no MVP: aceitar proposta → booking `confirmed`. |
| D12 | Conclusão **dual**: artista e venue confirmam; só então `completed` + experiência verificada. |
| D13 | Horários no fuso do app (`America/Sao_Paulo` gravado no booking); sem conversão multi-fuso ainda. |
| — | Conflito: mesmo artista + intervalo sobreposto com booking `confirmed` bloqueia novo aceite. |
| — | Cancelamento registra usuário, data e motivo. |
| — | Experiência `source=booking`, `is_verified=true`, `booking_id` preenchido. |

---

## 6. Avaliações

### Confirmadas

1. Dependem de contratação elegível (booking concluído conforme regra).
2. Cada participante avalia conforme direito do booking (tipicamente uma vez).
3. Nota dentro da escala definida.
4. Comentários com limite de tamanho.
5. Terceiros não criam/alteram avaliações sem poder administrativo.
6. Média calculada só com avaliações válidas.
7. Deve existir mecanismo admin para denúncias/conteúdo abusivo.

### Decisões (Etapa 10)

8. Escala **1–5** inteiros.
9. Janela: **30 dias** após `completed_at`.
10. Públicas por padrão; ocultação só por `is_platform_admin`.

| ID | Decisão |
|----|---------|
| D14 | Avaliações **independentes** (cada lado pode avaliar sem esperar o outro). |
| D15 | **Sem edição** após publicar no MVP. |
| — | Uma avaliação por lado (`booking_id` + `author_side` unique). |
| — | Artista avalia o venue; venue avalia o artista. |
| — | Denúncia (`review_reports`) disponível a usuários autenticados (exceto o autor). |

---

## 7. Rede profissional

### Confirmadas

1. Seguir ≠ contratação ≠ conexão comercial formal.
2. Favoritos e follows sem duplicidade (unicidade no banco).
3. Contagens devem refletir dados reais.
4. Evitar rede social genérica (curtidas/comentários/stories) no MVP.

### Decisões da Etapa 6

5. Alvos: perfis públicos de artista e estabelecimento (morph `follows` / `favorites`).
6. Usuário autenticado e ativo; não pode seguir/favoritar o próprio perfil.
7. Só perfis `is_public` (privados: 403 se sem autorização de view).
8. Contagens na página pública = `COUNT` real (`follows_count` / `favorites_count`).
9. Feed simples em `/rede`: oportunidades `open` de estabelecimentos seguidos (não é timeline social).

---

## 8. Dados, privacidade e mídia

### Confirmadas

1. Dados privados não aparecem em páginas públicas ou APIs públicas.
2. Uploads validados (tipo MIME real, extensão, tamanho máximo).
3. Logs sem senhas, tokens ou dados sensíveis desnecessários.
4. Exclusão/desativação/retenção respeitam política e legislação aplicável (LGPD).

### Uploads e mídia (Etapas 3–4)

| Tipo | Formatos | Tamanho máx. |
|------|----------|--------------|
| Foto / capa do artista | JPEG, PNG, WebP | 5 MB |
| Portfólio foto | JPEG, PNG, WebP | 8 MB |
| Vídeo | URL YouTube ou Vimeo | — |
| Capa / galeria do estabelecimento | JPEG, PNG, WebP | 5 MB capa · 8 MB galeria |

- **D16 confirmada (MVP):** armazenamento em disco local (`storage/app/public`).
- Experiências manuais: `is_verified = false`, `source = manual`. Usuário **não** pode forçar verificação nem alterar booking_id.
- Avaliações: só via booking `completed` (Etapa 10); média só com `is_hidden = false`.
- Perfil privado: só gestores; público: visitantes via `/artistas/{slug}` ou `/estabelecimentos/{slug}`.
- Cachê / orçamento: `decimal(12,2)` + moeda `BRL`.
- **Estabelecimento (Etapa 4):** proprietário + gestores (`venue_managers`, papéis `owner` / `admin`). Gestores adicionados por e-mail de conta existente — sem compartilhar senha. Contato profissional do venue é separado do e-mail da conta.

### Descoberta (Etapa 5)

- **D17 confirmada (MVP):** para entrar na busca, artista/estabelecimento precisa ser `is_public` **e** ter `city` + `state` preenchidos. Oportunidade aparece só se `status = open` e o venue for descoberta.
- Listagens públicas paginadas (12/página); filtros no servidor (q, cidade, UF, região/artista, gênero, tipo, datas).
- Listas **não** expõem e-mail/telefone de conta nem contato profissional do venue (só na página pública do espaço, se o gestor publicou).
- Índices: `is_public`, geo `(is_public, city, state)`, `kind`/`region` (artista), `(status, event_date)` (oportunidades).

---

## 9. Notificações

### Confirmadas

1. Eventos relevantes geram notificação interna persistida (`app_notifications`).
2. Destinatários corretos apenas (gestores do venue e owner/admin do artista).
3. Evitar duplicatas em reprocessamento (idempotência por `idempotency_key` único).
4. Push fica fora do MVP. E-mail é opcional (Etapa 17).
5. Inbox em `/notificacoes`; marcar como lida redireciona ao `url` do payload quando houver.
6. **D26 (Etapa 17):** após persistir notificação in-app nova, envia e-mail se `preferences.notify_email` (default true) e conta ativa; reprocessamento idempotente **não** reenvia. Local: `MAIL_MAILER=log`.

### Eventos do MVP (Etapa 11)

| Tipo | Quando | Destinatários |
|------|--------|---------------|
| `application.submitted` | Nova candidatura | Gestores do venue |
| `application.accepted` / `rejected` | Venue altera status | Gestores do artista |
| `application.withdrawn` | Artista retira | Gestores do venue |
| `message.created` | Nova mensagem | Participantes exceto o autor |
| `proposal.sent` | Nova proposta | Participantes exceto o autor |
| `proposal.rejected` | Proposta recusada | Criador da proposta |
| `booking.confirmed` | Proposta aceita → booking | Ambos os lados |
| `booking.completed` | Conclusão dual | Ambos os lados |
| `booking.cancelled` | Cancelamento | Outro lado |
| `review.created` | Nova avaliação | Gestores do perfil avaliado |
| `artist_invite.sent` | Convite de banda | Convidado |
| `artist_invite.accepted` | Convite aceito | Gestores do artista |
| `artist_invite.declined` | Convite recusado | Quem convidou |

| ID | Decisão |
|----|---------|
| D14 | Canal primário: in-app; e-mail opcional por preferência (D26). |
| D15 | Idempotência via chave única no DB (reprocessamento seguro). |
| D26 | E-mail espelha o alerta in-app; opt-out em Minha conta (`notify_email`). |

---

## 10. Administração

### Confirmadas

1. Privilégios admin **nunca** concedidos automaticamente a usuários comuns.
2. Suspensão registra motivo.
3. Ações administrativas relevantes são auditáveis.
4. Moderação de denúncias e conteúdo abusivo.

### Decisões (Etapa 12)

| ID | Decisão |
|----|---------|
| D16 | Admin = `users.is_platform_admin`; acesso via middleware `platform.admin` em `/admin/*`. |
| D17 | Suspensão admin reutiliza soft-deactivate (`deactivated_at` + motivo obrigatório); login bloqueado. |
| D18 | Auditoria em `admin_audit_logs` (ator, ação, subject polimórfico, payload). |
| D19 | Fila de denúncias em `/admin/denuncias`; resolução `hidden` ou `dismissed`; unhide auditado. |
| D20 | Promoção a admin só via DB/tinker (sem UI de grant no MVP). |
| D27 | Admin API (`/api/v1/admin/*`) exige Sanctum + `platform.admin`; mesmas ações e auditoria do painel web. |
| D28 | Mutações de perfil na API reutilizam Form Requests/Policies da web (`/auth/me`, `/me/artists`, `/me/venues`). |

---

## 10b. API (Etapa 13)

| ID | Decisão |
|----|---------|
| D21 | Prefixo `/api/v1/`; auth por token Sanctum (não sessão de cookie para clientes API). |
| D22 | API reutiliza Policies e scopes da web; sem regras paralelas. |
| D23 | Superfície inicial: auth + descoberta pública + notificações; demais recursos sob demanda. |
| D24 | Etapa 14: candidaturas, bookings e follow/favorite na API com as mesmas Policies da web. |
| D25 | Etapa 15: conversas/mensagens/propostas na API; aceite de proposta cria booking como na web. |

---

## 11. Fora de escopo (não criar regras/tabelas prematuras)

- Pagamentos, comissões, assinaturas
- Ticketing próprio
- Contratos digitais complexos
- Integrações sociais obrigatórias
- IA de matching
- Streaming

URL externa de ingressos de terceiros pode ser campo futuro sem implementar ticketing.

---

## 12. Regras implícitas do código existente (não violar)

| Invariante | Origem |
|------------|--------|
| Não substituir Laravel/Eloquent/Vite/Tailwind sem justificativa | Arquitetura SaaSMaluco |
| Migrations versionadas; sem alteração manual só no banco | Convenção Laravel |
| Document root HTTP = `app/public` | nginx + Laravel |
| PHP do projeto ≥ 8.3 | `composer.json` |
| Não versionar `.env` real | `.gitignore` |

---

## 13. Decisões necessárias antes/durante Etapas 1–2

Prioridade alta para desbloquear modelagem:

1. ~~**Runtime local**~~ — **Decidido: Docker Compose** (Etapa 1)
2. **D1** múltiplos perfis por conta  
3. **D3** verificação de e-mail  
4. **D10** moeda  
5. **D13** timezone  

Decisões não devem ser inventadas de forma silenciosa e irreversível: documentar aqui a escolha quando forem tomadas.
