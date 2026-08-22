# Factory Unificada V2 — Matriz de Consolidação

Data: 2026-08-22
Branch: `consolidation/factory-unified-v2`
Base: `consolidation/factory-baseline-v1`

## Objetivo

Consolidar a Vitrine IA Pro Factory como fábrica operacional de software do ecossistema, preservando a implementação mais evoluída e reaproveitando seletivamente capacidades úteis de branches e repositórios históricos.

## Regra de decisão

1. Manter a implementação mais atual e aderente à separação Core × Factory × Roteia × VIA.
2. Migrar apenas capacidades inexistentes ou superiores à baseline.
3. Não reintroduzir componentes que pertençam ao Core.
4. Não executar deploy por comandos arbitrários embutidos no domínio da aplicação.
5. Não restaurar dependências de cPanel/HostGator na arquitetura ativa da VPS.
6. Ações sensíveis devem ser planejadas, auditáveis e exigir confirmação no executor operacional.
7. IA da Factory deve consumir o Roteia; integrações diretas com providers ficam fora da arquitetura canônica.
8. Código descartado do runtime permanece recuperável no Git até a homologação final.

## Fontes avaliadas

### Baseline canônica — MANTER

`consolidation/factory-baseline-v1`

Mantém:

- Products;
- Engine Core;
- Blueprint Engine;
- Capability Engine;
- Mission Engine;
- Agent Engine;
- GitHub Engine;
- Dashboard/Builder;
- Deployment Engine;
- AI Analysis Engine;
- runtime Docker HML;
- migrations, seeders e testes consolidados.

### Branches `core-*` — ARQUIVAR COMO LINHAGEM

As implementações de `core-pack-05-dashboard-builder` e `core-pack-05-mission-agent` já foram absorvidas pela baseline em caminhos canônicos. Os diretórios `pack-0.5` não devem voltar ao runtime ativo.

### `package-8.0` — MIGRAR SELETIVAMENTE

Aproveitar conceitos:

- provisioning;
- health checks;
- diagnóstico operacional;
- inteligência da Factory;
- pipeline visual/deploy center onde preencher lacunas;
- marketplace/templates somente depois da camada principal de produção estar estável.

Não importar como está:

- deploy baseado em `git pull`, `composer install`, `migrate --force` executados diretamente pela aplicação;
- recomendações específicas de cPanel;
- dependências de HostGator como arquitetura principal;
- modelos duplicados de projeto/provisionamento quando `FactoryProduct`, Missions e Engines já cobrem o domínio.

### `VitrineAI-FACTORY-ENTERPRISE-X` — FONTE HISTÓRICA/SELETIVA

Migrar somente após comparação individual:

- contratos e manifestos de produto;
- validadores;
- documentação técnica;
- serviços de build/produção que não pertençam ao Core;
- integrações de infraestrutura ainda válidas.

Descartar do runtime da Factory:

- `EnterpriseDashboard` histórico;
- páginas e indicadores de clientes, planos, licenças, financeiro e Centro IA;
- cópias do Centro Operacional;
- funcionalidades específicas de produtos que devam viver em seus próprios repositórios.

## Arquitetura alvo

Fluxo operacional:

`Intake → Projeto → Arquitetura → Desenvolvimento → QA → Documentação → Build → HML → Validação → Release → Deploy`

Camadas:

- Command Center;
- Projects;
- Intake;
- Architecture;
- Development;
- QA;
- Documentation;
- Builds;
- Homologation;
- Releases;
- Deploy.

Engines/agentes:

- Architecture;
- Development;
- QA;
- Documentation;
- Deployment;
- AI Analysis via Roteia.

## Primeiros projetos de validação

1. Projeto Imobiliárias.
2. Gabinete Online.

Esses projetos devem entrar pela camada de Intake e percorrer o pipeline completo, servindo como validação funcional da Factory unificada.

## Próxima implementação nesta branch

- adicionar Health Engine canônico;
- adicionar Provisioning Engine controlado e declarativo;
- evoluir Deployment Engine para operar por plano, executor e evidências;
- criar contratos de integração com Roteia;
- estruturar entidades faltantes de Intake, QA, Builds, HML e Releases;
- consolidar menu e Command Center;
- validar runtime HML isolado antes de qualquer publicação em `factory.vitrineiapro.com.br`.
