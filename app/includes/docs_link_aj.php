<?php
//
// docs_link_aj.php | Gera uma URL assinada de curta duração para docs_view.php.
// Usado quando o preview precisa ser buscado por um serviço externo (ex.:
// Google Docs Viewer, para docx/xlsx/pptx) que não envia cookie de sessão.
//

session_start();

require_once __DIR__ . '/f_docs_token.php';

if (!isset($_SESSION['idLogin']) || (int) ($_SESSION['idGrupo'] ?? 0) === 8) {
    http_response_code(403);
    die(json_encode(['status' => false, 'msg' => 'Acesso negado.']));
}

$idPessoa = filter_input(INPUT_GET, 'pessoa', FILTER_VALIDATE_INT);
$pasta    = $_GET['pasta'] ?? null;
$arquivo  = $_GET['arquivo'] ?? '';

$pastasPermitidas = ['cipa', 'brigada'];

if ($arquivo === '' || (!$idPessoa && !in_array($pasta, $pastasPermitidas, true))) {
    http_response_code(400);
    die(json_encode(['status' => false, 'msg' => 'Parâmetros inválidos.']));
}

$query = $idPessoa
    ? docs_token_gerar_query_pessoa($idPessoa, $arquivo, 300)
    : docs_token_gerar_query_pasta($pasta, $arquivo, 300);

// A URL completa é montada no cliente (precisa ser um endereço público
// e absoluto, pois quem busca esse link é o serviço externo de preview,
// não o navegador do usuário).
echo json_encode(['status' => true, 'query' => $query]);
