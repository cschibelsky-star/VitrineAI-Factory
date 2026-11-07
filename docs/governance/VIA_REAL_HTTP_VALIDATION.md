# Validação real HTTP VIA → Factory em CI
Escopo: contêiner descartável, SQLite isolado, credenciais públicas de fixture exclusivamente para CI.
A prova roda no repositório privado VIA: o token da Factory pública não pode consultar a VIA privada.
Não copiar o adaptador privado para o repositório público nem ampliar permissões.

A workflow VIA fixa o commit da Factory e usa o adaptador da própria PR.
VIA_FACTORY_ADAPTER_PATH aponta ao módulo local da VIA.
O transporte só traduz DNS Docker para a porta loopback; respostas são do Laravel real.
O teste gera artefato, altera um arquivo, confirma bloqueio da próxima etapa, consulta por HTTP, solicita correção, rejeita aprovação injetada/repetição, confirma que não houve avanço automático e retoma pelo Runner.
A consulta final exige tentativa 2, artifact_gate_passed=true e independent_qa_verified=false.

QA do scaffold também valida JSON/dependências e sintaxe PHP sem executar o código.
Isso não comprova aplicação gerada funcional nem valida agentes externos.
Contratos por domínio permanecem bloqueados até existirem executores, validadores e identidade independente.

CI descartável não equivale a HML implantada. VPS sem alterações.
