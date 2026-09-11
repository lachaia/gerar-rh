<?php
// includes/conexao_gerar.php
// Conexão PDO usando pacote cifrado de credenciais
// Lê BI_MASTER_KEY da env (se definida) e a propaga para DB_MASTER_KEY,
// que é o nome que helpers/crypto.php utiliza por padrão.

//echo "<pre>__DIR__ = " . __DIR__ . "</pre>";


require_once __DIR__ . '/../helpers/crypto.php';

try {
    // Se a variável BI_MASTER_KEY estiver definida (ex.: via Apache SetEnv),
    // propagamos para DB_MASTER_KEY para compatibilidade com get_master_key().
    $biKey = getenv('BI_MASTER_KEY');
    if ($biKey !== false && trim($biKey) !== '') {
        // Define DB_MASTER_KEY no ambiente para que helpers/crypto.php consiga ler.
        // Usamos putenv + atualizar $_ENV/$_SERVER para máxima compatibilidade.
        putenv('DB_MASTER_KEY=' . $biKey);
        $_ENV['DB_MASTER_KEY'] = $biKey;
        $_SERVER['DB_MASTER_KEY'] = $biKey;
    }

    // Caminho do pacote cifrado (ajuste conforme seu ambiente)
    $env = getenv('APP_ENV') ?: 'development';
    $dev = ($env === 'development');
    if ($dev) {
        // Windows local (exemplo)
        $encPath = 'C:/.secrets/db_cred.enc';
    } else {
        // produção (ajuste conforme sua infra)
       // $encPath = realpath(__DIR__ . '/GERAR/db_cred.enc');
        $encPath = dirname(__DIR__) . '/../GERAR/db_cred.enc';
    }

    if (!file_exists($encPath)) {
        throw new RuntimeException("Pacote cifrado não encontrado em $encPath");
    }

    // Lê pacote cifrado
    $pkg = file_get_contents($encPath);
    if ($pkg === false) throw new RuntimeException("Falha ao ler o pacote cifrado.");

    // Descriptografa usando a master key (get_master_key() pega do DB_MASTER_KEY / arquivo .key)
    $json = decrypt_secret($pkg);

    // Converte em array
    $cred = json_decode($json, true);
    if (!is_array($cred)) throw new RuntimeException("Pacote descriptografado inválido.");

    $host   = $cred['host'];
    $port   = $cred['port'];
    $dbname = $cred['dbname'];
    $user   = $cred['user'];
    $pass   = $cred['pass'];

    // Cria PDO
    $dsn = "mysql:host={$host};port={$port};dbname={$dbname};charset=utf8mb4";
    $conn = new PDO($dsn, $user, $pass, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    ]);

} catch (Throwable $e) {
    if (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && $_SERVER['HTTP_X_REQUESTED_WITH'] === 'XMLHttpRequest') {
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode([
            'status' => false,
            'mensagem' => 'Falha ao conectar ao banco de dados.',
            'detalhe' => $e->getMessage()
        ], JSON_UNESCAPED_UNICODE);
        exit;
    } else {
        die("Erro na conexão: " . $e->getMessage());
    }
}
