<?php
//
//-- Inicia a Extração do Texto de Arquivos Excel usando PhpSpreadsheet
//-- Chaia, 27/02/2024

error_reporting(E_ALL);
ini_set('display_errors', 1);

//echo "<br>ocr_excel.php";

require 'vendor/autoload.php'; // Caminho para o arquivo de autoload gerado pelo Composer
use PhpOffice\PhpSpreadsheet\IOFactory;

$caminho_arquivo = "documentos/" . $_POST['arquivo'];

if (!file($caminho_arquivo)) {
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
