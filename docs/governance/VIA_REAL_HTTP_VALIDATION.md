# Validação real HTTP VIA → Factory em CI
Escopo: contêiner descartável, SQLite isolado, credenciais públicas de fixture exclusivamente para CI.
Adaptador VIA fixado em ac42ea99dbbd21e83b5fa85d10869f18edb7c746 (PR #34).

O teste gera artefato real, altera um arquivo, confirma bloqueio da próxima etapa, consulta por HTTP, solicita correção autenticada, rejeita aprovação injetada e repetição, confirma ausência de avanço automático e retoma pelo Runner interno.
Depois consulta novamente a API e exige artefatos verificados, tentativa 2 e independent_qa_verified=false.
O transporte só traduz DNS do serviço Docker para a porta loopback publicada: respostas vêm do Laravel real.

QA do scaffold valida também JSON/dependências declaradas e sintaxe PHP com TOKEN_PARSE sem executar o código gerado.
Isso não comprova funcionamento de uma aplicação gerada nem valida agentes externos.
Contratos funcionais por domínio permanecem bloqueados até existirem executores, verificadores e identidade independente de QA.

Não confundir CI descartável com HML implantada. VPS permanece sem alterações.
