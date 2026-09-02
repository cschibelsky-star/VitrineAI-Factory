# Vitrine Factory Agent Bridge

Serviço de orquestração autônoma entre a Vitrine IA Pro Factory, GitHub/Copilot e o MCP V5.

## Responsabilidade

O Bridge coordena o ciclo de desenvolvimento sem depender de intervenção humana em operações de baixo risco:

1. recebe uma especificação técnica da Factory;
2. cria/acompanha a tarefa no GitHub;
3. aciona um agente de desenvolvimento (inicialmente Copilot);
4. acompanha branch, PR, review e CI;
5. aplica a política de autonomia;
6. autoriza merge quando todos os gates permitidos estiverem verdes;
7. solicita ao V5 build/deploy em HML;
8. coleta health checks, smoke tests e evidências;
9. bloqueia produção por padrão.

## Princípio de segurança

O Bridge não possui acesso direto à VPS. Toda operação de projeto/HML deve passar pelo MCP V5. Produção é negada por política na versão v1.

## Endpoints iniciais

- `GET /health`
- `GET /policy`
- `POST /decisions/evaluate`
- `POST /tasks/plan`

## Estados previstos

`received -> planned -> delegated -> coding -> pr_open -> ci_running -> review -> merge_ready -> merged -> hml_deploying -> hml_validating -> homologated`

Falhas devem transitar para `blocked` ou `needs_attention`, com evidências anexadas.
