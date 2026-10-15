# Factory Asset Ingestion — contrato operacional

## Problema
Os conectores atuais de escrita GitHub tratam texto UTF-8, mas arquivos de design, vídeo e PDF são binários. Enviar base64 no chat ou reconstruir imagens não é o fluxo oficial.

## Arquitetura obrigatória
1. Adaptador MCP autenticado recebe um **file reference** de upload, não base64 em argumento textual nem URL arbitrária.
2. O adaptador materializa os bytes em diretório de intake isolado, com limite de tamanho, autenticação, quotas e trilha de auditoria.
3. A Factory valida MIME por assinatura, extensão, SHA-256, projeto/branch e destino autorizados. Proíbe traversal, symlinks e sobrescrita implícita.
4. Importa bytes ao checkout Git de branch de trabalho, sem commit/push automático. Operação Git separada e controlada publica no GitHub (LFS para arquivos grandes, quando configurado).
5. CI verifica SHA-256, integridade de arquivos e presença dos assets; HML valida HTTP 200, screenshots e comparação visual. A produção só segue após aprovação.
6. Um registro de manifesto relaciona asset original, SHA-256, projeto, branch, caminho, versão, aprovação e evidência HML.

## Gate de aceitação
- Upload de PNG/WebP original até GitHub e HML, com SHA-256 idêntico, sem manipular base64 em mensagens.
- Tentativas de path traversal, symlink, overwrite e checksum divergente são bloqueadas.
- Sem modificar produção nem árvores Git sujas.
- Reutilizar o fluxo em ao menos dois projetos.

## Situação
Núcleo Python implementado e testado localmente (10 testes). Adaptador MCP de recebimento binário, instalação na VPS e teste E2E ainda pendentes. Não alegar conclusão antes desse teste.


## Implementação do adaptador (15/10/2026)

`tools/asset_intake.py` registra `factory_asset_receive` no servidor MCP existente
via `register_mcp(server, intake, get_context)`. Os únicos argumentos da ferramenta
são `file_reference`, `project`, `branch`, `destination` e `sha256`. A ferramenta
não aceita identidade, checkout, token, URL, caminho local de origem ou base64.
`asset_job.run_materialized` integra a transferência ao mesmo núcleo de ingestão.
O CLI local anterior permanece interno; não deve ser exposto como ferramenta pública.

`tools/asset_reference_store.py` fornece o resolvedor concreto de um catálogo de
uploads controlado pelo servidor: `StoredReferenceResolver` verifica principal,
ACL, validade e arquivo regular; lê bytes originais por descritores sem seguir
symlinks. O catálogo `lookup(reference)` deve ser fornecido pelo serviço real de
uploads. Uma referência da Library/Drive não se torna automaticamente referência
aceita: é necessário um binding autorizado ao provedor. Não inventar esse binding.

### Integração obrigatória do host (gate ainda aberto)

1. Middleware autentica a sessão e fornece contexto verificado por requisição.
   `get_context` e `principal(context)` não podem confiar em argumentos do modelo,
   headers sem validação ou variáveis globais compartilhadas entre sessões.
2. `authorize(context, project, branch)` consulta o registro de permissões e retorna
   `Grant` com checkout isolado, branch de trabalho e prefixos exatos autorizados.
   Nenhum checkout de produção pode constar desse registro. Branches main/master/
   production são recusadas adicionalmente pelo adaptador.
3. O serviço de uploads fornece catálogo privado, ACL e TTL. O diretório de uploads
   e o de intake pertencem ao usuário do serviço e têm permissão 0700. O intake
   deve ficar fora dos checkouts e nunca sob uma raiz HTTP pública.
4. `reserve(grant)` deve reservar quota por principal/projeto e exclusividade do
   checkout em **todos os workers**, inclusive durante auditoria. Callback vazio
   usado em teste não é configuração válida de runtime. Limite por arquivo é
   25 MiB ou o valor menor do grant; limites cumulativos são responsabilidade do host.
5. `audit(event)` deve persistir eventos de autorização e resultado com fsync ou
   confirmação durável. Não registrar tokens, bytes, caminhos de origem ou URLs.
   Falha antes da transferência bloqueia a operação. Falha no registro final pode
   deixar o asset importado: reconciliar pelo status Git e SHA, sem repetir com
   overwrite, apagar ou alegar sucesso auditado.
6. Expor somente `factory_asset_receive` pelo registro MCP autenticado já existente.
   Não abrir uma API de upload paralela nem executar commit/push/deploy nessa chamada.

### Proteções e evidências

- Git real, raiz exata e branch verificada; árvore deve estar limpa, incluindo
  arquivos ignorados. Não executa reset/clean/stash/commit/push.
- Transferência limitada durante leitura, checksum do conteúdo materializado e
  nova validação antes da escrita; valida assinatura de formato, sem transcodificar.
- `.git`, traversal e symlinks recusados; destino percorrido por descritores;
  publicação por hard link exclusivo impede substituir arquivo concorrente.
- Sobrescrita foi removida, inclusive do antigo flag interno. Substituições exigem
  operação separada com revisão; este fluxo importa somente caminhos novos.
- Recibo retorna principal, projeto, branch, base_commit, destino, SHA e bytes.
  GitHub e HML permanecem gates separados, não são inferidos de `status=staged`.

### Validação executada e limites

Suíte local: 31 testes aprovados, incluindo registro MCP, ACL/TTL, quota,
checksum divergente, assinatura inválida, destino fora do escopo, árvore suja/
ignorada, symlink, overwrite e criação concorrente de destino. Teste integrado
usa **dois repositórios temporários de teste**, resolvedor real de catálogo local,
commit Git local e HTTP em loopback; SHA do original, blob Git e resposta HTTP
coincide. Esses repositórios não são projetos homologados do ecossistema.

Não houve upload de logos reais, push de assets a projetos, screenshot ou comparação
visual na HML. Os testes usam um PNG mínimo de fixture, não uma reconstrução de logo.
Nenhuma aprovação visual pode ser inferida desse teste.

Auditoria operacional: o inventário legado de VPS Operations aponta para container
`vitrine_app` ausente; V5 encontrou `/srv/projects/factory/repository`, branch
`consolidation/factory-baseline-v1`, limpa e quatro commits atrás do remoto. Essa
árvore foi preservada. As ferramentas disponíveis não expõem binding de catálogo
autenticado de uploads nem instalação deste adaptador. Não foi realizado deploy.

### Antes de liberar o draft

- Conectar autenticação, catálogo do provedor, quota/lock e auditoria durável no host
  canônico; testar sessões reais e chamadas concorrentes entre workers.
- Importar dois assets originais aprovados em dois projetos autorizados e isolados.
- Em operação Git separada, revisar diff/status, conferir SHA do **blob Git publicado**
  (filtros/autocrlf/LFS podem mudar a representação), conferir checkout após download
  do GitHub; nunca enviar um ponteiro LFS como se fosse a imagem original.
- Pelo gate HML de cada projeto, servir o arquivo, validar HTTP 200 e SHA dos bytes,
  registrar URL/commit/manifesto, screenshots e comparação visual ao layout aprovado.
- Registrar no Cockpit e obter homologação; somente então avaliar merge/deploy.

## Serviços concretos de runtime — segunda etapa de 15/10/2026

`tools/asset_runtime.py::PrivateRuntime` implementa os serviços que antes eram
somente callbacks: catálogo SQLite privado com ACL/TTL e hashes, importação interna
de stream original, quota diária persistente por principal, lock `flock` por
checkout entre processos, auditoria SQLite com `synchronous=FULL` e registro na
instância MCP existente. Não cria listener, token, autenticação paralela ou serviço
Docker. O host ainda deve fornecer identidade verificada e políticas canônicas.

Integração mínima no host existente, sem argumentos públicos de credencial/caminho:

```python
from asset_runtime import PrivateRuntime

runtime = PrivateRuntime(private_state_directory, authorized_grants,
                         principal=verified_principal_from_context)
runtime.register(existing_mcp_server, verified_request_context)
# No handler autenticado do provedor de arquivos, após validar a ACL no provedor:
receipt = runtime.accept_upload(verified_context, project=authorized_project,
    branch=authorized_work_branch, stream=original_binary_stream,
    sha256=original_sha256)
# A chamada MCP subsequente usa apenas receipt['file_reference'].
```

O diretório de estado precisa ser privado (0700), fora dos checkouts e de qualquer
raiz pública; os arquivos usam 0600. Políticas `Grant` vêm do servidor e não do
modelo. `accept_upload` é um método interno, não uma ferramenta MCP; o provedor
real deve enviar o stream original com identidade e checksum verificados.

A quota cobra conservadoramente a reserva máxima por tentativa, tanto no upload
como na ingestão; falhas também contam. O dia é UTC, o padrão é 100 MiB por
principal e o limite por arquivo segue o grant, até 25 MiB. Limites podem ser
reduzidos pelo host. Dois uploads+ingestões de 25 MiB consomem os 100 MiB.
O lock exige que todos os workers usem o mesmo diretório de estado em filesystem
local com `flock` funcional; não foi validado para NFS ou múltiplos hosts.
O serviço não remove originais expirados automaticamente: TTL bloqueia acesso,
mas retenção e descarte continuam sujeitos à política de preservação aprovada.

Validação ampliada: **39 testes aprovados**, incluindo upload interno → catálogo
persistido → ferramenta registrada → ingestão em dois repositórios isolados,
SHA do blob Git local, recibos persistidos após reinicialização, quota após
reinicialização e bloqueio de um segundo processo concorrente. Estes testes
não equivalem a sessões reais no host MCP de produção nem à HML dos projetos.

Auditoria adicional: a branch `consolidation/factory-baseline-v1` contém runtime
PHP 8.3/Laravel/Filament; a branch deste PR foi aberta a partir de `main` e não
contém `factory-runtime`/compose. O inventário do repositório deste PR não contém
host MCP autenticado. Não adicionar Python ao container PHP nem reconciliar essas
branches por conveniência. O próximo gate exige identificar o repositório/serviço
canônico do host MCP e seu handler de arquivos; depois revisar a integração e
instalação isolada, sem merge/deploy implícito. A auditoria genérica de capacidades
do Super retorna suporte a Docker/arquivos/Git, mas isso não comprova uma capacidade
de upload binário nem um binding autenticado de arquivos.

## Host operacional localizado e binding FastMCP — terceira etapa

A descoberta pelo registry V5 localizou o host Python no repositório
`cschibelsky-star/Vitrine-IA-Pro-VPS`, arquivos `connector-v5/main.py` e
`connector-v5/main_ext.py`. A arquitetura informada é `standalone-fastmcp`;
requirements: `fastmcp>=2.10,<3`. O checkout canônico consultado em 15/10/2026
está na branch `fix/hml-provider-assessor-20261014` (GitHub: commit
`806d2d1301928df36af966bce02ef20adea09454`), diferente da branch declarada no
manifesto. Seu estado foi preservado. Isso substitui a pendência genérica de
"localizar o host"; ainda falta conectar o handler autenticado do provedor de arquivos.

O código FastMCP auditado cria `FastMCP(name)` sem `auth=...`. Isso não comprova
que o endpoint externo está aberto — pode haver autenticação no proxy — mas
não fornece ao adaptador um principal verificado pelo SDK. Não inferir identidade
de headers, IP, nome de conta ou presença de token.

`tools/asset_fastmcp.py::bind_existing_host` agora conecta os serviços ao host
FastMCP e **recusa o registro se `server.auth` estiver ausente**. A identidade de
cada chamada vem de `fastmcp.server.dependencies.get_access_token`, nunca dos
argumentos MCP. Exige escopo `factory:assets:write`, client_id verificado e
expiração finita futura. A política de grants deve usar esse mesmo client_id,
considerando que ele pode identificar um cliente OAuth, não necessariamente uma
pessoa. Provider deve validar assinatura, issuer e audience antes de criar contexto.

```python
from asset_fastmcp import bind_existing_host, request_principal

# O servidor existente já precisa ter provider autenticado homologado.
runtime, tool = bind_existing_host(existing_mcp_server,
    state_root=private_state_directory, policies=authorized_grants)
# Handler interno do provedor, na própria requisição autenticada:
receipt = runtime.accept_upload(request_principal(), project=authorized_project,
    branch=authorized_work_branch, stream=original_binary_stream,
    sha256=original_sha256)
```

O binding não configura ou substitui o provider, nem altera proxy, secrets ou
listeners. É intencional bloquear a ativação no host auditado até homologar o
contexto de identidade e o handler do provedor real. Nenhum patch foi instalado
no V5 compartilhado ou em sua branch de trabalho.

Validação local: **41 testes aprovados**, incluindo recusa de host sem auth e
negação de token ausente, sem escopo, expirado ou sem identidade. A instalação
do SDK no ambiente local não estava disponível no índice acessível. Por isso,
foi criado job CI separado `authenticated-mcp` com FastMCP 2.10.6 e ASGI HTTP
real, verificador JWT com chaves efêmeras de teste, tokens inválidos/expirados,
escopo insuficiente, principal sem grant e ingestão autorizada. Confirmar o
resultado desse job no commit exato; não confundir testes unitários com SDK real.

Referência primária da API auditada:
https://github.com/PrefectHQ/fastmcp/blob/v2.10.6/src/fastmcp/server/dependencies.py

## Entrada binária HTTP no host existente — quarta etapa

O binding agora registra `POST /factory/assets/upload?project=...&branch=...`
no próprio servidor FastMCP existente. Não cria listener, proxy, container ou
credencial. A rota customizada **não presume herdar o middleware MCP**: chama
explicitamente `server.auth.verify_token` com o bearer recebido e só então
obtém principal/escopo/validade. Headers de identidade não são usados.

O corpo deve conter bytes originais com `Content-Type: application/octet-stream`
e `X-Asset-SHA256`. O grant determina checkout, escopo e limite (até 25 MiB).
O limite é aplicado ao Content-Length e durante o streaming, inclusive quando
não há Content-Length. Tempo de transferência limitado a 30 s; lock não bloqueante
por checkout cobre o spool privado. Arquivos parciais são removidos em erros;
respostas não expõem paths, tokens ou erros internos. A resposta 201 contém
somente referência opaca, SHA e tamanho e usa `Cache-Control: no-store`.

Após o upload, a chamada MCP `factory_asset_receive` recebe essa referência e
executa as verificações de branch/destino/árvore Git antes da ingestão.
O upload registra `received` na mesma transação durável do catálogo. Expiração
do token é verificada novamente ao concluir a transferência. A integração do
provedor real deverá transmitir os bytes originais a essa rota autenticada;
nenhuma URL externa será aceita como referência pela ferramenta MCP.

### Teste com arquivos originais recebidos

Foram recuperados dois PNGs originais já fornecidos (logo e estúdio TV Sumaré),
com identidade e versão de origem preservadas. O teste interno utilizou os
bytes reais, sem reconstrução ou transcodificação: catálogo → ingestão → Git
local → HTTP 200 em dois repositórios temporários. SHA coincidiu em todas as
etapas. Evidência em `asset-intake-original-evidence-20261015.json`.

Esse ensaio não publica os PNGs no GitHub, não aprova identidade visual nem
substitui a HML dos projetos. Os arquivos binários não foram duplicados neste
repositório. O CI HTTP passou a usar o endpoint de upload, verificando ausência
ou invalidade de autenticação, escopo/projeto negados, checksum divergente,
Content-Length excessivo, stream excessivo sem Content-Length, cleanup e a
sequência upload autenticado → referência → ingestão MCP autorizada.

A ativação remota segue pendente do gate do host: provider autenticado, rota
restrita e transferência autorizada do provedor. Código e testes podem avançar
sem instalar uma rota não autenticada no V5 compartilhado.
