<?php
// helpers/gera_pacote.php
// Gera um pacote cifrado das credenciais do DB usando helpers/crypto.php
// Uso (CLI): php helpers/gera_pacote.php

if (php_sapi_name() !== 'cli') {
    die("Este script deve ser executado apenas no terminal (CLI).\n");
}

// compatibilidade Windows (XAMPP)
if (!defined('STDIN'))  define('STDIN', fopen('php://stdin', 'r'));
if (!defined('STDOUT')) define('STDOUT', fopen('php://stdout', 'w'));
if (!defined('STDERR')) define('STDERR', fopen('php://stderr', 'w'));

require_once __DIR__ . '/crypto.php'; // ajusta se o arquivo estiver em outro caminho

fwrite(STDOUT, "=== Gerador de pacote cifrado - credenciais do DB ===\n\n");

try {
    // pergunta valores
    fwrite(STDOUT, "Host (ex: node214955-gerar.sp1.br.saveincloud.net.br): ");
    $host = trim(fgets(STDIN));
    if ($host === '') throw new RuntimeException('Host é obrigatório.');

    fwrite(STDOUT, "Porta [3306]: ");
    $port = trim(fgets(STDIN));
    if ($port === '') $port = '3306';

    fwrite(STDOUT, "Nome do banco (dbname): ");
    $dbname = trim(fgets(STDIN));
    if ($dbname === '') throw new RuntimeException('DB name é obrigatório.');

    fwrite(STDOUT, "Usuário DB: ");
    $user = trim(fgets(STDIN));
    if ($user === '') throw new RuntimeException('Usuário é obrigatório.');

    // senha sem eco (se suportado)
    fwrite(STDOUT, "Senha DB: ");
    if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
        // Windows: não há stty. Ler normalmente (menos seguro)
        $pass = trim(fgets(STDIN));
    } else {
        system('stty -echo');
        $pass = trim(fgets(STDIN));
        system('stty echo');
        fwrite(STDOUT, PHP_EOL);
    }
    if ($pass === '') throw new RuntimeException('Senha é obrigatória.');

    $payloadArr = [
        'host' => $host,
        'port' => (int)$port,
        'dbname' => $dbname,
        'user' => $user,
        'pass' => $pass,
    ];
    $payload = json_encode($payloadArr, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);

    // encriptar via helper
    $pkg = encrypt_secret($payload);

    // decidir caminho de saída
    $dev = false;
    try {
        $dev = function_exists('is_dev') ? is_dev() : false;
    } catch (Exception $e) {
        // se is_dev lançar, considerar false (produção)
        $dev = false;
    }

    if ($dev) {
        $secretsDir = realpath(__DIR__ . '/..') . DIRECTORY_SEPARATOR . '.secrets';
        if (!is_dir($secretsDir)) {
            if (!mkdir($secretsDir, 0700, true)) {
                throw new RuntimeException("Falha ao criar diretório de segredos em $secretsDir");
            }
        }
        $outPath = $secretsDir . DIRECTORY_SEPARATOR . 'db_cred.enc';
    } else {
        // padrão produção (ajuste se necessário)
        $outPath = 'c:/.secrets/db_cred.enc';
    }

    // salvar arquivo
    if (file_put_contents($outPath, $pkg) === false) {
        throw new RuntimeException("Falha ao gravar pacote cifrado em $outPath");
    }

    // tentar ajustar permissões (só em UNIX)
    if (strtoupper(substr(PHP_OS, 0, 3)) !== 'WIN') {
        @chown($outPath, 'www-data'); // caso falhe, ignora
        @chmod($outPath, 0600);
    }

    fwrite(STDOUT, "\nPacote cifrado gerado e salvo em: $outPath\n");
    fwrite(STDOUT, "Mantenha esse arquivo seguro e fora do controle de versão.\n");
    fwrite(STDOUT, "No servidor de produção, coloque a master key em /etc/secrets/bi_master.key ou defina DB_MASTER_KEY.\n");

} catch (Throwable $ex) {
    fwrite(STDERR, "Erro: " . $ex->getMessage() . PHP_EOL);
    exit(1);
}
