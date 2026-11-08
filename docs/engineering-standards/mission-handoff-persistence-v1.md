# Persistência de devolução na missão canônica — piloto OBSERVER

Contrato primeiro, sem migration: `App\Services\Governance\MissionHandoffRepository` opera na tabela existente `factory_missions`, usada por `FactoryMission`. Somente `status`, `updated_at` e a seção `mission_dna.handoff_supervision` são atualizados. Demais chaves de DNA são preservadas; não se cria cadastro paralelo de missão, agente ou campanha.

## Configuração e fronteira de confiança

O serviço recebe uma conexão PDO confiável para MySQL/InnoDB ou SQLite, sem transação externa ativa. Não existe endpoint público, DSN padrão, configuração de produção nem processo de polling novo. Quem chama deve autenticar/autorizar o usuário e carregar escopo e identidades do servidor, nunca do JSON do executor.

A missão deve ser previamente vinculada pelo owner autorizado a productId (coluna canônica) e tenantId/projectId (seção confiável de governança). O adapter recusa falta de vínculo, escopo divergente, tarefa inválida ou inconsistência entre status canônico e de governança. Isto não implementa o login/ACL do Core; é uma pré-condição obrigatória do futuro host.

Formato mínimo preexistente em `mission_dna.handoff_supervision`:

```json
{
  "schemaVersion": "1.0",
  "scope": {"tenantId": "tenant-authorized", "projectId": "marketing"},
  "revision": 0,
  "status": "ready_for_via",
  "task": {
    "id": "engineering-task-id",
    "repository": "owner/repo",
    "resultSha": "0000000000000000000000000000000000000000",
    "gate": "G3",
    "correctionOwner": "engineering-owner",
    "attempts": 0,
    "maxAttempts": 3,
    "budgetExceeded": false
  },
  "audit": [],
  "outbox": [],
  "receipts": []
}
```

SHA/IDs acima são ilustrativos, não evidências. O adapter não cria nem inicializa missões; o provisionamento autorizado e o vínculo de owner ao FactoryAgent são pré-requisitos do futuro host. Os nove agentes criativos do Marketing permanecem separados dos papéis de engenharia.

## Porta interna v1

| Método | Garantia |
| --- | --- |
| load(id, scope) | missão canônica, revisão e tarefa no escopo autorizado |
| recordAssessment(id, scope, {expectedRevision, plan}) | grava status, auditoria, recibo e evento passivo em uma transação |
| recordCorrection(id, scope, {expectedRevision, correctedBy, resultSha}) | registra submissão de novo SHA pelo responsável; não afirma execução de correção nem valida GitHub |
| correctionCandidates(id, scope) | somente evento da revisão/SHA/responsável atuais; dispatchAllowed=false |
| inspectNextAgent(id, scope) | allowed=false em qualquer estado OBSERVER |

Plan contém taskId/resultSha/failedGate, state, correctionOwner, reasons e flags canAdvance/dispatchAllowed/productionAllowed=false. Qualquer flag permissiva, responsável divergente ou SHA/tarefa divergente é recusado. O plan deve vir do serviço VIA confiável, nunca de uma requisição de cliente.

## Atomicidade, retry e correção

MySQL usa transação com SELECT FOR UPDATE; SQLite usa BEGIN IMMEDIATE. Depois do lock, expectedRevision impede avaliação obsoleta. A identidade do evento inclui missão, tenant/projeto, tarefa, repositório, SHA, gate e tentativa; repetição idêntica retorna o recibo sem duplicar auditoria/outbox. Uma decisão diferente para a mesma tentativa é recusada. Alteração do SHA invalida a tentativa anterior.

O evento correction_required é passivo e destinado ao responsável canônico. Não existe worker executável. Submeter uma correção exige estado needs_correction, responsável, revisão e limites válidos, novo SHA e ausência de reutilização de commit histórico. A tarefa passa a awaiting_evaluation com contador incrementado; todos os checks/reviews precisam ser coletados novamente. Eventos antigos ficam no histórico e deixam de ser candidatos.

Estados bloqueados/escalados/pendentes nunca liberam o próximo agente. Histórico tem limite de 1000 registros/recibos; ultrapassá-lo gera falha explícita para intervenção, sem truncar evidências. Falha no UPDATE reverte também recibo, auditoria e outbox.

## Validação e limites

`php tests/test-mission-handoff-repository.php` executa cenários reais com PDO SQLite em memória, usando as colunas existentes da tabela. O teste integrado na VIA usa este adapter por um bridge em `tests/fixtures/mission-pdo-bridge.php`, exclusivamente sobre arquivos temporários SQLite: processos separados comprovam reinício, retry concorrente, devolução, SHA antigo, revisão antiga e reavaliação sem avanço. O bridge não é transporte de produção.

Ainda não comprovados: runtime Laravel/VPS, lock InnoDB no ambiente real, Core ACL, credenciais de GitHub reais, FactoryAgent efetivo, entrega a um executor e correção efetiva do código. HML exige roteiro e autorização separados, com backup/rollback, conexão de privilégio mínimo e identificação dos hosts. CI SQLite não substitui homologação MySQL.
