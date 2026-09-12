<?php
//
// f_ouvidoria_cripto.php | Criptografa/descriptografa os campos sensíveis
// de denúncias e ouvidoria (nome, e-mail, telefone, relato, testemunhas...).
// Centraliza a chave (antes hardcoded e duplicada em ~7 arquivos) num único
// lugar, lida do .env.
//

require_once __DIR__ . '/env.php';

define('OUVIDORIA_VETOR_IV', substr(hash('sha256', 'vetor-unico'), 0, 16));

function ouvidoria_chave_cripto(): string
{
    return $_ENV['OUVIDORIA_CRYPTO_KEY'] ?? '';
}

function criptografar(?string $texto): string|false
{
    return openssl_encrypt((string) $texto, 'AES-256-CBC', ouvidoria_chave_cripto(), 0, OUVIDORIA_VETOR_IV);
}

function descriptografar(?string $textoCriptografado): string|false
{
    if ($textoCriptografado === null || $textoCriptografado === '') {
        return '';
    }
    return openssl_decrypt($textoCriptografado, 'AES-256-CBC', ouvidoria_chave_cripto(), 0, OUVIDORIA_VETOR_IV);
}
