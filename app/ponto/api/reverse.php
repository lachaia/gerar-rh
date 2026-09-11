<?php
header("Content-Type: application/json; charset=utf-8");

if (!isset($_GET['lat']) || !isset($_GET['lon'])) {
    echo json_encode(["erro" => "Coordenadas ausentes"]);
    exit;
}

$lat = $_GET['lat'];
$lon = $_GET['lon'];

$url = "https://nominatim.openstreetmap.org/reverse?format=jsonv2&lat={$lat}&lon={$lon}&accept-language=pt-BR";

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

// **IMPORTANTE**: Nominatim exige user-agent
curl_setopt($ch, CURLOPT_USERAGENT, "GerarRH/1.0 contato@seudominio.com"); 

$response = curl_exec($ch);
$erro = curl_error($ch);
curl_close($ch);

if ($erro) {
    echo json_encode(["erro" => $erro]);
} else {
    echo $response;
}
