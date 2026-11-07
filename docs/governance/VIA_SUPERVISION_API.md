# API de supervisão VIA v1
Status: código proposto, sem implantação ou provisionamento de credenciais.

GET /api/via/factory/missions/{id}: leitura com FACTORY_VIA_READ_TOKEN, incluindo tentativa, motivo, responsável pelo retorno e contratos de aceite por domínio.

POST /api/via/factory/missions/{mission}/steps/{step}/correction:
- Habilitação explícita FACTORY_VIA_CORRECTION_ENABLED e token dedicado FACTORY_VIA_CORRECTION_TOKEN distinto do leitor.
- Bloqueada em produção; escopo restrito a missões de scaffold interno.
- Corpo: reason (3–1000 caracteres), expected_attempt (0–2).
- Retorno 202: etapa pending, sem execução ou aprovação.
- 401: credencial inválida; 503: indisponível/desativada; 404: vínculo missão/etapa ausente; 409: versão/estado/escopo incompatível; 422: entrada inválida.
- Repetir comando aceito retorna 409, sem duplicar log ou reiniciar execução.
- Limite de 10 requisições/minuto por chave de rate limit do Laravel.

A VIA usa um token de proprietário próprio para entrada e mantém tokens Factory somente no servidor.
Nenhuma credencial foi criada, copiada ou ativada neste lote.
A API apenas devolve para correção. Retomada do executor continua pela operação existente e requer QA novamente.

Contratos architecture/backend/frontend/integration/homologation são publicados como requisitos com functional_validator_available=false.
Não interpretar essa configuração ou hashes como prova funcional. Implementação dos validadores e identidade de QA independente permanecem pendentes.
