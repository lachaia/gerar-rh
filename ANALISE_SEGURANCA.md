# Análise de Segurança — Sistema de RH (GERAR)

> Auditoria de código estático (leitura apenas, nada foi alterado ou executado contra produção) cobrindo `app/`, `app/colaborador/`, `app/gestor/`, `app/ponto/`, `app/cipa/`, `app/brigada/`, `app/termos/`, `app/api/`, `recrutamento/`, `talentos/`, `vagas/`. Escopo: SQL Injection, controle de acesso/IDOR, upload de arquivo/path traversal, XSS/CSRF, segredos expostos.

## Como ler este documento

Os achados estão organizados por severidade. Dentro de cada nível, por categoria. Cada item tem arquivo(s):linha quando disponível, um cenário de exploração concreto, e uma recomendação objetiva. O padrão de causa-raiz se repete o tempo todo: **o app cresceu por dezenas de arquivos `_aj*.php` numerados, copiados uns dos outros ao longo do tempo, e o padrão de segurança correto (`isset($_SESSION['idLogin'])` + `exit()` + bind de parâmetro + checagem de posse do registro) foi aplicado de forma inconsistente** — alguns arquivos de um mesmo módulo estão corretos, os "irmãos" ao lado não.

---

## CRÍTICO — ação imediata

✅ Todos os itens CRÍTICO (2 a 11) foram corrigidos e commitados — ver histórico do git e o "Plano de ação sugerido" abaixo para o resumo de cada um. Os achados restantes (ALTO/MÉDIO/BAIXO abaixo) ainda estão pendentes.

---

## ALTO

### IDOR generalizado — "o servidor confia no `id` que o cliente manda" ✅ RESOLVIDO
Esse padrão se repetia em dezenas de arquivos, com dados cada vez mais sensíveis. Todos os 7 grupos abaixo foram corrigidos (commits `1004917`, `de3e8d5`, `4dc44d7`, `060c317`, `50e1a9c`, `e58b388`, `5f45a04`, `21d6df4`):

- ~~**Ponto eletrônico**: `app/ponto/api/registrar_ponto.php` (bater ponto **de outro colaborador**...), `espelho_assina.php`, `ultimas_batidas.php`/`ultimas_batidas_web.php`, `edita_cartao_aj3.php`~~ ✅ — `colaborador_id`/`idColab` agora sempre vem de `$_SESSION['idColab']`, nunca do cliente; `espelho_assina.php` e `edita_cartao_aj3.php` verificam posse antes de assinar/excluir. *(`edita_cartao_aj2.php`, `solicitacoes_aj.php`, `localizacao_aj.php`/`aj1.php`, listados na tabela de módulos abaixo, não foram investigados nesta rodada — não estavam no texto original deste achado.)*
- ~~**Gestor**: `ficha_colab.php`, `ponto_aj.php`/`ponto_aj2.php`, `ferias_aj2.php`~~ ✅ — `ficha_colab.php` agora usa `getSubordinados()` como os arquivos-irmãos; `ponto_aj.php` usa sempre o `supervisor_id` da sessão (+ bind, fechando a SQLi); `ponto_aj2.php` verifica que o `supervisor_id` da solicitação bate com quem decide; `ferias_aj2.php` (o pior caso — qualquer gestor aprovava com a própria senha) agora só aprova férias de subordinado real, verificado por `getSubordinados()` antes mesmo de checar a senha.
- ~~**Colaborador**: `dados_aj4.php`, `dados_aj1/2/3.php`, `includes/ferias_aj2.php`~~ ✅ — `idPessoa`/`idColab` sempre vêm da sessão em todas as cópias (`app/colaborador/*.php` e `app/colaborador/includes/*.php`, 10 arquivos ao todo); `dados_aj5.php` (alterar endereço, achado durante o mapeamento) também corrigido.
- ~~**Documentos de RH**: `rh_docs_vis_aj.php`, `rh_docs_aj4.php`, `rh_docs_aj5.php`, `rh_docs_del_aj.php`, `rh_pessoa_aj6/10/11/12/15.php`~~ ✅ — todos agora exigem `isset($_SESSION['idLogin'])`; SQLi fechada com bind; `rh_docs_del_aj.php` também corrigido para apagar o arquivo físico da pasta do dono real do documento (bug latente que deixava arquivo órfão).
- ~~**Saúde ocupacional e Rescisão**: `rh_saude_aj1/2.php`, `rh_rescisao_aj1/2.php`~~ ✅ — agora exigem `idGrupo` em (1, 9): RH e Super Usuário.
- ~~**Recrutamento**: `candidato_cv_aj.php`, `vaga_fluxo_mover_aj.php`, `candidato_fluxo_mover_aj.php`, `vaga_edit_salvar_aj.php`~~ ✅ — não era falta de escopo por subsede (a listagem `fluxo.php` já mostra todas as vagas para qualquer logado, de propósito); restringido por `idGrupo` em (6, 9): Recrutamento e Super Usuário, decisão confirmada com o usuário.
- ~~**Talentos**: `talentos/new_aj1.php`~~ ⚠️ parcialmente — o `LIKE` com o CPF cru do cliente (permitia varrer CPFs por wildcard) virou comparação exata normalizada. Continua em aberto, de propósito (não dá pra exigir login num fluxo pré-login): quem já sabe um CPF exato ainda recebe nome/telefone/e-mail/gênero/nacionalidade/escolaridade/LinkedIn — mitigar isso de verdade exigiria rate limiting/CAPTCHA, inexistente hoje em todo o sistema.

### XSS armazenado ✅ RESOLVIDO (commit `9c86829`)
- ~~**`vagas/vaga_perfil.php`** (página pública) — a função `conteudo_rico()` só escapava texto que **não** tivesse nenhuma tag HTML; qualquer tag (inclusive `<script>`) era impressa crua~~ ✅ — mesma lógica quebrada existia duplicada em `recrutamento/vaga_view.php` e `recrutamento/vaga_edit.php`. Centralizada em `app/includes/f_html_seguro.php`, agora sanitiza de verdade com o HTMLPurifier (já vendorizado, dependência transitiva existente) antes de exibir.
- ~~**`recrutamento/aprova.php`** — os mesmos campos eram ecoados **sem nenhum escape**~~ ✅ — os 6 campos (`descricao`, `experiencia`, `atividades`, `c_comportamentais`, `c_tecnicas`, `equipamentos`) agora passam pelo mesmo `conteudo_rico()` sanitizado.

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
- ~~`header('Location: ...')` sem `exit()`/`die()` depois, em dezenas de arquivos de CIPA/Brigada/Termos~~ ✅ corrigido (item 9), junto com a checagem de grupo — ver abaixo.
- **Nomes de arquivo previsíveis** em upload de foto de perfil (`"usu_" . idUsuario . "." . extensao`, sem parte aleatória) — facilita adivinhar onde um upload malicioso cairia.
- **Cookies de sessão sem `HttpOnly`/`Secure`/`SameSite`** configurados nem no `php.ini` nem via `session_set_cookie_params()` no código — amplia o impacto de qualquer XSS (furto de cookie) e de CSRF.
- **`rh_cv_conq.idLogin`** gravado como `varchar` recebendo `"0"`/`"66"` como texto (mencionado também no diagnóstico de FKs) — não é vulnerabilidade em si, mas é sintoma do mesmo padrão de tipagem solta que facilita os bugs acima.

## BAIXO

- `recrutamento/inc/testar_usuario.php` — enumeração de login/e-mail de recrutador sem autenticação (dá feedback "usuário existe/não existe" pré-login).
- `app/termos/teste.php` — arquivo de debug esquecido em produção, dispara envio de e-mail real sem autenticação nem parâmetros.
- ~~`app/termos/login_auth.php:58` — hardcodava `$_SESSION['idGrupo'] = 2`~~ ✅ corrigido (item 9) — precisava guardar o grupo real para a checagem de acesso do módulo funcionar também para quem loga pela tela própria de Termos.
- Injeção de cabeçalho de e-mail — **não explorável hoje**: o PHPMailer está na versão 6.8.0, que já neutraliza `\r\n` em `Subject`/destinatário internamente.
- Command Injection — **nada encontrado**: os únicos `exec`/`popen` do código são de string fixa (helper de terminal) ou uso interno do PHPMailer, sem input do usuário.

---

## Achados por módulo (detalhamento rápido, para o time priorizar)

| Módulo | Sem checagem de sessão | Sem checagem de grupo (`idGrupo`) | SQLi confirmada | Upload sem whitelist |
|---|---|---|---|---|
| `app/includes/` (núcleo) | ~1 arquivo (cv, reajuste — docs/pessoa já corrigidos no IDOR) | ~~rh_saude*, rh_rescisao*~~ ✅ corrigido (IDOR): exige `idGrupo` em (1,9) | ~~login_aj2~~ ✅ corrigido (item 8); rh_docs_edt/eml_aj (aberto, ver ALTO) | ~~rh_perfil_aj, rh_usuarios_inc_aj, rh_pessoa_aj4/7/13, rh_colab_aj3/4, rh_docs_inc_aj~~ ✅ corrigido (item 4) |
| Ouvidoria/Acolhimento (`rh_ficha_ouvidoria*.php`, `rh_ouvidoria*.php`, `includes/rh_*ouvir*.php`, `rh_acolhimento_aj*.php`) | ~~14 de 17 arquivos~~ ✅ corrigido (item 10) — 3 seguem públicos de propósito (formulário de denúncia) | ~~nenhum~~ ✅ corrigido (item 10): exige `idGrupo` em (3,9); também corrigido bug de precedência de operador em `rh_ficha_ouvidoria.php` que tornava a checagem antiga inofensiva na prática | — | — |
| `app/cipa/` | ~~10 de 31 arquivos~~ ✅ corrigido (item 9) | ~~0 de 31~~ ✅ corrigido (item 9): exige `dcCIPA=1` ou Super (grupo 9) | ~~aj4,12,16,18,22,23,29~~ ✅ corrigido (item 8) | ~~aj3,6,15,20,27,30~~ ✅ corrigido (item 4) |
| `app/brigada/` | ~~10 de 30 arquivos~~ ✅ corrigido (item 9) | ~~0 de 30~~ ✅ corrigido (item 9): exige `dcBrigada=1` ou Super (grupo 9) | ~~aj4,12,16,18,22,23,29~~ ✅ corrigido (item 8) | ~~aj3,6,15,18,20,22,27,30~~ ✅ corrigido (item 4) |
| `app/termos/` | ~~aj9,aj12,aj13,aj14,aj18~~ ✅ corrigido (item 9, exceto aj12/aj14 que continuam de propósito sem sessão — ver nota) | ~~nenhum~~ ✅ corrigido (item 9): exige `idGrupo` em (4,7,9) | ~~aj12, aj14~~ ✅ corrigido (item 8) | ~~aj10,aj13,aj16~~ ✅ corrigido (item 4) |
| `app/ponto/` | ~~registrar_ponto, ultimas_batidas(_web), edita_cartao_aj3~~ ✅ corrigido (IDOR); edita_cartao_aj2, solicitacoes_aj, localizacao_aj/aj1 não investigados nesta rodada | — | ~~cron_banco_horas.php~~ ✅ corrigido (item 8) | — |
| `app/colaborador/` `app/gestor/` | ~~dados_aj1~~ ✅ corrigido (IDOR); index_aj1, ferias_aj1 não investigados nesta rodada | ~~ficha_colab (sem scope de equipe)~~ ✅ corrigido (IDOR): usa `getSubordinados()` | ~~dados_aj4, gestor/ponto_aj~~ ✅ corrigido (IDOR) | — |
| `talentos/` | — (padrão de sessão é bem aplicado) | — | esqueci_senha.php (idEmpresa) | ~~candidatos_aj1_fa, candidatos_aj3_con, new_aj8_fa, new_aj13_conq~~ ✅ corrigido (item 4) |
| `recrutamento/` | — | ~~vaga_fluxo_mover_aj, candidato_fluxo_mover_aj, candidato_cv_aj~~ ✅ corrigido (IDOR): exige `idGrupo` em (6,9) | — | — |
| `vagas/` | (público por design, sem PII exposta) | — | — | — |
| `app/api/` | ~~api_supervisor.php~~ ✅ corrigido (item 11): removido `Access-Control-Allow-Origin: *` (não fazia sentido — só é chamado servidor->servidor) e restringido a chamada vinda do próprio servidor, mesmo padrão do item 5/6 | — | — | — |

> Nota: item 4 corrigiu, de brinde, o SQL Injection nas queries de `rh_documentos`/upload logo ao lado do código de upload em vários arquivos de CIPA/Brigada. O item 8 fechou o restante — todas as queries `DELETE`/`INSERT`/`UPDATE` com `id` cru (reunião, ação, membro, documento) nesses módulos, mais `login_aj2.php`, `rh_reajuste_aj1.php`, `cron_banco_horas.php` e `app/termos/index_aj12.php`/`index_aj14.php`. `dados_aj4` (colaborador) e `gestor/ponto_aj` tinham SQLi combinado com IDOR — fechados junto com a correção de IDOR generalizado (ver seção ALTO).
>
> Nota (item 9): `app/termos/assinar.php`, `index_aj11.php`, `index_aj12.php` e `index_aj14.php` continuam sem exigir `idGrupo` de propósito — são o fluxo público de assinatura (quem recebe/devolve o equipamento confirma com o próprio usuário+senha via token, não necessariamente alguém do grupo 4/7/9). A falta de vínculo entre essa senha e o dono real do termo já está registrada acima, em "assinatura de termo não vinculada à identidade do signatário" (ALTO) — item 9 não mexeu nisso.
>
> Nota (item 10): existem **dois sistemas paralelos** de denúncia — o mais antigo (`rh_ouvidoria`, com dados reais em produção) e um mais novo (`rh_denuncias`/"acolhimento", cuja tabela **não existe** no banco de produção — o fluxo já vem quebrado, então o achado ali é teórico até a tabela ser criada). Os 3 formulários de **envio** de denúncia (`rh_ouvir_aj.php`, `includes/rh_ouvidoria_inc_aj.php`, `includes/rh_acolhimento_aj3.php`) foram deixados sem checagem de sessão de propósito — são o formulário público, inclusive para denúncia anônima. Todos os pontos de **leitura/gestão** (ficha, grid, excluir, fechar, linha do tempo) agora exigem grupo 3 (Psicólogos) ou 9 (Super). A chave AES usada para criptografar (antes hardcoded e duplicada em 7 arquivos, não 3 como a auditoria original estimou) foi centralizada em `app/includes/f_ouvidoria_cripto.php`, lida de `OUVIDORIA_CRYPTO_KEY` no `.env` — mantido o valor idêntico ao anterior, já que trocar a chave tornaria os registros já gravados permanentemente ilegíveis.

---

## Plano de ação sugerido (ordem de prioridade)

1. ⬜ **Rotacionar todas as credenciais** já commitadas no git (banco, Anthropic, AWS SES, Google) — item independente de qualquer deploy de código. *(depende de ação fora do repositório — não verificável por código.)*
2. ✅ ~~Corrigir os 3 endpoints de takeover de conta (`rh_usuario_alt_aj.php`, `rh_usuario_exc_aj.php`, `reset_senha_aj.php`)~~ — feito (itens 2 e 3).
3. ✅ ~~Bloquear execução de PHP em todas as pastas de upload via `.htaccess`~~ — feito (item 4), incluindo whitelist de extensão + validação de MIME real em todos os endpoints de upload confirmados.
4. ✅ ~~Fechar `app/docs/` e PDFs de termos para acesso direto~~ — feito (item 7): gateway `docs_view.php` com checagem de sessão/posse + `.htaccess` de negação total. ⚠️ **`app/temp/` continua com o mesmo gap parcial**: o `.htaccess` só bloqueia execução de PHP, não leitura direta de arquivo — os arquivos que ficam ali temporariamente (staging de OCR) ainda são baixáveis por quem adivinhar o nome. Não estava no escopo do item 7.
5. ✅ ~~Corrigir o SQL Injection não autenticado em `login_aj2.php`, `rh_reajuste_aj1.php`, `cron_banco_horas.php` e nos módulos CIPA/Brigada/Termos~~ — feito (item 8): todas as queries `DELETE`/`INSERT`/`UPDATE` que concatenavam `id` (ou nome de arquivo) cru nesses módulos agora usam bind de parâmetro; `login_aj2.php` e `rh_reajuste_aj1.php` também passaram a exigir sessão/parâmetro validado, e `cron_banco_horas.php` só aceita chamada com sessão válida ou vinda do próprio servidor (mesmo padrão do item 5/6).
6. ✅ ~~Adicionar checagem de `idGrupo` nos módulos CIPA/Brigada/Termos~~ — feito (item 9): CIPA e Brigada agora exigem a flag por usuário (`dcCIPA`/`dcBrigada`) ou Super Usuário (grupo 9) em **todos** os 34+33 arquivos PHP dos dois módulos (nenhum ficou de fora); Termos exige `idGrupo` em (4,7,9), com exceção proposital do fluxo público de assinatura (token+senha, ver nota na tabela de módulos). De passagem: corrigido o bug de `app/termos/login_auth.php` que hardcodava `idGrupo=2` (impedia a própria checagem de funcionar para quem loga pela tela de Termos) e adicionado `exit()` faltante depois de 21 `header('Location...')` que só "funcionavam" por acidente (erro fatal por `$conn` indefinido) — ambos eram pré-requisitos para a correção funcionar de verdade.
7. ✅ ~~Corrigir o vazamento de denúncias de ouvidoria/acolhimento~~ — feito (item 10): ficha, grid, excluir, fechar e linha do tempo agora exigem grupo 3 (Psicólogos) ou 9 (Super); os 3 formulários de envio continuam públicos de propósito (denúncia anônima). Corrigido também um bug de precedência de operador em `rh_ficha_ouvidoria.php` que fazia a checagem de grupo pré-existente não bloquear ninguém na prática, e centralizada a chave AES (antes hardcoded em 7 arquivos) em `OUVIDORIA_CRYPTO_KEY` no `.env`.
8. ✅ ~~Fechar `app/api/api_supervisor.php` (PII sem autenticação, CORS aberto)~~ — feito (item 11): removido o CORS aberto (desnecessário — só é chamado servidor→servidor via `file_get_contents()`, nunca por JS de navegador) e restringida a chamada ao próprio servidor, mesmo padrão usado nos itens 5/6/8 para APIs internas equivalentes.

**Com isso, todos os itens CRÍTICO (2 a 11) estão corrigidos.** Segue o que falta, todo em severidade ALTO/MÉDIO/BAIXO:

9. ✅ ~~Tratar os IDORs generalizados (ponto, gestor, colaborador, documentos de RH, saúde/rescisão, recrutamento, talentos)~~ — feito, nos 7 grupos do achado "IDOR generalizado" (ver seção ALTO): commits `1004917` (ponto), `de3e8d5` (gestor), `4dc44d7` (colaborador), `060c317`+`50e1a9c` (documentos de RH), `e58b388` (saúde/rescisão), `5f45a04` (recrutamento), `21d6df4` (talentos, parcial — rate limiting/CAPTCHA continuam em aberto, não existem em nenhum ponto do sistema hoje).
10. ✅ ~~Corrigir o XSS armazenado em vagas (vaga_perfil.php, aprova.php)~~ — feito (commit `9c86829`): `conteudo_rico()` centralizada em `app/includes/f_html_seguro.php`, agora sanitiza de verdade com HTMLPurifier em vez de imprimir HTML cru assim que o campo tivesse qualquer tag.
11. ⬜ Revisar sistematicamente os arquivos que usam `extract($_POST)`/`extract($dados)` e trocar por atribuição explícita + bind de parâmetro em toda query. *(o SQLi de CIPA/Brigada/Termos e dos IDORs de ponto/colaborador/gestor já foi fechado; o restante — `rh_docs_edt_aj.php`/`rh_docs_eml_aj.php` — segue pendente, ver seção ALTO.)*
12. ⬜ Adicionar token CSRF (ou pelo menos `SameSite=Strict/Lax` nos cookies de sessão) como camada adicional.

Nenhuma alteração foi feita no código ou no banco durante a análise original — os itens marcados ✅ acima já foram corrigidos e commitados desde então; o restante é o que falta priorizar.
