# MOCKUP — Vitrine AI Factory

## Resumo Compactado dos Engines

Este documento consolida os módulos enviados para a construção da Vitrine AI Factory e define a referência operacional para integração dos códigos no Factory Core.

## Engines consolidados

- Deployment Engine
- AI Analysis Engine
- Engine Core
- Blueprint Engine
- Capability Engine
- Mission Engine
- Agent Engine
- Dashboard Engine
- GitHub Engine

## Estrutura macro

Cada engine deverá seguir a estrutura padrão:

- Migrations
- Models
- Repositories
- Services
- Actions
- Policies
- Filament Resources
- Widgets
- Factories
- Seeders
- Feature Tests
- Config files
- Bindings no AppServiceProvider

## Entidades por Engine

### Engine Core

- engine_types
- engines

### Blueprint Engine

- blueprint_versions
- blueprint_entities
- blueprint_fields
- blueprint_relations
- blueprint_capabilities

### Capability Engine

- capabilities
- capability_versions
- capability_dependencies
- capability_blueprints
- capability_products

### Mission Engine

- mission_agents
- mission_steps
- mission_logs

### Agent Engine

- factory_agents
- agent_skills
- agent_capabilities
- agent_missions

### GitHub Engine

- github_repositories
- github_branches
- github_pull_requests
- github_issues
- github_commits

## Fluxos principais

### Blueprint Import/Snapshot

- BlueprintSchemaParser
- BlueprintImporter
- BlueprintVersion
- BlueprintService

### Capability Lifecycle

- CRUD
- Versionamento
- Publicação
- Dependências
- Vínculos com Blueprints e Produtos

### Mission Orchestration

- Missões
- Steps
- Logs
- Agentes responsáveis
- Execução simulada

### Agent Assignment

- Agentes
- Skills
- Capabilities
- Missões
- Estado operacional

### GitHub Data Hub

- Repositórios
- Branches
- Pull Requests
- Issues
- Commits
- Sync stub preparado para API real

### Factory Dashboard

- Products
- Blueprints
- Capabilities
- Missions
- Agents
- Builders
- Saúde geral da Factory

## Filament Resources previstos

- DeploymentEngineResource
- AiAnalysisEngineResource
- EngineTypeResource
- EngineResource
- BlueprintResource
- CapabilityResource
- MissionResource
- AgentResource
- GitHubRepositoryResource
- GitHubPullRequestResource
- GitHubIssueResource
- FactoryDashboard

## Seed inicial

- Tenant base
- Project base
- Product base
- Blueprint base
- Engine Types
- Engine inicial
- Blueprint schema inicial
- Capabilities iniciais
- Mission Agents
- Factory Agents
- GitHub Repository seed

## Testes previstos

- EngineFeatureTest
- BlueprintEngineTest
- CapabilityEngineTest
- MissionEngineTest
- AgentEngineTest
- GitHubEngineTest

## Status

Este mockup passa a ser tratado como referência para a integração dos códigos no Factory Core.

A prioridade é transformar esta estrutura em implementação executável Laravel + Filament.
