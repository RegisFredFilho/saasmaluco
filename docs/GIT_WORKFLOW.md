# Fluxo Git (padrão do time)

## Destino padrão: `develop`

- Toda feature/fix sobe por **pull request para `develop`**.
- Não usar `main` como base de PR, salvo pedido explícito.
- `develop` é branch protegida: sem push direto; só via PR + checks.
- Deploy de homologação acompanha o push/merge em `develop`.

## Rotina esperada do agente

1. Trabalhar em branch a partir de `develop` atualizada.
2. Commit quando o usuário autorizar.
3. Push + PR com base `develop` quando pedir para subir/abrir PR.
4. Se o PR anterior já tiver sido mergeado, abrir **novo** PR com os commits restantes.
