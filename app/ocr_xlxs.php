<?php
//
//-- Inicia a Extração do Texto de Arquivos Excel usando PhpSpreadsheet
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

//echo "<br>ocr_excel.php";

require 'vendor/autoload.php'; // Caminho para o arquivo de autoload gerado pelo Composer
use PhpOffice\PhpSpreadsheet\IOFactory;

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

if ($extensao != 'xlsx') {
    die("Arquivo não é do tipo xlsx");
}

try {
    // Carrega o arquivo Excel
    $spreadsheet = IOFactory::load($caminho_arquivo);

    // Inicializa uma variável para armazenar o texto extraído
    $texto_extraido = '';

    // Percorre todas as planilhas no arquivo
    foreach ($spreadsheet->getWorksheetIterator() as $worksheet) {
        // Percorre todas as linhas da planilha
        foreach ($worksheet->getRowIterator() as $row) {
            // Percorre todas as células da linha
            foreach ($row->getCellIterator() as $cell) {
                $texto_extraido .= $cell->getValue() . "\t"; // Use "\t" para separar as células por tabulação
            }
            $texto_extraido .= "\n"; // Adiciona uma quebra de linha após cada linha
        }
    }

    // Imprime o texto extraído
    echo nl2br($texto_extraido);
} catch (Exception $e) {
    echo 'Erro: ' . $e->getMessage();
}
?>
