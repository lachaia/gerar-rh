# Análise do Sistema de RH — GERAR

> Documento gerado a partir da análise do código-fonte em `D:\xampp\htdocs\rh`, para registrar o entendimento da arquitetura, módulos e integrações do sistema. Não é um repositório git com histórico — trata-se de uma aplicação PHP procedural em produção contínua.

## Visão geral

O sistema é um **monólito PHP procedural** (sem framework), dividido em quatro "apps" independentes que compartilham o mesmo banco MySQL (`RH`):

| Pasta | Papel |
|---|---|
| `app/` | Núcleo do RH: cadastro de colaboradores, estrutura organizacional, ponto, férias, afastamentos, avaliações, documentos, CIPA/brigada, ouvidoria, usuários/auditoria |
| `recrutamento/` | CRM interno de recrutamento e seleção (solicitação → aprovação → funil de vagas → candidatos) |
| `talentos/` | Portal público onde candidatos externos criam login próprio e mantêm currículo |
| `vagas/` | Vitrine pública de vagas abertas (sem login) |
| `suporte/` | Página estática de abertura de chamado (HTML puro, sem lógica de servidor) |

Padrão de código dominante: uma tela (`rh_modulo.php`, HTML + Bootstrap 5 + DataTables + Summernote + jQuery) inclui `includes/conexao_gerar.php`, valida a sessão, e dispara chamadas AJAX para arquivos-satélite `rh_modulo_aj1.php`, `_aj2.php`, etc., cada um responsável por uma única operação (insert/update/select), retornando JSON ou HTML parcial. Os sufixos numéricos indicam crescimento orgânico por funcionalidade, não uma arquitetura em camadas planejada.

`vagas/` → `talentos/` → `recrutamento/` formam um funil único sobre as mesmas tabelas (`rs_vagas`, `rs_vagas_candidaturas`, `rh_pessoas`).

## Banco de dados e multiempresa

- Conexão via **PDO** (`app/includes/conexao_gerar.php`), banco MySQL único (`RH`) hospedado externamente (mesmo em ambiente de desenvolvimento local via XAMPP).
- Não é multi-tenant por banco separado; é **multiempresa dentro do mesmo schema**: tabela `rh_empresas` (o login exige escolher a empresa) e subdivisões organizacionais por `rh_subsedes` / `rh_polos`.
- `rh_organograma` guarda a hierarquia (nível, staff, supervisor) que caracteriza quem é "gestor" (líder) e quem é colaborador comum.

## Autenticação e autorização

- Login (`app/login.php` → `login_aut.php`) aceita **login, CPF ou e-mail** + senha, com seleção de empresa e perfil. Senhas com hash bcrypt (`password_verify`).
- Grupos de usuário (`idUsuarioGrupo`) observados: 1 = RH, 3 = Psicólogos do RH, 4 = Termos de Responsabilidade de Equipamentos, 6 = Recrutamento & Seleção, 7 = TI, 8 = Candidatos externos (bloqueado de entrar no sistema interno), 9 = Super Usuário. Colaborador comum e gestor são diferenciados por atributos do organograma, não por um grupo fixo.
- Sessão guarda `idUsuario`, `idColab`, `idOrgao`, `idPessoa`, `idGrupo`, `idSubSede`, `idEmpresa`, além de flags `dcCIPA`/`dcBrigada` (participação em comissões de segurança do trabalho).
- Controle de acesso é feito **ad-hoc em cada página** (ex.: `if ($_SESSION['idGrupo'] > 2 ...) header('Location: proibido.php')`), não há um middleware central de autorização.
- Auditoria dupla: `rh_logins` registra cada sessão (IP, SO, navegador, login/logout); `rh_logs` (via função `f_log()`) registra praticamente toda operação de negócio relevante.
- Sub-portais têm login próprio e sessão isolada: `app/ponto/` (ponto eletrônico, PWA), `recrutamento/login.php`, e `talentos/auth.php` (candidato — tabela própria `rh_user_candidatos`, deliberadamente separada de `rh_usuarios` para isolar credenciais de candidato e de funcionário).
- Recuperação de senha e solicitação de acesso geram token em `rh_token`, enviado por e-mail via PHPMailer.

## Módulos funcionais do núcleo (`app/`)

- **Cadastro de Pessoas/Colaboradores**: `rh_pessoas*` (pessoa física genérica), `rh_colab*` (contratação completa: matrícula, cargo, função, subsede, polo, centro de custo, salário, dados bancários/PIX, eSocial), `rh_ficha_colab.php` / `rh_ficha_pessoa.php` (ficha-resumo), `rh_cv.php` / `rh_pessoa_cv.php` (currículo estruturado: experiências, formação, idiomas, conquistas, habilidades).
- **Estrutura organizacional**: `rh_cargos` (com CBO), `rh_funcoes`, `rh_organograma` (hierarquia/departamentos), `rh_polos` / `rh_subsedes` (unidades geográficas).
- **Jornada/Ponto**: `app/ponto/` é um sub-app PWA próprio (versões mobile e web, API dedicada), com telas de bater ponto, espelho de ponto e visão de equipe. `cron_ferias.php` gera automaticamente períodos aquisitivos/concessivos de férias por colaborador; `rh_ferias*` faz a gestão manual (agendamento, aprovação, fracionamento em até 3 partes).
- **Afastamentos/Saúde**: `rh_afastamentos*` (licenças, atestados) e `rh_saude*` (saúde ocupacional).
- **Avaliações de desempenho**: `rh_avaliacoes*`.
- **Equipamentos**: `rh_equipamentos*` — termo de responsabilidade de equipamentos entregues ao colaborador.
- **Contrato de experiência**: `rh_ctr_exp*`.
- **Rescisão**: `rh_rescisao*` — módulo de desligamento.
- **Reajuste salarial**: `rh_reajuste.php`.
- **Documentos (GED) com OCR**: `rh_docs*` (upload, edição, exclusão, visualização, envio por e-mail). Arquivos físicos ficam em `app/docs/pessoa_<idPessoa>/` — confirmado padrão de ~165 pastas individuais por pessoa, além de pastas auxiliares `brigada/` e `cipa/`.
- **Ouvidoria/Denúncias**: `rh_ouvidoria*` e `rh_ouvir.php` / `rh_ficha_ouvidoria.php` (canal de denúncia e acompanhamento de casos).
- **CIPA e Brigada de incêndio** (segurança do trabalho): sub-apps próprios (`app/cipa/`, `app/brigada/`), cada um com dezenas de endpoints AJAX dedicados; participação é um atributo do usuário (`dcCIPA`/`dcBrigada`).
- **Termos de uso / Privacidade**: `rh_politica_priv.php`, termo de responsabilidade/aceite no portal do colaborador.
- **Usuários, Logins e Logs**: `rh_usuarios*`, `rh_logins*` (sessões), `rh_logs*` (auditoria de operações).
- **Notificações**: `rh_cfg_notificacao.php` + `f_notificacoes.php` (função `notificar_usuarios_evento()`), usada por exemplo em aprovações de férias.
- **Autocadastro de colaborador**: `rh_autocadastro*` — fluxo granular em 7 etapas AJAX (distinto do autocadastro de currículo de candidato externo em `talentos/`).

### Portais dedicados por perfil (dentro de `app/`)

- `app/colaborador/` — autoatendimento (ponto, férias, afastamentos, currículo, desempenho, equipamentos, termos).
- `app/gestor/` — visão do gestor/líder (equipe, aprovações de férias/afastamentos/atestados/ponto, ficha do colaborador).
- `app/cipa/`, `app/brigada/` — portais de segurança do trabalho.
- `app/api/api_supervisor.php` — API interna (JSON, CORS aberto) que devolve dados do colaborador e de seu gestor a partir do organograma.
- `app/helpers/` — utilitários (`crypto.php`, `gera_pacote.php`).

## OCR e Inteligência Artificial

Duas soluções coexistem para digitalização/extração de dados:

1. **Google Document AI / Vision** (`ocr_google.php`, roteado por `ocr_gerar.php` conforme extensão do arquivo) — extração de texto de PDFs e imagens. Formatos Office (`.docx`, `.xlsx`, `.pptx`, Visio) têm rotinas próprias (`ocr_docx.php`, `ocr_xlxs.php`, `ocr_pptx.php`, `ocr_visio.php`).
2. **Anthropic Claude** (`includes/f_ia_curriculo.php` + `includes/config_ia.php`) — extração estruturada de currículo em PDF via API Messages com **tool use forçado** (JSON Schema garantido: experiências, formação, idiomas, conquistas, habilidades), lendo o PDF nativamente (sem OCR prévio). O código já inclui mitigação explícita contra **prompt injection** embutida no PDF do candidato (instrução no system prompt + sanitização adicional em PHP antes de gravar no banco). Há também configuração para Gemini, mas comentada como não utilizada.

## Módulo `recrutamento/` (R&S)

Fluxo com login e sessão próprios:

1. **Solicitação** (`solicitacao.php`) — gestor solicita abertura de vaga.
2. **Aprovação** (`aprova.php`) — superintendente aprova/reprova por link de e-mail autenticado por token (sem exigir login), atualizando `rs_vagas.status_id` (1=Solicitada, 2=Aprovada, 3=Reprovada).
3. **Dashboard** (`index.php`) — Kanban de status de aprovação.
4. **Funil pós-aprovação** (`fluxo.php`) — Kanban por `rs_vagas.fluxo_id` (etapas como Alinhamento, Divulgação, Cancelada), com contagem de candidatos e dias parado na etapa.
5. **Vaga**: `vaga_new.php` / `vaga_edit.php` / `vaga_view.php`, configuração de status/fluxo em `vagas_status.php` / `vagas_fluxo.php`.
6. **Candidatos**: `vaga_candidatos.php`, `candidato_parecer.php` (parecer do recrutador), `candidatos_origem.php`.
7. Apoio: `competencias_comportamentais.php`, `competencias_tecnicas.php`, `motivos.php`, `superintendentes.php`.

Usa a mesma conexão e as mesmas tabelas de estrutura organizacional do núcleo — é um módulo satélite, não um sistema separado.

## Módulo `talentos/` (Portal do Candidato)

- Login próprio contra `rh_user_candidatos` (isolado de `rh_usuarios` por design).
- `new.php` + cadeia de ~20 etapas AJAX (`new_aj0` a `new_aj18`) para cadastro passo a passo do currículo (dados pessoais, formação, experiências, idiomas, conquistas, habilidades). Aceita `vaga_id` na URL para candidatura automática após concluir o cadastro.
- `candidatos.php` — painel do candidato autenticado para editar seu currículo, usando apenas dados de sessão (mitigação de IDOR comentada no código).
- Integra com `f_ia_curriculo.php` (extração automática por IA) e com `recrutamento/` via `rs_vagas_candidaturas`; a mesma tabela `rh_pessoas` serve tanto candidato quanto colaborador.

## Módulo `vagas/` (Vitrine Pública)

- `index.php` — lista vagas elegíveis: `status_id = 2` (aprovada), `fechada_em IS NULL`, `publicada_em IS NOT NULL`, `expira_em` nula ou futura.
- `vaga_perfil.php` — ficha pública de uma vaga com botão de candidatura; redireciona ao login/cadastro em `talentos/` se necessário, senão grava em `rs_vagas_candidaturas` (com checagem de duplicidade).

## Integrações externas

- **Google Calendar** (`includes/f_google_calendar.php`) — conta de serviço com domain-wide delegation, cria eventos com Google Meet como se fosse o organizador; autenticação manual via JWT/OAuth2 (não usa a lib oficial do Composer nesse arquivo).
- **E-mail** — PHPMailer (vendorizado manualmente, não via Composer) via SMTP do Amazon SES, usado em reset de senha, convites, aprovação de vaga por e-mail, etc.
- **API interna** (`app/api/api_supervisor.php`) — resolve "quem é o gestor de quem" a partir do organograma, consumida por outros módulos (ponto, colaborador).

## Dependências (Composer)

`app/composer.json` inclui: `google/cloud-document-ai`, `google/cloud-vision`, `google/cloud-core`, `google/auth`, `google/apiclient` (Google Cloud/OCR), `dompdf/dompdf` + `drewlabs/php-dompdf` (geração de PDF), `phpoffice/phpword`, `phpoffice/phpspreadsheet`, `phpoffice/phppresentation` (leitura/escrita Office), `aspose/slides-sdk-php` (PowerPoint).

## Integridade referencial

Verificado diretamente no banco (`information_schema.KEY_COLUMN_USAGE`): das 124 tabelas (todas InnoDB, portanto todas capazes de ter FK), **apenas 11 chaves estrangeiras existem de fato**, restritas a 7 tabelas: `rh_brigada_acoes_membros`, `rh_brigada_reuniao_membros`, `rh_cipa_acoes_membros`, `rh_cipa_reuniao_membros`, `rh_cv_habilidades`, `rh_logins` e `rh_rescisoes`. Ver `SCHEMA.sql` para o DDL completo.

As tabelas centrais do domínio (`rh_pessoas`, `rh_colaboradores`, `rh_organograma`, `rh_cargos`, `rh_subsedes`, `rh_polos`, `rh_ferias`, `rh_afastamentos`, `rh_docs`, `rs_vagas`, `rh_usuarios`, etc.) **não têm chave estrangeira declarada**, apesar de terem colunas de relacionamento (`idPessoa`, `idColab`, `idOrgao`, `idEmpresa`...) usadas extensivamente em JOINs no código PHP. Isso confirma que a integridade referencial é garantida **apenas na camada de aplicação**, não pelo MySQL — coerente com o padrão de crescimento orgânico do sistema (endpoints AJAX numerados, checagens ad-hoc por página). Risco prático: é possível inserir/orfanizar registros (ex.: excluir uma pessoa com documentos/currículo/férias associados) sem que o banco impeça, dependendo inteiramente da lógica de cada endpoint.

**Script de correção**: `ADD_FOREIGN_KEYS.sql` (na raiz do projeto) contém 201 chaves estrangeiras propostas, cada uma validada contra os dados reais de produção (zero linhas órfãs) antes de entrar no script — não foi aplicado no banco, só gerado para revisão. `DIAGNOSTICO_ORFAOS.md` documenta as ~36 relações que ficaram de fora por inconsistência de dados, categorizadas por causa (sentinela "0" no lugar de `NULL`, registros-pai realmente ausentes, tabelas de log com numeração histórica quebrada) e com sugestões de correção para revisão manual.

Achado relevante descoberto durante essa validação: as colunas `idSubSede`/`subsede_id` (em ~12 tabelas) e `rs_vagas.polo_id` **não referenciam o `id` novo** de `rh_subsedes`/`rh_polos` — referenciam um **código legado do sistema anterior à migração** (ex.: `rh_subsedes.id=1` tem `subsede_id=101`). Pior: `polo_id` tem dois significados diferentes dependendo da tabela (`rh_colaboradores.polo_id` usa o `id` novo, `rs_vagas.polo_id` usa o código legado) — vale uma auditoria do código PHP que faz join por `polo_id` para confirmar que nenhuma tela está cruzando os dois sistemas de numeração sem querer.

## Observações de segurança

Diversas credenciais sensíveis estão **hardcoded em texto plano** no código-fonte, e não em variáveis de ambiente ou secrets manager:

- Senha do MySQL de produção (`app/includes/conexao_gerar.php`)
- Chave de API da Anthropic (`app/includes/config_ia.php`)
- Credenciais SMTP do Amazon SES (`app/includes/inc_email.php`)
- Conta de serviço Google (`app/chaves/gerarocr-ba77063bf0b6.json`, e possivelmente `app/credentials.json`)

Recomenda-se migrar esses segredos para variáveis de ambiente/secrets manager e garantir que esses arquivos não sejam versionados publicamente. Além disso, o controle de acesso por perfil é verificado individualmente em cada página (sem middleware central), o que aumenta o risco de alguma tela nova esquecer a checagem — vale considerar uma camada de autorização centralizada no futuro.

## Resumo

Monólito PHP procedural, multi-módulo e multi-perfil, construído incrementalmente. Um único banco MySQL serve o núcleo de RH, recrutamento e portal de talentos, com multiempresa e multi-subsede/polo como dimensão organizacional. Autenticação por sessão PHP com auditoria dupla (login e ações). Já incorpora automação com IA generativa (Claude) para pré-preenchimento de currículos, complementando OCR tradicional via Google Document AI, com atenção explícita a mitigação de prompt injection nesse fluxo.
