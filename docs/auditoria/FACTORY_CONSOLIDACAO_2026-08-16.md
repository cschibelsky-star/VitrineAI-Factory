# Auditoria de Consolidação — Vitrine AI Factory

Data: 2026-08-16

## Objetivo

Consolidar a linhagem da Vitrine AI Factory, separando a arquitetura ativa de artefatos históricos, sem perda de rastreabilidade ou de código potencialmente reutilizável.

## Conclusão executiva

A linha canônica da Factory passa a ser o repositório `cschibelsky-star/VitrineAI-Factory`.

O repositório `cschibelsky-star/VitrineAI-FACTORY-ENTERPRISE-X` deve ser tratado como base histórica/de consolidação e fonte de reaproveitamento. Ele não deve ser usado como aplicação canônica da Factory nem restaurado integralmente em produção.

O antigo `EnterpriseDashboard` não deve ser restaurado como dashboard oficial da Factory. A auditoria identificou sua origem como artefato extraído do `Centro Operacional / CENTRO_OPERACIONAL_UI_V4_TECH_DASHBOARD`, portanto ele pertence à linhagem histórica do Centro Operacional e não ao núcleo canônico da Factory.

## Classificação

### MANTER — Factory canônica

- `VitrineAI-Factory`
- `factory-core`
- Products
- Blueprints
- Capabilities
- Agents
- Engines
- Missions
- Builder
- Factory Intelligence
- Pipeline de build/provisionamento
- Deploy Center, após adequação à infraestrutura atual
- Histórico de builds e releases
- Playbooks, engineering standards e documentação da Factory

### MIGRAR / REAPROVEITAR

Do `VitrineAI-FACTORY-ENTERPRISE-X`, reaproveitar somente componentes após avaliação individual de dependências e aderência à arquitetura Core × Factory × Flow:

- contratos e manifestos de produtos;
- serviços de build e produção;
- validadores;
- integrações de infraestrutura úteis;
- documentação técnica e inventários;
- componentes de automação que não pertençam ao Core nem a produtos específicos.

### RETIRAR DA ARQUITETURA ATIVA / ARQUIVAR

- `EnterpriseDashboard.php` e respectiva view como dashboard oficial da Factory;
- UI histórica `CENTRO_OPERACIONAL_UI_V4_TECH_DASHBOARD`;
- cópias extraídas de Centro Operacional e Centro IA usadas apenas para auditoria;
- patches de HostGator/cPanel que tenham sido superados pela infraestrutura VPS, mantendo-os apenas como histórico enquanto ainda houver projetos legados em HostGator;
- builds intermediárias já substituídas por versões posteriores.

### NÃO APAGAR AINDA

Não apagar fisicamente:

- repositório `VitrineAI-FACTORY-ENTERPRISE-X`;
- diretórios `extraidos/`;
- branches históricas;
- migrations ou código legado que possam explicar dependências de bancos/release existentes.

Esses itens devem ficar fora do fluxo operacional, mas preservados até concluir auditoria de dependências em VPS, banco e deploys existentes.

## Arquitetura operacional aprovada

- Core: clientes, planos, licenças, financeiro, comercial, Centro IA e capacidades SaaS compartilhadas.
- Factory: builds, blueprints, componentes, versões, QA, releases, implantação e atualização.
- Flow/n8n: automações e orquestração de processos.
- Produtos: código e domínio funcional próprios, consumindo capacidades do Core/Factory/Flow conforme contrato.

## Decisão sobre factory.vitrineiapro.com.br/admin/enterprise-dashboard

Não restaurar a rota antiga.

Caso o subdomínio `factory.vitrineiapro.com.br` volte a ser publicado, ele deve apontar para a Factory canônica/evoluída e não para o dashboard histórico do Centro Operacional.

## Próximas ações

1. Auditar a versão atualmente implantada/última versão válida da Factory no VPS/HostGator.
2. Comparar `main`, `package-8.0` e branches `core-*` para definir baseline única de código.
3. Mapear funcionalidades do Enterprise X contra Core × Factory × Flow.
4. Migrar apenas componentes úteis ainda ausentes da Factory canônica.
5. Só depois da verificação de dependências, remover fisicamente duplicações comprovadamente obsoletas.

## Regra de descarte

Nenhum código, banco, migration, release ou arquivo histórico será apagado somente por aparentar duplicidade. O descarte físico exige comprovação de substituição, ausência de dependências e existência de histórico recuperável no Git.
