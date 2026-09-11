<?php
// helpers/crypto.php
// AES-256-GCM helpers: encrypt_secret / decrypt_secret
// Espera-se que a chave mestra seja fornecida como HEX (64 chars) na env DB_MASTER_KEY
// ou em arquivo /etc/secrets/bi_master.key (conteúdo HEX, sem newline indesejado).

declare(strict_types=1);

function get_master_key(): string {
    // Preferência: variável de ambiente (hex)
    $hex = getenv('DB_MASTER_KEY');
    if ($hex !== false && strlen(trim($hex)) === 64) {
        $key = @hex2bin(trim($hex));
        if ($key !== false && strlen($key) === 32) return $key;
    }

    // Alternativa: arquivo seguro (fora do docroot)
    $path = 'c:/.secrets/bi_master.key';
    if (file_exists($path)) {
        $hex2 = trim(file_get_contents($path));
        if (strlen($hex2) === 64) {
            $key = @hex2bin($hex2);
            if ($key !== false && strlen($key) === 32) return $key;
        }
    }

    throw new RuntimeException('Chave mestra não encontrada ou inválida. Defina DB_MASTER_KEY hex ou crie /etc/secrets/bi_master.key com 64 hex chars.');
}

/**
 * Criptografa $plaintext usando AES-256-GCM.
 * Retorna base64(nonce(12) | tag(16) | ciphertext)
 */
function encrypt_secret(string $plaintext): string {
    $key = get_master_key();
    if (strlen($key) !== 32) throw new RuntimeException('Chave mestra inválida (tamanho).');

    $nonce = random_bytes(12); // 96 bits recomendado para GCM
    $tag = ''; // será preenchido por openssl
    $ciphertext = openssl_encrypt($plaintext, 'aes-256-gcm', $key, OPENSSL_RAW_DATA, $nonce, $tag, '', 16);
    if ($ciphertext === false) throw new RuntimeException('Erro ao criptografar com OpenSSL.');

    $package = $nonce . $tag . $ciphertext;
    return base64_encode($package);
}

/**
 * Descriptografa o pacote base64 gerado por encrypt_secret.
 * Retorna o plaintext (string).
 */
function decrypt_secret(string $package_base64): string {
    $key = get_master_key();
    if (strlen($key) !== 32) throw new RuntimeException('Chave mestra inválida (tamanho).');

    $data = base64_decode($package_base64, true);
    if ($data === false || strlen($data) < 28) {
        throw new RuntimeException('Pacote criptografado inválido.');
    }

    $nonce = substr($data, 0, 12);
    $tag   = substr($data, 12, 16);
    $ciphertext = substr($data, 28);

    $plaintext = openssl_decrypt($ciphertext, 'aes-256-gcm', $key, OPENSSL_RAW_DATA, $nonce, $tag);
    if ($plaintext === false) {
        throw new RuntimeException('Falha na descriptografia (auth tag inválido).');
    }
    return $plaintext;
}
