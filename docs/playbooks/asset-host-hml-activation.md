# Asset Host HML — ativação operacional controlada

Estado: **manifesto criado, não implantado**. Não alterar V5 compartilhado.

## Arquivos versionados
- `tools/asset_host.py`: instancia FastMCP com verificação JWT via chave pública e grants explícitos; sem emissão de tokens.
- `docker-compose.asset-host.yml`: serviço persistente isolado, sem publicação de portas no host.
- `docker-compose.asset-validation.yml`: runner separado para CI, manter inalterado.

## Precondições obrigatórias para ativar
1. Operador deve provisionar **fora do Git** um diretório `ASSET_CONFIG_DIR` com `public.pem` (chave pública confiável) e `grants.json` (permissões por identidade/projeto/branch/checkout/destino), além de `ASSET_AUTH_ISSUER`, `ASSET_AUTH_AUDIENCE`. Jamais usar chave privada no container.
2. `ASSET_PROJECT_DIR` deve apontar a checkout isolado e autorizado; `grants.json` deve nomeá-lo no container como `/workspace`. Sem permitir branch `main` ou `production`.
3. Diretório persistente do intake e checkout devem ser graváveis exclusivamente pelo UID/GID do serviço (65534). Os arquivos de configuração devem ser legíveis pelo serviço e não graváveis por ele. Acesso do proxy restrito ao domínio HML aprovado, sobre TLS, com proteção de rede.
4. Registrar explicitamente o serviço `asset-host` na política de operações controladas para **apenas** esse workspace e esse compose. Essa política não está aplicada.
5. Somente então validar `docker compose -f docker-compose.asset-host.yml config`, `build asset-host`, `up -d asset-host` e health; sem executar no V5 compartilhado.
6. Validar respostas negativas 401 sem bearer/expirado, 403 sem scope ou grant, 400 hash divergente, 413 limite; registrar `tools/list` incluindo `factory_asset_receive`.
7. Transferir um arquivo binário original por `POST /factory/assets/upload?project=...&branch=...`, receber referência opaca, invocar `factory_asset_receive` com `sha256`, conferir hash dos bytes no checkout autorizado. É proibido aceitar somente a execução do CI como prova de transferência.
8. Registrar commit, hora, hash e HTTP no histórico e exigir deploy da HML somente em fluxo específico.

## Formato de grants (EXEMPLO; não autoriza nenhum cliente)
```json
[
  {
    "client_id": "CLIENT_ID_VERIFICADO",
    "project": "vitrine-site-hml",
    "branch": "design/vitrine-showcase-approved-20261014",
    "checkout": "/workspace",
    "destinations": ["assets/img/home", "assets/img/cases"],
    "max_bytes": 26214400
  }
]
```

Sem configuração externa validada, o host deve falhar na inicialização. Sem prova de upload real, manter estado **NÃO HOMOLOGADO**.
