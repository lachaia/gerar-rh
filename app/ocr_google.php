<?php
//
//-- Inicia a Extração do Texto usando Google Document AI
//-- Chaia, 27/02/2024 | (U) 12/06/2025

# Includes the autoloader for libraries installed with composer
require __DIR__ . '/vendor/autoload.php';

# Imports the Google Cloud client library
use Google\Cloud\DocumentAI\V1\DocumentProcessorServiceClient;
use Google\Cloud\DocumentAI\V1\RawDocument;

$caminho_arquivo = __DIR__ . "/temp/" . $_POST['arquivo'];

if( ! file($caminho_arquivo)){
    die("Arquivo não encontrado #1");
}

// Caminho para o seu arquivo JSON de credenciais local
$credentialsPath = 'chaves/gerarocr-ba77063bf0b6.json';

$projectId = 'gerarocr';            # Your Google Cloud Platform project ID
$location  = 'us';                  # Your Processor Location
$processor = 'b86f5952bc686426';    # Your Processor ID

# Create Client
$client = new DocumentProcessorServiceClient([
    'credentials' => json_decode(file_get_contents( $credentialsPath ), true),
]);

# Read in File Contents
$handle = fopen($caminho_arquivo, 'rb');

//$contents = fread($handle, filesize($arquivo));
$contents = file_get_contents($caminho_arquivo);

fclose($handle);
//var_dump($contents);

$mime = tipo_mime( $caminho_arquivo );

$rawDocument = new RawDocument([
    'content' => $contents,
    'mime_type' => $mime
]);

# Fully-qualified Processor Name
$name = $client->processorName($projectId, $location, $processor);

# Make Processing Request
$response = $client->processDocument($name, [
    'rawDocument' => $rawDocument
]);

# Print Document Text
die($response->getDocument()->getText());

function tipo_mime($caminho_arquivo) {
    $extensao = strtolower(pathinfo($caminho_arquivo, PATHINFO_EXTENSION));
    $mapa_mime = [
        'pdf' => 'application/pdf',
        'gif' => 'image/gif',
        'tiff' => 'image/tiff',
        'tif' => 'image/tiff',
        'jpeg' => 'image/jpeg',
        'jpg' => 'image/jpeg',
        'png' => 'image/png',
        'bmp' => 'image/bmp',
        'webp' => 'image/webp',
        // Adicione mais extensões e tipos MIME conforme necessário
    ];
    return isset($mapa_mime[$extensao]) ? $mapa_mime[$extensao] : null;
}
