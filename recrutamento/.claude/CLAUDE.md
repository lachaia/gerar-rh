# Módulo Recrutamento (R&S) — Padrões e Convenções

Este documento descreve os padrões de código, estrutura e estilo já estabelecidos neste
módulo (`d:\xampp\htdocs\rh\recrutamento`), extraídos das páginas existentes
(`vaga_new.php`, `aprova.php`, `index.php`) e dos CRUDs de `motivos.php`,
`vagas_status.php` e `superintendentes.php`. Siga estas convenções ao criar novas
telas/CRUDs neste módulo, para manter consistência.

## 1. Conexão e sessão

- A conexão com o banco (PDO/MySQL) é sempre feita via
  `include "../app/includes/conexao_gerar.php"` (a partir da raiz de `recrutamento/`)
  ou `include "../../app/includes/conexao_gerar.php"` (a partir de `recrutamento/inc/`).
  A variável resultante é sempre `$conn` (objeto `PDO`).
- Toda página e todo endpoint AJAX começam com `session_start()` e checam
  `$_SESSION['idLogin']`; se não estiver setada, redireciona:
  ```php
  session_start();
  if (!isset($_SESSION['idLogin'])) {
      header("location: ../app/logout.php"); // páginas em recrutamento/
      // ou: header("location: ../logout.php"); // arquivos em recrutamento/inc/
      exit();
  }
  ```
- `$_SESSION['idLogin']` é usado como coluna de auditoria (`login_id`) em INSERTs.

## 2. Estrutura de uma página

Toda página "cheia" (não-AJAX) segue o mesmo esqueleto:

```php
<?php
session_start();
if (!isset($_SESSION['idLogin'])) { header("location: ../app/logout.php"); exit(); }
include "../app/includes/conexao_gerar.php";
$titulo_pagina = "TÍTULO DA PÁGINA";
include 'inc/header.php';
?>
<!-- HTML da página -->
<script src="js/pagina.js"></script>
<?php include 'inc/footer.php'; ?>
```

- `inc/header.php` monta o `<head>`, a sidebar e o topo (usa Bootstrap 5.3, DataTables
  1.13.6, jQuery 3.7, Font Awesome 6.3, todos via CDN).
- `inc/footer.php` fecha as divs e inclui `js/rotinas.js` (colapso da sidebar).
- CSS específico de página fica em `css/pagina.css`; quando é só um detalhe pequeno
  (ex.: `.icon-box`), pode ir num `<style>` inline logo após o `include 'inc/header.php'`.

## 3. Padrão de CRUD (lista + modal + AJAX)

Para uma tabela de cadastro/lookup (ex.: `rs_vagas_mot`, `rs_vagas_status`,
`rs_superintendentes`), o padrão é: **uma página com DataTable + modal Bootstrap** e
**um arquivo AJAX por ação**, dentro de `recrutamento/inc/`.

### 3.1 Nomenclatura de arquivos

Para uma tabela `xxx` (nome curto e descritivo, não precisa repetir o nome físico
da tabela):

| Arquivo                          | Responsabilidade                                   |
|-----------------------------------|-----------------------------------------------------|
| `recrutamento/xxx.php`            | Página: tabela (DataTable) + modal Incluir/Editar   |
| `recrutamento/inc/xxx_aj.php` ou `xxxs_aj.php` | Lista os dados para o DataTable (`SELECT`)          |
| `recrutamento/inc/xxx_get_aj.php` | Busca 1 registro (para preencher o modal de edição) |
| `recrutamento/inc/xxx_inc_aj.php` | Inclusão (`INSERT`)                                 |
| `recrutamento/inc/xxx_alt_aj.php` | Alteração (`UPDATE`)                                |
| `recrutamento/inc/xxx_exc_aj.php` | Exclusão (`DELETE`, com checagem de uso — ver 3.5)  |
| `recrutamento/js/xxx.js`          | Toda a lógica de front-end (grid + modal + ajax)    |

Não existe (e não deve ser criado) um `_aj.php` genérico com `switch($_POST['acao'])` —
cada ação tem seu próprio arquivo, propositalmente pequeno e de responsabilidade única.

### 3.2 Endpoint de listagem (`xxx_aj.php`)

Alimenta o DataTable no formato "client-side" (`serverSide: false`), retornando
sempre o mesmo formato:

```php
$sql = "SELECT ... FROM tabela ORDER BY ...";
$stmt = $conn->prepare($sql);
$stmt->execute();
$recordsFiltered = $stmt->rowCount();

$dados = array();
while ($linha = $stmt->fetch(PDO::FETCH_ASSOC)) {
    extract($linha);
    $dado = array();
    // monta células HTML (badges de status, botões de ação) já prontas
    $dado[] = htmlspecialchars($campo);
    $dado[] = "<span class='badge bg-success'>Ativo</span>";
    $dado[] = "<a href='#!' onclick='editar_xxx({$id})'>...</a>";
    $dados[] = $dado;
}

echo json_encode([
    "draw" => 1,
    "recordsTotal" => intval($recordsFiltered),
    "recordsFiltered" => intval($recordsFiltered),
    "data" => $dados
]);
exit;
```

A coluna de "Ações" é montada em PHP (HTML pronto), não no JS, com botões
`btn-outline-info` (editar, ícone `fa-pen`) e `btn-outline-danger` (excluir, ícone
`fa-trash`), chamando funções JS globais `editar_xxx(id)` / `excluir_xxx(id)`.

### 3.3 Endpoints de get/incluir/alterar

Sempre `header('Content-Type: application/json; charset=utf-8');` e resposta no
formato:

```php
echo json_encode(["status" => true|false, "msg" => "texto para o usuário", ...]);
exit;
```

- `status` é **booleano real** (não string `"1"/"0"`).
- `msg` é texto simples (não HTML pronto) — quem decide a cor do alerta é o JS.
- Entrada sempre validada com `filter_input(INPUT_POST, ...)`, nunca lendo
  `$_POST[...]` diretamente.
- Toda query usa **prepared statements com `bindParam`** — nunca concatenar valor de
  usuário direto na SQL.
- Campos de texto exibidos depois em HTML passam por `htmlspecialchars()` no momento
  da listagem.

### 3.4 Endpoint de exclusão — sempre protegido por integridade referencial

Antes de fazer `DELETE`, o endpoint `xxx_exc_aj.php` **verifica se o registro já está
em uso** em alguma tabela que referencia seu `id` (ex.: `rs_vagas.motivo_id`,
`rs_vagas.status_id`, `rs_vagas_aprova.super_id`). Se estiver em uso, a exclusão é
bloqueada e a mensagem sugere usar o campo `ativo` (editar e desmarcar) em vez de
excluir:

```php
$sql = "SELECT COUNT(*) AS qtd FROM tabela_dependente WHERE fk_id = :id";
// ...
if ($uso['qtd'] > 0) {
    echo json_encode(["status" => false, "msg" => "Já utilizado em N registro(s)... Desative em vez de excluir."]);
    exit;
}
// só então executa o DELETE
```

Isso evita registros órfãos e histórico quebrado (vagas antigas continuam
exibindo o motivo/status/superintendente corretamente).

### 3.5 Front-end (`xxx.js`)

- Uma variável global `dataTable`, inicializada em `constroi_grid()` chamada no
  `$(document).ready()`.
- `DataTable` usa `serverSide: false`, `ajax: {url: 'inc/xxx_aj.php', type: 'POST'}` e
  `language: {url: 'inc/pt-br.json'}` (tradução pt-BR compartilhada do módulo).
- `atualiza_grid()` recarrega a tabela via `dataTable.ajax.reload(null, false)` após
  qualquer inclusão/alteração/exclusão bem-sucedida.
- O modal (Bootstrap `.modal`) serve tanto para Incluir quanto Editar — o campo
  hidden `id` vazio indica inclusão; preenchido indica edição. O título do modal
  (`#modalXxxTitulo`) muda conforme o caso.
- Salvar não usa `<form action="...">` tradicional: o botão é `type="button"` com
  `onclick="salvar_xxx()"`, que monta um `FormData(formElement)`, ajusta campos que
  não vêm certos em checkbox (`formData.set('ativo', $('#x').is(':checked') ? 1 : 0)`)
  e envia via `$.ajax` com `processData: false, contentType: false`.
- Mensagens de sucesso/erro aparecem dentro do próprio modal, num
  `<div id="msgAlertaXxx">`, com `alert alert-success`/`alert alert-danger`. Ao
  salvar com sucesso, o modal fecha sozinho depois de ~800ms
  (`setTimeout(() => $('#modalXxx').modal('hide'), 800)`).
- Excluir usa `confirm()` nativo do navegador antes de chamar o endpoint; o
  resultado (`res.msg`) é mostrado em `alert()`.

## 4. Estilo visual

- Tema escuro (Bootstrap `bg-dark`, `text-white`, `border-secondary`) em todo o
  módulo — ver `css/styles.css` (`background-color: #121212`, sidebar `#1a1a1a`).
- **Pegadinha corrigida em `css/styles.css`:** `.card` define
  `background-color: #212529 !important` mas não definia `color` — o Bootstrap
  aplica a variável `--bs-body-color` (escura, do tema claro padrão) como cor do
  texto dentro do card, resultando em texto invisível (mesma cor do fundo) sempre
  que um texto sem classe explícita (`text-white`, `text-white-50`, badge, etc.)
  é colocado direto dentro de um `.card-body`. Foi por isso que `.card-header` já
  tinha um `color: #ffffff !important` manual. Corrigido adicionando
  `.card-body { color: #e0e0e0; }` globalmente — mas ao criar paineis de
  "somente leitura" com texto solto (ex.: `<dl>/<dt>/<dd>`), prefira ainda assim
  uma classe explícita (`text-white-50`, etc.) em vez de confiar só no
  default, para não reintroduzir o problema se o CSS mudar.
- Cabeçalho de página padrão: ícone circular (`.icon-box`, 45x45px, `border-radius:
  50%`) + título (`h4.text-white.fw-bold`) + subtítulo (`p.text-white-50.small`),
  dentro de uma barra com botões de ação à direita (`Novo X`, `Voltar ao Painel R&S`).
- Tabelas usam `table table-dark table-hover` dentro de um `.card` com
  `card-body p-1` e `table-responsive`.
- Badges de situação: `bg-success` (Ativo) / `bg-secondary` (Inativo). Quando a cor é
  dinâmica (ex.: status de vaga), usa `style="background-color: {$cor_fundo}"` com a
  classe de contraste de texto (`text-light`/`text-dark`) vinda do próprio registro.
- Ícones Font Awesome (`fa-solid`) em todos os botões e links de navegação.

## 5. Menu lateral (sidebar)

- A sidebar (`inc/header.php`) é "mini" (70px, só ícones) e expande para 250px
  (classe `.active` em `#sidebar`) ao clicar em "Recolher" — texto dos itens fica em
  `<span class="menu-text">`, escondido quando a sidebar está colapsada.
- Itens de cadastro (tabelas de apoio/lookup) ficam agrupados num menu colapsável
  "Cadastros" (Bootstrap `collapse`), não soltos na sidebar principal:
  ```html
  <a href="#submenuCadastros" class="nav-link collapsed" data-bs-toggle="collapse"
     role="button" aria-expanded="false" aria-controls="submenuCadastros">
      <i class="fa-solid fa-folder-tree text-secondary"></i>
      <span class="menu-text">Cadastros</span>
      <i class="fa-solid fa-chevron-down ms-auto menu-text chevron-icon"></i>
  </a>
  <div class="collapse" id="submenuCadastros">
      <a href="<?= $baseDir . '/xxx.php' ?>" class="nav-link submenu-link">
          <i class="fa-solid fa-circle-dot"></i>
          <span class="menu-text">Nome do Cadastro</span>
      </a>
      <!-- ... mais subitens ... -->
  </div>
  ```
- Novo CRUD de cadastro → adicionar um `<a>` dentro de `#submenuCadastros`, na mesma
  ordem em que aparece no menu.
- CSS do submenu (`.submenu-link`, `.chevron-icon`) está em `css/styles.css`.

## 6. Checklist para um novo CRUD de cadastro

1. Descobrir a estrutura real da tabela (`DESCRIBE tabela`) e dados de exemplo antes
   de codar — nunca supor nomes de coluna.
2. Verificar se a tabela é referenciada por FK em outra (ex.: `rs_vagas`) para saber
   se a exclusão precisa de guarda (quase sempre precisa).
3. Criar os 7 arquivos do padrão (página, `_aj` de lista, `get`, `inc`, `alt`, `exc`,
   `.js`), seguindo os templates das seções 3.2–3.5.
4. Adicionar o link no submenu "Cadastros" em `inc/header.php`.
5. Rodar `php -l` em todos os arquivos PHP criados/editados.
6. Testar a listagem (somente leitura) contra o banco antes de considerar pronto —
   **nunca rodar INSERT/UPDATE/DELETE de teste contra o banco de produção**
   (`conexao_gerar.php` aponta para produção — `$_SESSION['BD'] = 'PRODUCAO'`).
