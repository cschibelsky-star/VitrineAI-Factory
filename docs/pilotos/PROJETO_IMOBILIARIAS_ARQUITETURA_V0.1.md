# Projeto Imobiliárias — Arquitetura Piloto v0.1

Status: aprovado para desenvolvimento piloto
Etapa Factory: architecture
Objetivo: estabelecer o baseline funcional e técnico mínimo para validar o pipeline ponta a ponta da Vitrine IA Pro Factory.
Referência funcional/UX: Praedium — site para imobiliária. A referência é usada para benchmarking de funcionalidades e experiência, sem copiar código, identidade visual ou conteúdo proprietário.

## 1. Escopo funcional mínimo

O piloto deve permitir:

- presença digital responsiva para imobiliária;
- catálogo de imóveis com listagem e página de detalhe;
- filtros por finalidade, tipo, faixa de preço, cidade/bairro, status e características configuráveis;
- captação de interesse e contato por WhatsApp;
- formulário de lead vinculado ao imóvel;
- painel administrativo para cadastro, edição, publicação e retirada de imóveis;
- gestão de imagens do imóvel;
- dados institucionais da imobiliária e canais de contato;
- páginas institucionais e páginas personalizadas para serviços/campanhas;
- menu configurável por tipos, cidades, bairros e páginas;
- identidade visual configurável com logo, cores e tipografia;
- campos SEO por página e imóvel: título, slug, descrição e headings principais;
- sitemap.xml automático para imóveis, cidades, bairros e páginas públicas;
- domínio próprio e HTTPS/SSL como requisito de produção;
- trilha mínima de auditoria para alterações administrativas relevantes.

Fora do MVP inicial, mas previsto como evolução: CRM imobiliário completo, distribuição/rodízio de leads, integração com portais imobiliários, rastreamento avançado de visitantes identificados, assinatura eletrônica, gateway de pagamento, mapas/geocodificação avançada e automações comerciais.

## 2. Referência de produto e diferenciação

A referência Praedium demonstra um modelo SaaS em que o site não é apenas uma vitrine estática: o cliente pode personalizar tema, identidade visual, navegação, filtros e páginas; o produto também enfatiza SEO, domínio próprio, SSL, geração de leads e integração posterior com CRM.

Para a Vitrine IA Pro, o produto deve seguir o mesmo princípio de autonomia operacional, porém com arquitetura própria e integração nativa ao ecossistema Vitrine:

- Factory para evolução, builds, QA, HML e releases;
- Centro Operacional para execução controlada de infraestrutura/deploy;
- Vitrine IA Flow para automações futuras;
- Roteia para agentes e recursos de IA futuros;
- possibilidade de white-label/licenciamento para múltiplas imobiliárias.

## 3. Arquitetura técnica

Arquitetura sugerida para o MVP:

- aplicação web responsiva;
- camada pública para catálogo e conversão;
- área administrativa autenticada;
- backend com API interna para catálogo, leads, conteúdo, SEO e administração;
- banco relacional;
- armazenamento de mídia desacoplado da regra de negócio;
- sistema de configuração de tema/branding sem permitir código arbitrário pelo usuário;
- configuração por ambiente para HML e produção;
- deploy conteinerizado e controlado pelo Centro Operacional;
- logs e healthcheck obrigatórios em HML;
- preparação para arquitetura multi-tenant, ainda que o primeiro piloto possa operar com um único tenant.

A escolha final de framework, repositório e domínio permanece aberta e não bloqueia a fase de arquitetura.

## 4. Modelo de dados mínimo

Entidades principais:

- Imobiliaria
- UsuarioAdmin
- Imovel
- ImovelImagem
- TipoImovel
- CaracteristicaImovel
- Localidade
- Lead
- LeadInteracao
- PaginaPersonalizada
- MenuItem
- TemaConfiguracao
- SeoMetadata
- ConfiguracaoContato
- RegistroAuditoria

Relacionamentos essenciais:

- Imobiliaria 1:N Imovel
- Imobiliaria 1:N PaginaPersonalizada
- Imobiliaria 1:N MenuItem
- Imobiliaria 1:1 TemaConfiguracao
- Imovel 1:N ImovelImagem
- Imovel N:1 TipoImovel
- Imovel N:N CaracteristicaImovel
- Imovel N:1 Localidade
- Imovel 1:N Lead
- Lead 1:N LeadInteracao
- páginas e imóveis podem possuir SeoMetadata.

## 5. Mapa de integrações

Integrações obrigatórias do piloto:

- WhatsApp via link parametrizado de contato;
- serviço de mídia/imagens conforme infraestrutura escolhida;
- Centro Operacional para build/deploy controlado.

Integrações futuras, não bloqueantes:

- CRM imobiliário;
- distribuição/rodízio de leads;
- portais imobiliários;
- mapas/geocodificação;
- analytics e rastreamento de comportamento com consentimento adequado;
- Google Search Console;
- automações via Vitrine IA Flow;
- agentes de IA via Roteia.

## 6. Checklist de QA

Antes do build HML:

- cadastro, edição e remoção lógica de imóvel funcionando;
- upload/associação de imagens validado;
- filtros configuráveis retornando resultados coerentes;
- página de detalhe sem campos críticos vazios;
- CTA de WhatsApp com contexto do imóvel;
- lead persistido corretamente;
- páginas personalizadas publicadas corretamente;
- menu configurável refletido no frontend;
- logo, cores e tipografia aplicados sem quebrar contraste/acessibilidade;
- slugs únicos e URLs amigáveis;
- sitemap.xml gerado sem URLs inválidas;
- metadados SEO presentes nas páginas críticas;
- autenticação do admin protegida;
- validação de inputs e CSRF;
- layout responsivo sem rolagem horizontal indevida;
- páginas públicas sem erro 5xx;
- healthcheck disponível.

## 7. Baseline de documentação

O projeto deverá manter:

- README com objetivo e bootstrap;
- arquitetura e decisões técnicas;
- variáveis de ambiente documentadas sem segredos;
- instruções de build e HML;
- inventário de integrações;
- checklist de QA;
- documentação de personalização de tema/menu/filtros;
- documentação de SEO e sitemap;
- changelog por release.

## 8. Plano de desenvolvimento/build

Fluxo esperado:

1. definir repositório canônico;
2. criar bootstrap da aplicação;
3. implementar tenancy/configuração da imobiliária;
4. implementar banco e migrations;
5. implementar catálogo público e detalhes;
6. implementar filtros configuráveis;
7. implementar admin de imóveis e mídia;
8. implementar páginas, menu e branding;
9. implementar SEO e sitemap;
10. implementar leads/WhatsApp;
11. executar QA automatizado e manual;
12. gerar build HML;
13. registrar evidências na Factory;
14. aprovar HML antes de Release.

## 9. Critérios de aceite HML

A HML será considerada apta quando:

- container estiver healthy;
- migrations concluírem sem erro;
- catálogo público abrir e listar imóveis de teste;
- detalhe do imóvel abrir corretamente;
- filtros funcionarem em desktop e mobile;
- admin autenticar e permitir CRUD básico;
- branding e menu puderem ser alterados sem código;
- página personalizada puder ser criada/publicada;
- SEO básico e sitemap estiverem válidos;
- lead de teste for persistido;
- CTA de WhatsApp gerar mensagem contextual;
- QA não possuir bloqueador crítico;
- documentação mínima estiver disponível;
- evidências de build e health estiverem registradas na Factory.

## 10. Roadmap após MVP

Prioridade 2:

- CRM de leads e funil;
- distribuição/rodízio de atendimento;
- histórico de interesse do lead;
- analytics de páginas e imóveis;
- integrações com portais;
- automações comerciais.

Prioridade 3:

- agente IA para descrição e enriquecimento de imóveis;
- recomendações de imóveis por perfil;
- qualificação automática de lead via Roteia;
- conteúdo SEO assistido por IA;
- integração avançada com campanhas e Vitrine IA Flow.

## 11. Decisões em aberto

Não definidas nesta versão:

- repositório Git definitivo;
- domínio/subdomínio;
- framework final;
- provedor de mídia;
- CRM externo ou CRM próprio na fase 2.

Esses itens não impedem o início do desenvolvimento piloto.
