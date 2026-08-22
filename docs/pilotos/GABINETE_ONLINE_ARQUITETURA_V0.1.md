# Gabinete Online — Arquitetura Piloto v0.1

Status: aprovado para desenvolvimento piloto
Etapa Factory: architecture
Objetivo: estabelecer o baseline funcional e técnico mínimo para validar o pipeline ponta a ponta da Vitrine IA Pro Factory em uma solução de atendimento e organização de gabinete.

## 1. Escopo funcional mínimo

O piloto deve permitir:

- página pública responsiva do gabinete;
- formulário de contato/demanda do cidadão;
- classificação básica da demanda por assunto e prioridade;
- registro de protocolo interno;
- painel administrativo autenticado;
- fila de demandas com status;
- histórico de interações e encaminhamentos;
- cadastro básico de contatos e responsáveis internos;
- agenda simples de compromissos/atendimentos;
- registro de anexos ou evidências quando aplicável;
- trilha mínima de auditoria para alterações relevantes.

Fora do escopo inicial: integração oficial com sistemas públicos, assinatura eletrônica, disparos em massa, CRM externo, automações avançadas e integrações eleitorais/políticas. Esses itens permanecem como evolução separada.

## 2. Arquitetura técnica

Arquitetura sugerida para o MVP:

- aplicação web responsiva;
- camada pública para captação de demandas;
- área administrativa autenticada;
- backend com API interna para demandas, contatos, agenda e histórico;
- banco relacional;
- configuração por ambiente para HML e produção;
- deploy conteinerizado e controlado pelo Centro Operacional;
- logs e healthcheck obrigatórios em HML;
- separação entre dados operacionais e configurações da aplicação.

A escolha final de framework, repositório e domínio permanece aberta e não bloqueia a fase de arquitetura.

## 3. Modelo de dados mínimo

Entidades principais:

- Gabinete
- UsuarioAdmin
- CidadaoContato
- Demanda
- DemandaCategoria
- DemandaInteracao
- ResponsavelInterno
- Encaminhamento
- Compromisso
- Anexo
- RegistroAuditoria

Relacionamentos essenciais:

- Gabinete 1:N UsuarioAdmin
- CidadaoContato 1:N Demanda
- Demanda N:1 DemandaCategoria
- Demanda 1:N DemandaInteracao
- Demanda 1:N Encaminhamento
- ResponsavelInterno 1:N Encaminhamento
- Demanda 1:N Anexo

## 4. Mapa de integrações

Integrações obrigatórias do piloto:

- canal de contato via formulário web;
- possibilidade de CTA para WhatsApp conforme configuração;
- Centro Operacional para build/deploy controlado.

Integrações futuras, não bloqueantes:

- agenda externa;
- e-mail transacional;
- automações via Vitrine IA Flow;
- classificação assistida por IA via Roteia;
- integrações oficiais com sistemas públicos, desde que juridicamente e tecnicamente aprovadas.

## 5. Checklist de QA

Antes do build HML:

- formulário público persistindo demanda;
- geração/registro de protocolo funcionando;
- painel admin autenticado;
- filtros por status, categoria e prioridade funcionando;
- alteração de status registrada;
- histórico de interações preservado;
- encaminhamento para responsável funcionando;
- agenda básica funcionando;
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
- política mínima de dados e auditoria;
- changelog por release.

## 7. Plano de build

Fluxo esperado:

1. definir repositório canônico;
2. criar bootstrap da aplicação;
3. implementar banco e migrations;
4. implementar captação pública de demandas;
5. implementar painel administrativo;
6. implementar fila, histórico e encaminhamentos;
7. implementar agenda básica;
8. executar QA automatizado e manual;
9. gerar build HML;
10. registrar evidências na Factory;
11. aprovar HML antes de Release.

## 8. Critérios de aceite HML

A HML será considerada apta quando:

- container estiver healthy;
- migrations concluírem sem erro;
- formulário público criar demanda de teste;
- protocolo interno for registrado;
- admin autenticar e listar demandas;
- alteração de status e histórico funcionarem;
- encaminhamento de teste for persistido;
- agenda básica registrar compromisso;
- QA não possuir bloqueador crítico;
- documentação mínima estiver disponível;
- evidências de build e health estiverem registradas na Factory.

## 9. Decisões em aberto

Não definidas nesta versão:

- repositório Git definitivo;
- domínio/subdomínio;
- framework final;
- canal transacional definitivo;
- integrações com sistemas públicos externos.

Esses itens não impedem o início do desenvolvimento piloto.
