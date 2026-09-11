<?php
//
// docs_view.php | Gateway para servir arquivos de dentro de app/docs/.
// A pasta em si fica bloqueada para acesso direto (ver app/docs/.htaccess) —
// todo mundo (staff, colaborador, candidato, ou o preview do Google Docs
// Viewer) passa por aqui, que valida sessão/posse antes de entregar o arquivo.
//
// Dois modos:
//   ?pessoa=<idPessoa>&arquivo=<nome>   -> app/docs/pessoa_<idPessoa>/<nome>
//   ?pasta=cipa|brigada&arquivo=<nome>  -> app/docs/<pasta>/<nome>
//     (documentos coletivos de comissão, não pertencem a uma pessoa só)
//

session_start();

require_once __DIR__ . '/includes/f_docs_token.php';

$PASTAS_COLETIVAS_PERMITIDAS = ['cipa', 'brigada'];

$idPessoa = filter_input(INPUT_GET, 'pessoa', FILTER_VALIDATE_INT);
$pasta    = $_GET['pasta'] ?? null;
$arquivo  = basename($_GET['arquivo'] ?? '');

if ($pasta !== null && !in_array($pasta, $PASTAS_COLETIVAS_PERMITIDAS, true)) {
    http_response_code(400);
    die('Parâmetros inválidos.');
}

if ($arquivo === '' || (!$idPessoa && !$pasta)) {
    http_response_code(400);
    die('Parâmetros inválidos.');
}

// Identificador usado na assinatura do token: "pessoa:<id>" ou "pasta:<nome>".
$identificador = $idPessoa ? "pessoa:$idPessoa" : "pasta:$pasta";

$autorizado = false;

// 1) URL assinada de curta duração (ex.: preview via Google Docs Viewer,
//    que busca o arquivo sem cookie de sessão nenhum).
if (docs_token_valido($identificador, $arquivo, $_GET['exp'] ?? null, $_GET['sig'] ?? null)) {
    $autorizado = true;
}

// 2) Sessão do sistema principal (staff/colaborador) — mantém o mesmo nível
//    de acesso que já existia nas telas internas (qualquer perfil logado,
//    exceto candidato externo, grupo 8). Restringir por equipe/posse fica
//    para uma correção à parte (ver ANALISE_SEGURANCA.md, item 9).
if (!$autorizado && isset($_SESSION['idLogin']) && (int) ($_SESSION['idGrupo'] ?? 0) !== 8) {
    $autorizado = true;
}

// 3) Sessão do portal de candidatos (talentos/) — só os próprios documentos,
//    e só faz sentido no modo "pessoa" (candidato não acessa docs coletivos).
if (!$autorizado && $idPessoa && isset($_SESSION['candidato_idPessoa'])
    && (int) $_SESSION['candidato_idPessoa'] === $idPessoa) {
    $autorizado = true;
}

if (!$autorizado) {
    http_response_code(403);
    die('Acesso negado.');
}

$diretorio_base = $idPessoa
    ? __DIR__ . "/docs/pessoa_$idPessoa/"
    : __DIR__ . "/docs/$pasta/";
$caminho_arquivo = $diretorio_base . $arquivo;

$caminho_real = realpath($caminho_arquivo);
$base_real = realpath($diretorio_base);

if ($caminho_real === false || $base_real === false
    || strncmp($caminho_real, $base_real, strlen($base_real)) !== 0
    || !is_file($caminho_real)) {
    http_response_code(404);
    die('Arquivo não encontrado.');
}

$mime = mime_content_type($caminho_real) ?: 'application/octet-stream';

header('Content-Type: ' . $mime);
header('Content-Disposition: inline; filename="' . rawurlencode(basename($caminho_real)) . '"');
header('Content-Length: ' . filesize($caminho_real));
header('X-Content-Type-Options: nosniff');
header('Cache-Control: private, max-age=0, must-revalidate');

readfile($caminho_real);
exit;
