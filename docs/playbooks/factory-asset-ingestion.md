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
