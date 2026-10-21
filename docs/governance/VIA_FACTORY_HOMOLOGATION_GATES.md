# Gate mestre de homologacao VIA + Factory

Status inicial: NAO HOMOLOGADO
Data de instituicao: 2026-10-20
Escopo exclusivo de desenvolvimento: VIA Personal (Canonica 01), VIA Web, Core, orquestrador, Factory e agentes.

## Diretriz executiva

Nao iniciar novos produtos, funcionalidades comerciais nem novas frentes de desenvolvimento antes da conclusao integral dos gates abaixo. Intervencoes emergenciais estritamente necessarias para preservar seguranca, dados ou disponibilidade devem manter a autorizacao aplicavel e nao representam liberacao para ampliar o escopo.

A VIA e o ponto de contato do proprietario e a futura supervisora/diretora de desenvolvimento IA. O Core aplica identidade, permissoes, custos e politica. A Factory e a executora canonicamente versionada; agentes executam funcoes especializadas, um QA independente valida, e o Cockpit registra homologacao. A VIA nao substitui o QA nem pode aprovar a propria evidencia.

## Fonte de verdade e nao-regressao

- GitHub e fonte de verdade do codigo; preserve alteracoes locais antes de reconciliar.
- Reutilizar componentes existentes antes de criar novos.
- Preservar a VIA Canonica 01: HUD, oito estados, voz pt-BR, audio, modo maos livres, Android, web/PWA, integracao Google, memoria, pesquisas, ferramentas e automacoes conforme escopo previamente aprovado.
- A Factory Enterprise X e fonte seletiva para lacunas; nao e o runtime canonico da Factory (ver factory-core/BASELINE_V1.md).
- HML isolada, testes e autorizacao obrigatorios antes de publicacao; nunca promover por declaracao de sucesso de agente.
- Acesso a segredos sempre por mecanismo seguro, sem leitura em claro.
- Limites de despesa e privilegio minimo por agente/operacao.
- Nao adicionar novos projetos ate a aprovacao integral dos gates.

## Modelo de evidencia para cada item

Cada item exige: ID, responsavel, ambiente, versao/commit, data, acao executada, evidencias verificaveis (log/teste/artefato/captura/URL), resultado (NAO_TESTADO | APROVADO | REPROVADO | BLOQUEADO), validador independente e proxima acao. Nenhum item e aprovado so por conter codigo, CI generico verde ou Docker saudavel.

## Gates obrigatorios

### G0 - Preservacao e baseline canonica

- [ ] Mapear VIA, JARVIS Canonica 01, Core, Factory e Enterprise X, indicando origem, branch, HEAD e divergencia.
- [ ] Preservar alteracoes locais e verificar backups/rollback; nao forcar reset/clean/merge.
- [ ] Inventariar agentes, skills, motores e blueprints reutilizaveis; evitar duplicacao.
- [ ] Congelar escopo e vincular missoes a esta politica.

Saida: arquitetura, matriz de componentes e trilha de recuperacao conferidas.

### G1 - VIA funcional

- [ ] Voz pt-BR, reconhecimento de ativacao, interrupcao e filtragem de ruido testados em aparelho real.
- [ ] HUD Canonica 01, estados, cores, animacao e respostas extensas sem cortes.
- [ ] PWA, web, tablet e APK Android assinado e instalavel; testes por dispositivo.
- [ ] Pesquisa atual, calculos, contexto/memoria, agenda e e-mail Google, tarefas e automacoes.
- [ ] Erros e timeouts apresentados corretamente; sem travar em 'pensando'.

Saida: roteiro de testes e evidencias por interface, dispositivo e versao.

### G2 - VIA -> Core -> Factory

- [ ] Identidade e permissoes owner/cliente isoladas e comprovadas.
- [ ] Integracao segura e funcional com Centro IA, provedores e conectores (presenca/status de credenciais, sem valores).
- [ ] VIA cria missao persistente na Factory, recebe ID e consulta estado real ate encerramento.
- [ ] Reinicio e indisponibilidade nao apagam, duplicam nem falsificam missoes.
- [ ] Custo e uso IA por tarefa registrados e limitados.

Saida: API e ciclo de missao rastreaveis de ponta a ponta.

### G3 - Agentes e executor de missoes

- [ ] Cada agente possui funcao, acesso minimo, entrada, saida e criterios de aceite definidos.
- [ ] MissionRunner NAO declara sucesso sem executor efetivo e comprovacao da entrega.
- [ ] Uma etapa reprovada ou bloqueada impede a proxima; ha retorno ao responsavel.
- [ ] GitHub, QA, Builder e demais ferramentas produzem evidencias vinculadas a cada missao.
- [ ] Nenhum status 'completed' e atribuido por simples loop, mock ou log.

Saida: teste positivo REAL e teste negativo que bloqueia a etapa seguinte.

### G4 - QA independente, HML e rollback

- [ ] Testes unitarios, integracao, seguranca, UI/visual, mobile e regressao relevantes.
- [ ] Mesmo commit entre artefato testado e HML homologada.
- [ ] QA distinto do executor; aprovacao registrada com evidencia, versao e data.
- [ ] Rollback comprovado; producao somente mediante autorizacao correspondente.

Saida: homologacao verificavel, nao um status decorativo.

### G5 - VIA supervisora e diretora de desenvolvimento

- [ ] VIA identifica divergencias de versao, CI, falhas, bloqueios e regressoes reais.
- [ ] VIA prioriza custo, risco, impacto e urgencia; recomenda correcoes e atualizacoes.
- [ ] VIA aciona Factory, acompanha execucao, exige QA e comunica resultados.
- [ ] VIA nao usa a propria recomendacao como validacao independente.
- [ ] Acoes da VIA sao auditaveis por outro agente ou operador.

Saida: missao supervisionada com relato fiel do executado e nao executado.

### G6 - Demonstracao final de ponta a ponta

- [ ] Proprietario solicita pela VIA uma tarefa de TESTE INTERNO da integracao (sem tocar em outros projetos).
- [ ] A missao persiste, o agente correto executa, o QA verifica e o Cockpit exibe evidencias.
- [ ] Falha deliberada bloqueia prosseguimento e abre retorno; sucesso so depois da correcao.
- [ ] A VIA apresenta ao proprietario evidencias de cada transicao e qualquer autorizacao pendente.
- [ ] Execucao nao modifica producao nem projetos externos.

Saida: demonstracao observavel e repetivel, com rastreamento ponta a ponta.

### G7 - Estabilidade e liberacao executiva

- [ ] Testes de falha/recuperacao e continuidade; rotinas de monitoramento e alertas.
- [ ] Documentacao operacional e contrato de integracao publicados no repositorio canonico.
- [ ] Inventario de pendencias zerado ou de itens nao bloqueantes formalmente aprovados.
- [ ] Relatorio final com evidencias de G0 a G7 aprovado pela direcao.
- [ ] Somente apos essa aprovacao podem ser retomados projetos externos.

Saida: aprovacao explicita da direcao. Sem aprovacao, o projeto permanece NAO HOMOLOGADO.

## Estado inicial confirmado em 2026-10-20 (nao equivale a nova rodada de testes)

- MissionRunner atual da linha de consolidacao registra conclusao e ok=true sem chamar executor real: G3 REPROVADO.
- VIA Agent Hub no repositorio via opera em modo OBSERVER: G2/G5 BLOQUEADOS ate existir integracao real.
- Factory HML no manifesto consultado sem servicos Docker ativos: G4 NAO TESTADO em HML.
- Android Canônica 01 possui workflows recentes com falhas de baseline e assinatura: G1 REPROVADO para disponibilizacao do APK.
- Os demais itens permanecem NAO TESTADOS ate coleta de evidencias.

## Sequencia operacional autorizada para a fase inicial

1. Corrigir falsa conclusao no MissionRunner, em branch/PR isolado com testes.
2. Validar CI e reconciliar a baseline sem modificar arvore suja.
3. Definir contrato minimo VIA -> Core -> Factory, identidade e eventos de missao.
4. Homologar primeira missao exclusivamente interna, sob supervisao VIA.
5. Completar os demais gates sem ampliar escopo.

Nao executar merge, migrations, restart, cutover nem deploy de producao com base apenas neste documento.
