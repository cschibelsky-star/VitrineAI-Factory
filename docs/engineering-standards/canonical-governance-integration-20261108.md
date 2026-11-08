# Integração da governança com a linha executável canônica

## Diagnóstico

O PR #20 foi criado sobre main e contém governança/adapter, mas não os módulos executáveis dos PRs #12 -> #17 -> #19. main também não possui docker-compose.factory.yml. Portanto, a falha do Factory Baseline CI no #20 não deve ser corrigida com um Compose inventado, suprimindo o workflow ou sobrescrevendo os módulos históricos.

Esta branch nasce do head auditado do PR #19, eb82fa4a9d523c3e21104498ee6a4e9c052c526f, e acrescenta os nove arquivos da governança do PR #20 em f4766181cfad4bf024c890af8d7d7b2f65896742. Não há merge, rebase, reset, alteração de branches antigas ou publicação. O PR de integração deve usar como base feat/via-stage-supervision-20261107 para que o diff mostre somente o acréscimo da governança e os ajustes necessários.

## Preservação

- Compose, Dockerfile, entrypoint, migrations, models, Filament, executores e APIs existentes vêm integralmente da linha #19; não são recriados.
- O PR #18 de policy Engine é independente e não foi incorporado por presunção. Sua revisão/RBAC continua sendo um gate próprio.
- As branches e os PRs #12/#17/#19/#20 permanecem disponíveis; esta entrega não os promove nem os fecha.
- Core/Centro IA/Vitrine Social Mídia e os nove agentes criativos do Marketing não são modificados.

## Compatibilidade e bloqueio necessário

O executor legado FactoryMissionRunner possui capacidade limitada de scaffold interno. Sem uma checagem explícita ele poderia trocar o status de uma missão OBSERVER para running/completed, ignorando a seção handoff_supervision recém-persistida.

O runner agora recusa, sob o lock da missão e antes de qualquer execução, toda missão que contenha a chave handoff_supervision, inclusive nula/malformada. O mesmo bloqueio protege requestCorrection. A recusa não chama block(), não sobrescreve status/DNA/auditoria e não reabre etapas. As missões legadas sem marcador preservam seus contratos e testes anteriores. Não há caminho para habilitar execução só por mudar um flag no JSON.

O adapter PDO continua operando na tabela usada por FactoryMission. Novos testes de aplicação usam as migrations e os models reais para comprovar a gravação, relações, preservação de DNA, idempotência, invalidação por novo SHA e negação do executor legado. Os testes são isolados em processos e SQLite memory; não aplicam migrations fora do CI.

## CI

Factory Application CI e Factory Baseline CI passam a observar a base encadeada #19. Mantêm build Docker e smoke Laravel reais. O Baseline cria a rede efêmera do Compose e usa FACTORY_BOOTSTRAP_MODE=ci com CI=true somente no runner descartável, como a suíte de aplicação já fazia. O entrypoint continua com bootstrap desabilitado por padrão e não é alterado.

O Agent Excellence Gate preserva 16 testes de metadados e 16 de PDO. A integração VIA continua exigindo todos os checks/reviews do SHA exato. O antigo CI vermelho #20 permanece como evidência histórica daquela branch incompleta; não é reclassificado como sucesso desta branch.

## Gates ainda abertos

CI de aplicação não comprova host/ACL Core, vínculo do responsável ao FactoryAgent, MySQL/InnoDB, funcionalidade de sistemas gerados, QA independente ou homologação VPS. A outbox continua passiva e o avanço OBSERVER continua bloqueado. Antes de promover: revisão independente, consolidação explícita da sequência de PRs, política Engine/RBAC, ambiente isolado, conexão de privilégio mínimo, backup/rollback e autorização específica de homologação.
