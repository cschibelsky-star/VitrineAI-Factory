# Evidência de execução isolada na VPS — 15/10/2026

## Contexto
- Projeto operacional: `factory-asset-intake-validation-20261015`.
- Compose: `docker-compose.asset-validation.yml`.
- Serviço: `asset-validation`.
- Branch: `feat/factory-asset-ingestion-20261014`.
- Rota: Super Centro Operacional (`project_compose_service_execute`), após V5 retornar `controlled_service_not_allowed`.

## Execução confirmada
1. `build`: código de saída 0; imagem `factory-asset-intake-validation-20261015-asset-validation:latest` construída na VPS.
2. `up`: código de saída 0; container `factory-asset-intake-validation-20261015-asset-validation-1` criado/iniciado.
3. `docker_container_health` (leitura): status `exited`, `exit_code=0`, início `2026-10-15T12:23:53Z`, término `2026-10-15T12:23:56Z`.
4. Logs registram conclusão `OK`; traces de autorização negada são esperados nos testes negativos. Não concluir que o upload de produção está ativo.

## Escopo e gate ainda aberto
- Não houve merge, push dos PNGs originais em projetos reais, implantação no host FastMCP compartilhado, alteração de segredos, nem deploy de produção.
- O endpoint de upload requer identidade/escopo verificáveis no host canônico e implantação controlada do binding.
- Exigir E2E com arquivos reais em dois projetos, SHA no GitHub e HTTP, screenshots HML e aprovação visual antes de liberar a esteira.

**Estado:** runner isolado validado na VPS; integração operacional do endpoint ainda pendente.
