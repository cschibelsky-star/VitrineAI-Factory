# Auditoria de Consolidação — Vitrine AI Factory

Data: 2026-08-16

## Objetivo

Consolidar a linhagem da Vitrine AI Factory, separando a arquitetura ativa de artefatos históricos, sem perda de rastreabilidade ou de código potencialmente reutilizável.

## Conclusão executiva

A linha canônica da Factory passa a ser o repositório `cschibelsky-star/VitrineAI-Factory`.

O repositório `cschibelsky-star/VitrineAI-FACTORY-ENTERPRISE-X` deve ser tratado como base histórica/de consolidação e fonte de reaproveitamento. Ele não deve ser usado como aplicação canônica da Factory nem restaurado integralmente em produção.

O antigo `EnterpriseDashboard` não deve ser restaurado como dashboard oficial da Factory. A auditoria identificou sua origem como artefato extraído do `Centro Operacional / CENTRO_OPERACIONAL_UI_V4_TECH_DASHBOARD`, portanto ele pertence à linhagem histórica do Centro Operacional e não ao núcleo canônico da Factory.

## Linhagem técnica confirmada

As branches `core-*` não formam uma sequência linear simples. Elas divergem a partir de ancestral comum e implementam blocos distintos que precisam ser consolidados em uma única baseline.

### Blocos que pertencem à baseline

- `core-02-engine-core`: Engine Core.
- `core-03-blueprint-engine`: Blueprint Engine.
- `core-04-capability-engine`: Capability Engine.
- `core-pack-05-mission-agent`: Mission Engine + Agent Engine.
- `core-pack-05-dashboard-builder`: Dashboard próprio da Factory + Builder.
- `core-mockup-engines`: documentação consolidada dos engines e sequência oficial de implementação.

A sequência de implementação documentada no próprio repositório é:

1. Engine Core
2. Blueprint Engine
3. Capability Engine
4. Mission Engine
5. Agent Engine
6. GitHub Engine
7. Dashboard Engine
8. Deployment Engine
9. AI Analysis Engine

## Situação de package-8.0

A branch `package-8.0` não possui ancestral comum com `main`. Portanto, não deve ser tratada como continuação automática da linha canônica. Ela é uma linha paralela/importada e deve ser usada apenas como fonte seletiva de funcionalidades.

Funcionalidades potencialmente reaproveitáveis de `package-8.0`:

- Factory Intelligence Service;
- Deploy Center;
- Marketplace de Templates;
- Pipeline Visual;
- recursos de provisionamento;
- campos/migrations enterprise para templates e projetos.

Nenhum desses componentes deve ser incorporado sem comparação com a baseline consolidada e sem adequação à infraestrutura atual em VPS.

## Classificação

### MANTER — Factory canônica

- `VitrineAI-Factory`
- `factory-core`
- Products
- Engine Core
- Blueprint Engine
- Capability Engine
- Mission Engine
- Agent Engine
- GitHub Engine
- Dashboard Engine
- Deployment Engine
- AI Analysis Engine
- Builder
- pipeline de build/provisionamento
- histórico de builds e releases
- playbooks, engineering standards e documentação da Factory

### MIGRAR / REAPROVEITAR

Do `VitrineAI-FACTORY-ENTERPRISE-X`, reaproveitar somente componentes após avaliação individual de dependências e aderência à arquitetura Core × Factory × Flow:

- contratos e manifestos de produtos;
- serviços de build e produção;
- validadores;
- integrações de infraestrutura úteis;
- documentação técnica e inventários;
- componentes de automação que não pertençam ao Core nem a produtos específicos.

De `package-8.0`, reaproveitar apenas componentes que preencham lacunas da baseline, principalmente deployment, intelligence e provisionamento.

### RETIRAR DA ARQUITETURA ATIVA / ARQUIVAR

- `EnterpriseDashboard.php` e respectiva view como dashboard oficial da Factory;
- UI histórica `CENTRO_OPERACIONAL_UI_V4_TECH_DASHBOARD`;
- cópias extraídas de Centro Operacional e Centro IA usadas apenas para auditoria;
- patches de HostGator/cPanel que tenham sido superados pela infraestrutura VPS, mantendo-os apenas como histórico enquanto ainda houver projetos legados em HostGator;
- builds intermediárias já substituídas por versões posteriores;
- uso do `VitrineAI-FACTORY-ENTERPRISE-X` como aplicação principal da Factory.

### NÃO APAGAR AINDA

Não apagar fisicamente:

- repositório `VitrineAI-FACTORY-ENTERPRISE-X`;
- diretórios `extraidos/`;
- branches históricas;
- `package-8.0`;
- migrations ou código legado que possam explicar dependências de bancos/releases existentes.

Esses itens devem ficar fora do fluxo operacional, mas preservados até concluir auditoria de dependências em VPS, banco e deploys existentes.

## Baseline alvo

A baseline única deverá reunir, nesta ordem lógica:

`Products → Engine Core → Blueprints → Capabilities → Missions → Agents → GitHub Engine → Dashboard/Builder → Deployment → AI Analysis → QA → Release`.

Essa baseline deve nascer em branch própria de consolidação e só substituir a linha operacional depois de testes de migrations, models, resources Filament, services e fluxos de build.

## Arquitetura operacional aprovada

- Core: clientes, planos, licenças, financeiro, comercial, Centro IA e capacidades SaaS compartilhadas.
- Factory: builds, blueprints, componentes, versões, QA, releases, implantação e atualização.
- Flow/n8n: automações e orquestração de processos.
- Produtos: código e domínio funcional próprios, consumindo capacidades do Core/Factory/Flow conforme contrato.

## Decisão sobre factory.vitrineiapro.com.br/admin/enterprise-dashboard

Não restaurar a rota antiga.

Caso o subdomínio `factory.vitrineiapro.com.br` volte a ser publicado, ele deve apontar para a Factory canônica/evoluída e não para o dashboard histórico do Centro Operacional.

## Próximas ações

1. Criar baseline de consolidação das branches `core-*`.
2. Integrar Engine Core + Blueprint + Capability + Mission/Agent + Dashboard/Builder.
3. Identificar lacunas para GitHub Engine, Deployment Engine e AI Analysis Engine.
4. Comparar seletivamente `package-8.0` e Enterprise X apenas contra essas lacunas.
5. Auditar a implantação existente em VPS/HostGator antes de qualquer troca de domínio/deploy.
6. Só depois da verificação de dependências, remover fisicamente duplicações comprovadamente obsoletas.

## Regra de descarte

Nenhum código, banco, migration, release ou arquivo histórico será apagado somente por aparentar duplicidade. O descarte físico exige comprovação de substituição, ausência de dependências e existência de histórico recuperável no Git.
