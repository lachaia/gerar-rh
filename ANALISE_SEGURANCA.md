# Análise de Segurança — Sistema de RH (GERAR)

> Auditoria de código estático (leitura apenas, nada foi alterado ou executado contra produção) cobrindo `app/`, `app/colaborador/`, `app/gestor/`, `app/ponto/`, `app/cipa/`, `app/brigada/`, `app/termos/`, `app/api/`, `recrutamento/`, `talentos/`, `vagas/`. Escopo: SQL Injection, controle de acesso/IDOR, upload de arquivo/path traversal, XSS/CSRF, segredos expostos.

## Como ler este documento

Os achados estão organizados por severidade. Dentro de cada nível, por categoria. Cada item tem arquivo(s):linha quando disponível, um cenário de exploração concreto, e uma recomendação objetiva. O padrão de causa-raiz se repete o tempo todo: **o app cresceu por dezenas de arquivos `_aj*.php` numerados, copiados uns dos outros ao longo do tempo, e o padrão de segurança correto (`isset($_SESSION['idLogin'])` + `exit()` + bind de parâmetro + checagem de posse do registro) foi aplicado de forma inconsistente** — alguns arquivos de um mesmo módulo estão corretos, os "irmãos" ao lado não.

---

## CRÍTICO — ação imediata

### 8. SQL Injection não autenticada em pontos críticos
- **`app/includes/login_aj2.php`** (linha ~20-28) — a etapa que identifica o usuário **antes** da senha ser validada monta `WHERE login like '%$login%' OR P.cpf like '%$login_cpf%' OR P.email_corporativo like '%$login%'` sem bind. Isso é injeção de SQL **no próprio fluxo de login**, sem exigir credencial alguma.
- **`app/includes/rh_reajuste_aj1.php`** — sem `session_start()`, `WHERE C.idOrgao = $idOrgao` direto do `$_GET`.
- **`app/ponto/api/cron_banco_horas.php`** — sessão lida mas nunca verificada; `colaborador_id` do GET entra sem bind em **três `DELETE`** (`rh_ponto_banco_saldo`, `rh_ponto_banco_horas`, `rh_notificacoes`). Payload tipo `?colaborador_id=0 OR 1=1` apaga a tabela inteira.
- **Módulos CIPA/Brigada/Termos**: dezenas de `DELETE`/`INSERT` com `id` (e em alguns casos texto livre tipo "assunto" ou nome de arquivo enviado) concatenados sem bind — ver detalhamento na seção "Achados por módulo" abaixo.

### 9. Ausência sistêmica de controle de acesso por grupo em módulos inteiros
Confirmado por grep exaustivo: **0 dos 31 endpoints de `app/cipa/`, 0 dos 30 de `app/brigada/`, e nenhum de `app/termos/`** checam `$_SESSION['idGrupo']`. Esses módulos são pensados para perfis restritos (CIPA, Brigada, TI/RH — grupos 4/7/9 no caso de Termos), mas como a checagem de grupo simplesmente não existe no backend, **qualquer conta autenticada no sistema principal — inclusive grupo 8 "Candidatos externos" — pode acessar essas telas e seus endpoints diretamente**, incluindo o painel de "Usuários" de cada módulo. A restrição do login específico de `app/termos/login_auth.php` (grupos 4/7/9) é irrelevante, porque quem já está logado pelo login principal do RH cai na mesma sessão compartilhada e pode simplesmente navegar direto para `app/termos/index.php`.

### 10. Vazamento de denúncias de ouvidoria (dados descriptografados) sem autenticação
`app/rh_ficha_ouvidoria_aj.php` não tem checagem de sessão nem de grupo — decripta e devolve nome, e-mail, telefone, relato e nomes de testemunhas de qualquer denúncia de assédio/má conduta por `id`, embora a **página** (`rh_ficha_ouvidoria.php`) corretamente restrinja a acesso aos grupos 3 (Psicólogos)/9 (Super). A chave AES usada para descriptografar está hardcoded e duplicada em 3 arquivos (`'minha_senha_32_chars_segura_x!'`), então também é trivialmente recuperável do código-fonte.

### 11. `app/api/api_supervisor.php` — PII de toda a empresa sem autenticação, CORS aberto
`header('Access-Control-Allow-Origin: *')`, sem `session_start()`/checagem alguma. `?id=1..N` devolve nome, e-mail corporativo, telefone e foto de qualquer colaborador e do respectivo gestor — de qualquer site, sem login.

---

## ALTO

### IDOR generalizado — "o servidor confia no `id` que o cliente manda"
Esse padrão se repete em dezenas de arquivos, com dados cada vez mais sensíveis:

- **Ponto eletrônico**: `app/ponto/api/registrar_ponto.php` (bater ponto **de outro colaborador**, sem checagem alguma — `colaborador_id` do POST vira o registro assinado com hash de integridade), `espelho_assina.php` (assinar cartão-ponto de outro colaborador), `ultimas_batidas.php`/`ultimas_batidas_web.php` (histórico de ponto + localização GPS de qualquer colaborador, sem login), `edita_cartao_aj3.php` (excluir solicitação de ponto de qualquer um, sem login).
- **Gestor**: `app/gestor/ficha_colab.php` (ficha completa — CPF, salário, conta bancária, PIS, dependentes — de **qualquer** colaborador da empresa via `?id=`, não só da equipe do gestor, ao contrário dos arquivos-irmãos que corretamente usam `getSubordinados()`); `app/gestor/includes/ponto_aj.php`/`ponto_aj2.php` (ver/aprovar solicitações de ponto de qualquer supervisor, não só o seu — e `supervisor_id` concatenado sem bind, SQLi); `app/gestor/includes/ferias_aj2.php` (aprovar férias de qualquer colaborador da empresa, bastando saber login/senha válidos **de qualquer gestor**, não do gestor correto).
- **Colaborador**: `app/colaborador/dados_aj4.php` (sobrescrever nome/CPF/RG/e-mail de **outra pessoa**, `idPessoa` do POST, e SQL sem bind), `dados_aj1/2/3.php` (ler/criar endereço e documentos de qualquer `idPessoa`), `includes/ferias_aj2.php` (editar agenda de férias de qualquer colaborador).
- **Documentos de RH** (`app/includes/`): `rh_docs_vis_aj.php`, `rh_docs_aj4.php`, `rh_docs_aj5.php` (metadados + nome físico do arquivo de qualquer documento, sem login) e `rh_docs_del_aj.php` (exclui qualquer documento, sem login, com SQLi); `rh_pessoa_aj6/10/11/12/15.php` (endereço, antecedentes criminais, contatos de emergência, fotos — de qualquer pessoa, sem login).
- **Saúde ocupacional e Rescisão**: `rh_saude_aj1/2.php` e `rh_rescisao_aj1/2.php` checam só se existe sessão, não o grupo — qualquer conta autenticada (inclusive candidato externo) lê exames ocupacionais e dados financeiros de rescisão de qualquer colaborador.
- **Recrutamento**: `recrutamento/inc/candidato_cv_aj.php` (dados de diversidade — deficiência, raça, orientação sexual, identidade de gênero — de qualquer candidatura, sem checar se pertence à subsede do recrutador logado); `vaga_fluxo_mover_aj.php`/`candidato_fluxo_mover_aj.php`/`vaga_edit_salvar_aj.php` (mesma falta de escopo por subsede).
- **Talentos**: `talentos/new_aj1.php` — antes do login, busca por CPF devolve nome completo, telefone, e-mail, gênero, nacionalidade, escolaridade, LinkedIn e se é colaborador — permite enriquecer listas de CPF vazadas em massa.

### XSS armazenado
- **`vagas/vaga_perfil.php`** (página pública) — a função `conteudo_rico()` só escapa texto que **não** tenha nenhuma tag HTML; assim que o campo (vindo do editor Summernote, sem sanitização ao salvar) tem qualquer tag, é impresso cru. Qualquer visitante anônimo que abra a vaga executa o script.
- **`recrutamento/aprova.php`** — pior que o anterior: os mesmos campos (`descricao`, `experiencia`, `atividades`, etc.) são ecoados **sem nenhum escape**, nem o `conteudo_rico()` do item acima. Essa página é aberta pelo **superintendente** a partir de um link de e-mail — um solicitante de vaga (perfil de menor privilégio) pode plantar um script que roda no navegador de quem aprova a vaga.

### CSRF — ausência total de proteção
Nenhum token CSRF foi encontrado em lugar nenhum do código (grep por `csrf`/`token` só retorna os tokens de reset de senha e de aprovação de vaga, mecanismos diferentes). A proteção depende inteiramente do cookie de sessão, e o `php.ini` não define `session.cookie_samesite`, nem o código chama `session_set_cookie_params()` em lugar nenhum — o SameSite fica no default do navegador, não é garantido pela aplicação.

### `app/includes/rh_docs_eml_aj.php` — path traversal + exfiltração por e-mail + SQLi
`$idPessoa`/`$arquivo` vêm crus do POST (via `extract()`), sem checagem de posse, montam `"../docs/pessoa_$idPessoa/$arquivo"` sem sanitizar `../`, e o arquivo é anexado e enviado por e-mail para **qualquer destinatário também controlado pelo atacante**. Um usuário autenticado (mesmo de baixo privilégio) pode pedir o envio por e-mail de `../../includes/conexao_gerar.php` (credenciais do banco) para o próprio e-mail dele. O INSERT de log do envio também concatena `$titulo`/`$mensagem` sem bind.

### `app/includes/rh_docs_inc_aj.php` / `rh_docs_edt_aj.php` — `idPessoa` da sessão sobrescrito por POST
`extract($dados)` roda **depois** de `$idPessoa = $_SESSION['idPessoa']` ser lido, e sobrescreve a variável com o valor enviado pelo cliente — usado tanto no caminho de destino do upload quanto (sem bind) na query SQL. Efeito combinado: grava documento na pasta de outra pessoa e injeta SQL via `idPessoa`/`idEmpresa`/`idTipo`.

### `app/termos/` — assinatura de termo não vinculada à identidade do signatário
O token de assinatura (`bin2hex(random_bytes(16))`, gerado corretamente) identifica o termo certo, mas `index_aj11.php` valida **qualquer** login/senha válido do sistema contra o termo, sem checar se é o dono do termo — qualquer funcionário com credencial válida pode "assinar" o termo de responsabilidade de outra pessoa. Combinado com `index_aj12.php`/`index_aj14.php` (sem token, sem rate limit, aceitam usuário/senha para "dar baixa"), isso também funciona como **oráculo de força bruta de senha não autenticado e sem limite de tentativas** contra `rh_usuarios`.

---

## MÉDIO

- **`extract($_POST)`/`extract($dados)` disseminado em ~119 arquivos** — qualquer chave do POST vira variável PHP local, inclusive sobrescrevendo variáveis já existentes no escopo (como visto no item de `rh_docs_inc_aj.php` acima). É a causa raiz de vários dos achados de IDOR/SQLi. Recomenda-se substituir por atribuição explícita de cada campo esperado.
- **`header('Location: ...')` sem `exit()`/`die()`** depois, em dezenas de arquivos de CIPA/Brigada/Termos (`aj1`, `aj8`, `aj13`, `aj19`, `aj24/25`, `aj31` e equivalentes) — hoje o request só quebra com erro fatal (porque `$conn` não existe fora do `if`), mas é um controle de acesso que depende de um acidente de implementação, não de design; se `display_errors` estiver ligado, vaza caminho de arquivo/stack trace.
- **Nomes de arquivo previsíveis** em upload de foto de perfil (`"usu_" . idUsuario . "." . extensao`, sem parte aleatória) — facilita adivinhar onde um upload malicioso cairia.
- **Cookies de sessão sem `HttpOnly`/`Secure`/`SameSite`** configurados nem no `php.ini` nem via `session_set_cookie_params()` no código — amplia o impacto de qualquer XSS (furto de cookie) e de CSRF.
- **`rh_cv_conq.idLogin`** gravado como `varchar` recebendo `"0"`/`"66"` como texto (mencionado também no diagnóstico de FKs) — não é vulnerabilidade em si, mas é sintoma do mesmo padrão de tipagem solta que facilita os bugs acima.

## BAIXO

- `recrutamento/inc/testar_usuario.php` — enumeração de login/e-mail de recrutador sem autenticação (dá feedback "usuário existe/não existe" pré-login).
- `app/termos/teste.php` — arquivo de debug esquecido em produção, dispara envio de e-mail real sem autenticação nem parâmetros.
- `app/termos/login_auth.php:58` — hardcoda `$_SESSION['idGrupo'] = 2` independente do grupo real (bug funcional, não é o vetor de exploração principal já que a sessão vem do login central, mas mostra que esse valor nunca foi confiável nesse módulo).
- Injeção de cabeçalho de e-mail — **não explorável hoje**: o PHPMailer está na versão 6.8.0, que já neutraliza `\r\n` em `Subject`/destinatário internamente.
- Command Injection — **nada encontrado**: os únicos `exec`/`popen` do código são de string fixa (helper de terminal) ou uso interno do PHPMailer, sem input do usuário.

---

## Achados por módulo (detalhamento rápido, para o time priorizar)

| Módulo | Sem checagem de sessão | Sem checagem de grupo (`idGrupo`) | SQLi confirmada | Upload sem whitelist |
|---|---|---|---|---|
| `app/includes/` (núcleo) | ~13 arquivos (docs, pessoa, ouvidoria, cv, reajuste) | rh_saude*, rh_rescisao* (só checam sessão) | login_aj2, rh_docs_edt/eml_aj | ~~rh_perfil_aj, rh_usuarios_inc_aj, rh_pessoa_aj4/7/13, rh_colab_aj3/4, rh_docs_inc_aj~~ ✅ corrigido (item 4) |
| `app/cipa/` | 10 de 31 arquivos | **0 de 31** | aj4,12,16,23,29 (+ SQLi residual em aj15/18/20/22/27/30 fora do trecho de upload/documento) | ~~aj3,6,15,20,27,30~~ ✅ corrigido (item 4) |
| `app/brigada/` | 10 de 30 arquivos | **0 de 30** | aj4,12,16,23,29 (+ SQLi residual em aj15/18/20/22/27/30 fora do trecho de upload/documento) | ~~aj3,6,15,18,20,22,27,30~~ ✅ corrigido (item 4) |
| `app/termos/` | aj9,aj12,aj13,aj14,aj18 | **nenhum** (login próprio existe mas é bypassável pela sessão central) | aj12, aj14 | ~~aj10,aj13,aj16~~ ✅ corrigido (item 4) |
| `app/ponto/` | registrar_ponto, ultimas_batidas(_web), edita_cartao_aj2/3, solicitacoes_aj, localizacao_aj/aj1 | — | cron_banco_horas.php | — |
| `app/colaborador/` `app/gestor/` | dados_aj1, index_aj1, ferias_aj1 | ficha_colab (sem scope de equipe) | dados_aj4, gestor/ponto_aj | — |
| `talentos/` | — (padrão de sessão é bem aplicado) | — | esqueci_senha.php (idEmpresa) | ~~candidatos_aj1_fa, candidatos_aj3_con, new_aj8_fa, new_aj13_conq~~ ✅ corrigido (item 4) |
| `recrutamento/` | — | vaga_fluxo_mover_aj, candidato_fluxo_mover_aj, candidato_cv_aj (sem scope de subsede) | — | — |
| `vagas/` | (público por design, sem PII exposta) | — | — | — |

> Nota: item 4 corrigiu, de brinde, o SQL Injection nas queries de `rh_documentos`/upload logo ao lado do código de upload nos arquivos acima (bind de parâmetro). As demais queries dos **mesmos arquivos** (ex.: `DELETE ... WHERE id = $id` de reunião/ação/membro) **não foram tocadas** e continuam vulneráveis — por isso aj15/18/20/22/27/30 ainda aparecem na coluna de SQLi.

---

## Plano de ação sugerido (ordem de prioridade)

1. ⬜ **Rotacionar todas as credenciais** já commitadas no git (banco, Anthropic, AWS SES, Google) — item independente de qualquer deploy de código. *(depende de ação fora do repositório — não verificável por código.)*
2. ✅ ~~Corrigir os 3 endpoints de takeover de conta (`rh_usuario_alt_aj.php`, `rh_usuario_exc_aj.php`, `reset_senha_aj.php`)~~ — feito (itens 2 e 3).
3. ✅ ~~Bloquear execução de PHP em todas as pastas de upload via `.htaccess`~~ — feito (item 4), incluindo whitelist de extensão + validação de MIME real em todos os endpoints de upload confirmados.
4. ✅ ~~Fechar `app/docs/` e PDFs de termos para acesso direto~~ — feito (item 7): gateway `docs_view.php` com checagem de sessão/posse + `.htaccess` de negação total. ⚠️ **`app/temp/` continua com o mesmo gap parcial**: o `.htaccess` só bloqueia execução de PHP, não leitura direta de arquivo — os arquivos que ficam ali temporariamente (staging de OCR) ainda são baixáveis por quem adivinhar o nome. Não estava no escopo do item 7.
5. ⬜ **Adicionar checagem de `idGrupo` nos módulos CIPA/Brigada/Termos** — hoje é ausência total, não caso a caso. *(item 9, ainda não iniciado.)*
6. ⬜ Revisar sistematicamente os arquivos que usam `extract($_POST)`/`extract($dados)` e trocar por atribuição explícita + bind de parâmetro em toda query. *(item 8 cobre uma parte; o restante segue pendente — ver nota na tabela de módulos sobre SQLi residual em CIPA/Brigada fora do trecho de upload/documento.)*
7. ⬜ Adicionar token CSRF (ou pelo menos `SameSite=Strict/Lax` nos cookies de sessão) como camada adicional.
8. ⬜ Tratar os demais IDORs (ponto, gestor, ouvidoria, recrutamento) conforme a criticidade do dado exposto em cada caso.

Nenhuma alteração foi feita no código ou no banco durante a análise original — os itens marcados ✅ acima já foram corrigidos e commitados desde então; o restante é o que falta priorizar.
