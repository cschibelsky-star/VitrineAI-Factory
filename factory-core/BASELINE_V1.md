# Factory Baseline V1

Branch: `consolidation/factory-baseline-v1`
Data: 2026-08-16

## Objetivo

Criar uma única linha executável e canônica para a Vitrine AI Factory, consolidando apenas os módulos válidos da linhagem `factory-core`.

## Fontes obrigatórias

1. `main` — documentação, login e base geral preservada.
2. `core-02-engine-core` — Engine Core.
3. `core-03-blueprint-engine` — Blueprint Engine.
4. `core-04-capability-engine` — Capability Engine.
5. `core-pack-05-mission-agent` — Mission + Agent.
6. `core-pack-05-dashboard-builder` — Dashboard + Builder.
7. `core-mockup-engines` — sequência arquitetural e especificações consolidadas.

## Ordem canônica

`Products → Engine Core → Blueprints → Capabilities → Missions → Agents → GitHub Engine → Dashboard/Builder → Deployment → AI Analysis → QA → Release`

## Regras

- Não restaurar `EnterpriseDashboard` histórico.
- Não usar `VitrineAI-FACTORY-ENTERPRISE-X` como runtime da Factory.
- Não importar `package-8.0` integralmente; usar apenas como fonte seletiva para lacunas.
- Não excluir branches históricas antes da homologação da baseline.
- Não aplicar migrations no ambiente produtivo durante consolidação.
- Toda integração deve manter migrations, models, services, resources Filament e seeders coerentes entre si.

## Consolidação por etapa

### Etapa A — núcleo
- Products
- Engine Core
- Blueprint Engine
- Capability Engine

### Etapa B — execução
- Mission Engine
- Agent Engine
- Builder
- Dashboard próprio da Factory

### Etapa C — integrações faltantes
- GitHub Engine
- Deployment Engine
- AI Analysis Engine

### Etapa D — seletiva de legado
Avaliar apenas lacunas contra:
- `package-8.0`: Factory Intelligence, Deploy Center, Marketplace, Pipeline Visual e provisionamento.
- `VitrineAI-FACTORY-ENTERPRISE-X`: contratos de produto, validadores, serviços de build/produção e documentação útil.

## Critério de aceite

A baseline só poderá substituir qualquer instalação anterior quando:

- todas as migrations forem revisadas para colisões;
- models e relações estiverem consistentes;
- resources Filament carregarem sem erro;
- Dashboard usar apenas entidades da Factory;
- Builder gerar uma missão/build rastreável;
- testes mínimos de criação, execução e status passarem;
- deploy em homologação VPS estiver isolado da produção.

## Descarte

Itens históricos podem ser retirados do runtime, menus e deploys imediatamente após sua substituição comprovada. Exclusão física do Git/repositório só ocorrerá depois da homologação da baseline e auditoria das dependências de VPS/banco/releases.
