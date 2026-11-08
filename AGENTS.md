# Protocolo operacional dos agentes — Vitrine AI Factory (v1.0)

Este documento orienta agentes de IA, independentemente de modelo ou fornecedor. Complementa, sem substituir, a Constituição e os Engineering Standards existentes.

## Autoridade e segurança
- GitHub é a fonte de verdade. Verificar repositório, branch, alterações em andamento e requisitos antes de editar.
- CONTINUE = leitura/diagnóstico. EXECUTAR = escrita apenas no escopo imediato autorizado e em branch isolada.
- Merge, deploy, produção, migrações aplicadas, exclusões, segredos e chamadas pagas exigem aprovação específica.
- Jamais presumir que conectores, MCPs, hooks ou pipelines funcionam por estarem descritos em arquivos.
- Preservar arquitetura, contratos e tecnologia existente. Mudança de contrato precede mudança de consumidores.

## Fluxo mínimo
1. TRIAGEM: definir tarefa, responsável, contratos, riscos, critérios observáveis e limites.
2. PLANEJAMENTO: analisar reutilização, impacto, dependências e testes apropriados.
3. IMPLEMENTAÇÃO: mudanças mínimas e isoladas; sem inventar provas de funcionamento.
4. VERIFICAÇÃO: executar checks de fato; registrar comando, ambiente, resultado e evidência.
5. REVISÃO: QA independente e revisão de segurança proporcional ao risco.
6. VIA: verificar evidências e decidir liberar, devolver para correção ou escalar.
7. HANDOFF: registrar histórico, commits, falhas, riscos e próximo responsável.

É proibido declarar conclusão quando um check obrigatório falhou, não foi executado ou não possui evidência. "Não executado" não significa "aprovado". Falhas retornam para correção; tentativas ou orçamento excedidos exigem escalonamento.

## Especialidades
- Arquiteto: compatibilidade, contratos e decisão registrada para alterações estruturais.
- Desenvolvedor: código claro, seguro, sustentável, testável, consistente e com testes de regressão.
- QA: reproduzir requisitos, testes negativos e análise de regressão independente.
- Segurança: segredos, autenticação, autorização, integridade de dados e dependências.
- DevOps: deploy reversível e rastreável em homologação; produção só com aprovação específica.
- VIA: inspecionar evidências verificáveis, não somente respostas textuais dos próprios agentes.

Critérios formais e formato do handoff: `docs/engineering-standards/agent-excellence-v1.md`.
Validador offline de handoff: `tools/verify-agent-handoff.php`.
