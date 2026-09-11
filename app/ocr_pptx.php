<?php
//
//-- Inicia a Extração do Texto de Arquivos PowerPoint usando PHPPresentation
//-- Chaia, 27/02/2024
//
// Só aceita chamada vinda do próprio servidor (ver ocr_gerar.php, que é
// quem invoca este arquivo internamente via cURL, sem sessão de usuário).
if (($_SERVER['REMOTE_ADDR'] ?? '') !== ($_SERVER['SERVER_ADDR'] ?? '!')
    && ($_SERVER['REMOTE_ADDR'] ?? '') !== '127.0.0.1') {
    http_response_code(403);
    die("Acesso negado.");
}

error_reporting(E_ALL);
ini_set('display_errors', 1);

require 'vendor/autoload.php'; // Caminho para o arquivo de autoload gerado pelo Composer
use PhpOffice\PhpPresentation\IOFactory;

// O arquivo enviado por ocr_gerar.php fica em app/temp/. basename() impede
// path traversal (ex.: "../../.env"), e a checagem de realpath garante que
// o caminho final continua dentro da pasta esperada.
$diretorio_base = __DIR__ . '/temp/';
$nome_arquivo = basename($_POST['arquivo'] ?? '');
$caminho_arquivo = $diretorio_base . $nome_arquivo;

$caminho_real = realpath($caminho_arquivo);
$base_real = realpath($diretorio_base);

if ($nome_arquivo === '' || $caminho_real === false || $base_real === false
    || strncmp($caminho_real, $base_real, strlen($base_real)) !== 0) {
    die("Arquivo não encontrado #1");
}

$extensao = strtolower(pathinfo($caminho_arquivo, PATHINFO_EXTENSION));

if ($extensao != 'pptx') {
    die("Arquivo não é do tipo pptx");
}

try {
    // Carrega o arquivo PowerPoint
    $presentation = IOFactory::load($caminho_arquivo);

    // Inicializa uma variável para armazenar o texto extraído
    $texto_extraido = '';

    // Percorre todas as slides no arquivo
    foreach ($presentation->getAllSlides() as $slide) {
        // Percorre todos os objetos de texto na slide
        foreach ($slide->getShapeCollection() as $shape) {
            if ($shape instanceof \PhpOffice\PhpPresentation\Shape\RichText) {
                $texto_extraido .= $shape->getPlainText() . "\n";
            }
        }
        
    }

    // Imprime o texto extraído
    echo nl2br($texto_extraido);
} catch (Exception $e) {
    echo 'Erro: ' . $e->getMessage();
}
?>
