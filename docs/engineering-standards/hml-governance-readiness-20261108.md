# Preparação de homologação — Marketing IA / VIA / Factory

Data: 08/11/2026. Escopo deste documento: resultado da inspeção somente leitura da VPS e roteiro revisável. Não é certificado de revisão independente nem autorização de deploy.

## Candidata e gates de revisão

Factory #21 é a candidata executável, derivada de #19 com o delta de governança #20. VIA #37 e Marketing #162 permanecem drafts. As consultas GitHub desta preparação não encontraram reviews em nenhum dos três PRs. Antes de homologar, exigir parecer independente vigente para o SHA exato e todos os checks obrigatórios desse SHA. Um novo commit invalida evidências antigas como autorização do novo head.

Os testes existentes já cobrem Docker/Laravel, 37 testes/220 assertions, 16 metadados + 16 PDO SQLite, VIA com 98+4 e Marketing 32/100. A preparação acrescenta CI MariaDB 11.4/InnoDB: os mesmos 16 cenários de persistência e dois testes com processos separados para duplicidade/conflito sob row lock. Isso amplia a prova de dialeto/concorrência; não comprova a configuração real do banco da VPS. Resultados por commit devem ser conferidos no PR #21.

## Observações da VPS (leitura, sem mutação)

| Componente | Observado | Consequência |
| --- | --- | --- |
| Factory | project factory, /srv/projects/factory/repository; branch atual fix/via-factory-gates-20261020; serviço saudável em 127.0.0.1:8080 | não trocar checkout nem recriar este serviço durante o piloto |
| Registry Factory | branch declarada consolidation/factory-baseline-v1 | divergência entre declaração e checkout; preservar ambos antes de qualquer reconciliação futura |
| Backup Factory | backup_not_configured | não usar este ambiente como destino de alteração; ausência não comprova perda, mas impede contar com restauração configurada |
| VIA | project via em reconcile/via-voice-halfduplex-20261105; via saudável em 127.0.0.1:8095 | não trocar branch nem reiniciar o projeto compartilhado |
| Projeto Docker da VIA | inclui via, core_app, core_db e core_web | uma operação ampla de Compose pode afetar Core; usar ambiente novo sem esses serviços |
| Marketing HML | feature/marketing-agents-core-v1; app/db/scheduler saudáveis | não usar este banco/scheduler no piloto de governança |
| Factory PR18 RBAC HML | Compose modificado localmente | não reutilizar nem sobrescrever esse checkout |
| Domínios Factory no manifesto | nenhuma HML/produção registrada | não alegar URL de homologação publicada ou vínculo no Cockpit |

A presença de configuração foi consultada sem ler valores de secrets. Nada foi criado, copiado ou alterado na VPS nesta preparação.

## Viabilidade das rotas

| Capacidade | Estado | Evidência/limite |
| --- | --- | --- |
| Gravar/revisar código | confirmado | GitHub em branches isoladas |
| Testar em CI | confirmado | workflows da candidata, validar sempre head atual |
| Consultar VPS | confirmado | V5 ativo, contexto/Git/Docker consultados |
| Operar serviço factory pela rota controlada V5 | bloqueado | project_compose_service_status retornou controlled_service_not_allowed para factory/docker-compose.factory.yml/factory |
| Cadastrar projeto isolado | previsto | ferramenta disponível, mas não executada; autorização/política precisam ser aprovadas para o destino exato |
| Build/exec do novo ambiente | desconhecido | não inferir permissão a partir do catálogo; validar política da operação/serviço exatos antes de habilitar |
| Expor URL pública | fora desta primeira rodada | teste inicial interno; domínio/TLS/Cockpit são gate posterior se necessário |
| Executar correção por agente | desabilitado | OBSERVER, outbox passiva, sem consumo de IA ou publicação |

Não repetir uma rota negada ou usar Compose amplo/SSH para evitar a restrição. Nesta etapa de preparação não é necessário tentar fallback mutável: a homologação ainda depende de revisão, autorização e definição da política do novo destino.

## Roteiro proposto para aprovação específica

1. Destino novo: project_id factory-marketing-governance-hml-20261108; workspace /srv/projects/factory-marketing-governance-hml-20261108; Docker project factory_marketing_governance_hml_20261108. Esses identificadores são propostas, ainda não registradas.
2. Pin imutável da candidata #21 e da VIA #37, após verificar CI/review vigentes. Não usar main, branch operacional ou PR #20 isolado. Guardar hashes/manifesto dos dois repositórios usados pelo teste.
3. Aprovar previamente operações e serviços exatos na política do conector. Sem permissão comprovada para o projeto novo, não construir nem iniciar. Não ampliar a permissão de factory/via existentes por conveniência.
4. Preparar runner de teste sem portas públicas, sem container_name/volumes/redes dos projetos existentes, sem mounts do host e sem credenciais reais. Banco MariaDB temporário e isolado; saída limitada a status, contagem e hashes. Revisar Compose renderizado com dados fictícios para evitar exposição de secrets.
5. Build da imagem e testes não fazem inferência de IA, postagem, deploy de produto ou chamadas a providers. Não criar endpoint de escrita nem habilitar worker. Bootstrap de tabelas somente no banco descartável próprio do teste, nunca no banco da Factory ou Marketing ativos.
6. Executar: reprovação -> persistência canônica no banco descartável -> responsável registrado -> retry/reinício sem duplicação -> duas avaliações concorrentes -> submissão de novo SHA -> SHA/review antigos recusados -> prova nova mantém pending_supervision. Confirmar que o runner legado não consegue executar/reabrir a missão supervisionada.
7. Validar isolamento por tenant/projeto/produto, limites, erro de banco e rollback. Confirmar healthy/status dos serviços ativos antes/depois sem reiniciá-los. Registrar saída assinada/identificada pelo operador, SHAs, versões e evidências.
8. Aceite inicial: nenhum próximo agente executado, nenhuma duplicação/sobrescrita, banco/serviços existentes intactos e resultado revisado independentemente. A palavra homologado somente após aceite desse resultado real.
9. Rollback: parar somente os serviços do projeto novo, conservar seus logs/artefatos de teste para revisão e manter a execução desabilitada. Limpeza de volumes/workspace é ação posterior específica; não executar down global, reset, prune ou exclusões dos projetos existentes.

## Autorizações ainda necessárias

Revisão independente não é substituída por EXECUTAR do owner. Para avançar da preparação para VPS: aprovar o destino e o plano concretos, as operações exatas do conector, o Compose isolado e o banco descartável. Custos de API: zero previsto, porque não há chamadas a modelos/providers. O Compose preparado limita o conjunto a 768 MiB de RAM e 1 CPU, com tmpfs de banco de 512 MiB e /tmp de 64 MiB. A consulta de capacidade retornou CPU 0%, memória 50,1% e disco 40,66%, sem valores absolutos de capacidade; isso não comprova headroom suficiente. Verificar os valores absolutos e espaço da imagem antes de aprovar o uso na VPS. Não foram reservados recursos nem alterado limite de gasto.

Merge da cadeia, mudança de tráfego, domínio, credenciais reais, migration de ambiente existente, implantação em produção e ativação de agentes permanecem fora deste roteiro.


## Runner concreto para a primeira rodada limitada

Arquivos revisáveis: docker-compose.governance-validation.yml e tools/governance-validation/Dockerfile. Serviços exatos: governance_db e governance_tests; não usa os serviços factory/via/marketing existentes. O teste usa dados fictícios e banco factory_governance_ci somente no namespace de rede do banco descartável. Não há porta publicada, mount do host, volume persistente, nome fixo de container ou rede externa. O usuário do runner é 65534, filesystem somente leitura, capabilities removidas e sem escalada de privilégios.

A primeira rodada cobre persistência, gates e concorrência do adapter, não login/ACL/Cockpit nem a operação completa VIA→Core. Seu CI valida o Compose renderizado com o contrato de isolamento antes do build/up e executa 16 casos SQLite, 16 metadados, 16 InnoDB e 2 de concorrência. A prova VIA interprodutos permanece separada e pinada por commit no #37.

Este Compose não foi cadastrado ou executado na VPS. A política a revisar para o projeto novo deve mencionar expressamente esse arquivo e os dois serviços, sem privilégios para factory/via/Core/Marketing. A compatibilidade das operações de execução e coleta do exit code precisa ser verificada pelo conector autorizado; não se presume que a presença do arquivo ou um build CI torne a operação disponível no V5. Se não houver operação permitida que preserve esses limites, continuar bloqueado e revisar a rota com o proprietário, sem contornar o controle.
