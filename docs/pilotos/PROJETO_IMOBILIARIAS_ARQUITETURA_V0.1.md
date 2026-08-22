# Projeto Imobiliárias — Arquitetura Piloto v0.1

Status: aprovado para desenvolvimento piloto
Etapa Factory: architecture
Objetivo: estabelecer o baseline funcional e técnico mínimo para validar o pipeline ponta a ponta da Vitrine IA Pro Factory.

## 1. Escopo funcional mínimo

O piloto deve permitir:

- presença digital responsiva para imobiliária;
- catálogo de imóveis com listagem e página de detalhe;
- filtros básicos por finalidade, tipo, faixa de preço, cidade/bairro e status;
- captação de interesse e contato por WhatsApp;
- formulário simples de lead vinculado ao imóvel;
- painel administrativo para cadastro, edição, publicação e retirada de imóveis;
- gestão básica de imagens do imóvel;
- dados institucionais da imobiliária e canais de contato;
- trilha mínima de auditoria para alterações administrativas relevantes.

Fora do escopo inicial: integrações com portais imobiliários, assinatura eletrônica, CRM externo, gateway de pagamento e automações avançadas. Esses itens permanecem como evolução.

## 2. Arquitetura técnica

Arquitetura sugerida para o MVP:

- aplicação web responsiva;
- camada pública para catálogo e conversão;
- área administrativa autenticada;
- backend com API interna para catálogo, leads e administração;
- banco relacional;
- armazenamento de mídia desacoplado da regra de negócio;
- configuração por ambiente para HML e produção;
- deploy conteinerizado e controlado pelo Centro Operacional;
- logs e healthcheck obrigatórios em HML.

A escolha final de framework, repositório e domínio permanece aberta e não bloqueia a fase de arquitetura.

## 3. Modelo de dados mínimo

Entidades principais:

- Imobiliaria
- UsuarioAdmin
- Imovel
- ImovelImagem
- TipoImovel
- Localidade
- Lead
- LeadInteracao
- ConfiguracaoContato
- RegistroAuditoria

Relacionamentos essenciais:

- Imobiliaria 1:N Imovel
- Imovel 1:N ImovelImagem
- Imovel N:1 TipoImovel
- Imovel N:1 Localidade
- Imovel 1:N Lead
- Lead 1:N LeadInteracao

## 4. Mapa de integrações

Integrações obrigatórias do piloto:

- WhatsApp via link parametrizado de contato;
- serviço de mídia/imagens conforme infraestrutura escolhida;
- Centro Operacional para build/deploy controlado.

Integrações futuras, não bloqueantes:

- CRM;
- portais imobiliários;
- mapas/geocodificação;
- automações via Vitrine IA Flow;
- agentes de IA via Roteia.

## 5. Checklist de QA

Antes do build HML:

- cadastro, edição e remoção lógica de imóvel funcionando;
- upload/associação de imagens validado;
- filtros retornando resultados coerentes;
- página de detalhe sem campos críticos vazios;
- CTA de WhatsApp com contexto do imóvel;
- lead persistido corretamente;
- autenticação do admin protegida;
- validação de inputs e CSRF;
- layout responsivo sem rolagem horizontal indevida;
- páginas públicas sem erro 5xx;
- healthcheck disponível.

## 6. Baseline de documentação

O projeto deverá manter:

- README com objetivo e bootstrap;
- arquitetura e decisões técnicas;
- variáveis de ambiente documentadas sem segredos;
- instruções de build e HML;
- inventário de integrações;
- checklist de QA;
- changelog por release.

## 7. Plano de build

Fluxo esperado:

1. definir repositório canônico;
2. criar bootstrap da aplicação;
3. implementar banco e migrations;
4. implementar catálogo público;
5. implementar admin;
6. implementar leads/WhatsApp;
7. executar QA automatizado e manual;
8. gerar build HML;
9. registrar evidências na Factory;
10. aprovar HML antes de Release.

## 8. Critérios de aceite HML

A HML será considerada apta quando:

- container estiver healthy;
- migrations concluírem sem erro;
- catálogo público abrir e listar imóveis de teste;
- detalhe do imóvel abrir corretamente;
- admin autenticar e permitir CRUD básico;
- lead de teste for persistido;
- CTA de WhatsApp gerar mensagem contextual;
- QA não possuir bloqueador crítico;
- documentação mínima estiver disponível;
- evidências de build e health estiverem registradas na Factory.

## 9. Decisões em aberto

Não definidas nesta versão:

- repositório Git definitivo;
- domínio/subdomínio;
- framework final;
- provedor de mídia;
- integração externa de CRM/portais.

Esses itens não impedem o início do desenvolvimento piloto.
