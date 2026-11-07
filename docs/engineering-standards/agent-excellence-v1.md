# Padrão de excelência operacional para agentes — v1.0

Status: **padrão documental e gate offline de handoff implementados nesta branch**. Integração com runtime da Factory, VIA, CI, filas e homologação **ainda não implementada**. Este padrão complementa a Constituição e os padrões legados.

## Camadas obrigatórias
1. Global: autorização, auditoria, rastreabilidade, Git, segurança, confidencialidade e escalonamento.
2. Por especialidade: instruções específicas para Arquiteto, Desenvolvedor, QA, Segurança, DevOps, Documentação e VIA.
3. Por projeto: stack atual, contratos, escopo, testes, ambiente, orçamento, riscos e requisitos do produto.

## Contrato operacional por tarefa
Cada tarefa exige identificação, repositório, branch isolada, agente, especialidade, objetivo, escopo, critérios de aceitação, permissões, proibições, limites e artefatos esperados. Funções NÃO ficam presas a GPT, Claude, Roteia ou outro fornecedor.

### Desenvolvedor — padrão de excelência
- Ler requisitos, código existente, riscos, estado Git e contratos.
- Preferir reutilização, alterações pequenas, coesão, baixo acoplamento e controle de erros.
- Preservar compatibilidade, autorização, tratamento de dados, desempenho e acessibilidade quando aplicáveis.
- Testar caminho positivo, erros e regressão; verificar lint, tipos e build quando pertinentes.
- Documentar falhas honestamente; não marcar testes ausentes como aprovados.
- Entregar diff/commit, resumo, testes, evidências, riscos e pendências verificáveis.

### QA independente
- Confrontar implementação com critérios de aceite, executar ou reproduzir testes e registrar resultados e falhas.
- A revisão por outro agente NÃO substitui testes automatizados nem comprova execução real.

### VIA / supervisor
- Conferir origem das evidências, resultado dos checks obrigatórios e separação dos papéis.
- Reprovar diante de falha, check não executado, evidência ausente, escopo excedido ou falta de autorização.
- Escalonar ao atingir limite de tentativas, custos ou políticas; não sobrescrever aprovação humana.

## Gates
G0 Escopo e autorização; G1 Contratos/arquitetura; G2 Implementação; G3 Testes verificáveis; G4 Revisão independente; G5 Homologação pertinente; G6 Handoff e rastreabilidade.

A aprovação depende de **todos** os gates aplicáveis. Pontuação opcional é apenas indicador de melhoria, não substitui gates. Bloqueadores absolutos: falha crítica de segurança, regressão conhecida não aceita, falta de autorização, testes obrigatórios ausentes ou falhos, evidência fabricada.

## Handoff verificável v1
Arquivo JSON com estes campos:
- `schema_version`: `1.0`
- `task_id`, `repository`, `branch`, `agent_id`, `specialty`, `objective`
- `authorization`: `scope`, `approved_for_branch_write` (bool), `production_approved` (bool)
- `base_sha`, `result_sha` (SHA Git de 40 hex); `changed_files` (lista)
- `checks`: lista de objetos `id`, `required` (bool), `status` (`passed`, `failed`, `not_run`), `command`, `evidence`
- `review`: `reviewer_agent_id`, `status` (`approved`, `rejected`, `pending`), `evidence`
- `risks` (lista), `next_owner`, `state` (`ready_for_via`, `blocked`, `needs_correction`)

O gate local de `ready_for_via` exige autorização da escrita na branch, SHA válidos e diferentes, ao menos um arquivo alterado, checks obrigatórios presentes e aprovados com comando e evidência, e parecer independente `approved` com evidência. Este gate **não declara aprovação final da VIA**; somente verifica pré-condições formais. A VIA precisa checar autenticidade externa das evidências.

Exemplo de uso: `php tools/verify-agent-handoff.php caminho/handoff.json`.
Saída: `PASS: ready_for_via` ou falhas com código 1; não consome API.

## Implantação gradual
1. Adotar padrão e validar handoffs offline, sem alterar agentes ativos.
2. Mapear DNA e rotinas existentes em Factory/Enterprise X e VIA; não sobrescrever seus registros.
3. Integrar gate ao runtime e CI depois de testes do caso piloto.
4. Validar piloto de ponta a ponta em HML; só então considerar aplicação obrigatória aos agentes ativos.

## Referência
O ZIP fornecido foi inspiração de metodologia, sem incorporar suas dependências React/Fastify/Supabase, hooks exclusivos de Claude ou permissões MCP não verificadas.
