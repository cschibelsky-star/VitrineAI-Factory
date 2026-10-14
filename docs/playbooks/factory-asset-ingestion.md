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
