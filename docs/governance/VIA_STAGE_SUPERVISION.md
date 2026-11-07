# Supervisão VIA: gates e correção
Data: 2026-11-07. Implementação inicial, não homologada.

## Responsabilidades
VIA supervisiona e solicita correções; executor produz; QA separado verifica.
Factory impede avançar após reprovação. Produção exige autorização do proprietário.

## Implementação deste lote
- Runner bloqueia próxima etapa quando execução ou QA falham.
- rework_required devolve ao mesmo agent_id; correção exige motivo.
- Cada tentativa preserva arquivos anteriores em diretório próprio e histórico no log.
- Três tentativas no máximo; revisão humana necessária depois, sem reset automático.
- Etapas concluídas são verificadas novamente antes de continuar.
- Transação serializa runner/correção nos bancos com suporte a lockForUpdate.
- SQLite não oferece bloqueio de linha: execução concorrente requer validação adicional antes de habilitar workers.
- Correção possui API dedicada, desativada por padrão e restrita a HML interna; veja VIA_SUPERVISION_API.md.

## Limites obrigatórios
Apenas factory.internal_scaffold tem executor. QA atual comprova manifesto e hashes de quatro arquivos; NÃO comprova aplicação funcional, testes de navegador, deploy ou homologação.
Demais ações continuam bloqueadas. Adapter VIA e API de correção estão propostos nas PRs VIA #34 e Factory #19; acionamento real ainda não foi homologado.
Campo supervisor=VIA identifica a política, não comprova acionamento pela VIA.
Não liberar agentes comerciais com este QA.

## Próximos gates antes de uso geral
Contratos de aceite por arquitetura/backend/frontend/integração; executor especializado; identidade distinta do revisor; relatórios de testes vinculados ao commit; autenticação VIA/Core; retorno e painel Cockpit; demonstração E2E positiva e negativa.
Não confundir CI aprovado, imagem saudável e funcionalidade homologada.
