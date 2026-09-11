<?php
//
// f_docs_token.php | Assina/valida URLs temporárias para docs_view.php.
// Usado quando um preview precisa ser buscado por um serviço externo
// (ex.: Google Docs Viewer) que não envia o cookie de sessão do usuário.
// O token é um HMAC de curta duração, válido só para aquele arquivo.
//
// $identificador é "pessoa:<idPessoa>" ou "pasta:<nome>" — mesmo valor
// calculado dos dois lados (aqui e em docs_view.php) para bater a assinatura.
//

require_once __DIR__ . '/env.php';

function docs_token_segredo(): string
{
    return $_ENV['DOCS_TOKEN_SECRET'] ?? '';
}

function docs_token_assinatura(string $identificador, string $arquivo, int $exp): string
{
    return hash_hmac('sha256', "$identificador|$arquivo|$exp", docs_token_segredo());
}

function docs_token_valido(string $identificador, string $arquivo, ?string $exp, ?string $sig): bool
{
    if (empty($exp) || empty($sig) || !ctype_digit((string) $exp)) {
        return false;
    }
    if ((int) $exp < time()) {
        return false; // expirado
    }
    $esperado = docs_token_assinatura($identificador, $arquivo, (int) $exp);
    return hash_equals($esperado, $sig);
}

/**
 * Gera a query string assinada (pessoa, arquivo, exp, sig) para docs_view.php,
 * para um documento de app/docs/pessoa_<id>/.
 */
function docs_token_gerar_query_pessoa(int $idPessoa, string $arquivo, int $ttlSegundos = 300): string
{
    $arquivo = basename($arquivo);
    $exp = time() + $ttlSegundos;
    $sig = docs_token_assinatura("pessoa:$idPessoa", $arquivo, $exp);

    return http_build_query([
        'pessoa'  => $idPessoa,
        'arquivo' => $arquivo,
        'exp'     => $exp,
        'sig'     => $sig,
    ]);
}

/**
 * Gera a query string assinada (pasta, arquivo, exp, sig) para docs_view.php,
 * para um documento coletivo de app/docs/<pasta>/ (ex.: "cipa", "brigada").
 */
function docs_token_gerar_query_pasta(string $pasta, string $arquivo, int $ttlSegundos = 300): string
{
    $arquivo = basename($arquivo);
    $exp = time() + $ttlSegundos;
    $sig = docs_token_assinatura("pasta:$pasta", $arquivo, $exp);

    return http_build_query([
        'pasta'   => $pasta,
        'arquivo' => $arquivo,
        'exp'     => $exp,
        'sig'     => $sig,
    ]);
}
