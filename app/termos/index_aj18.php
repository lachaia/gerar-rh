<?php
// index_aj18.php | Salva aba ativa do módulo de termos na sessão

session_start();
header('Content-Type: application/json; charset=utf-8');

$tab = isset($_POST['tab']) ? trim((string)$_POST['tab']) : '';
$permitidas = ['#home', '#termos', '#modelos'];

if (!in_array($tab, $permitidas, true)) {
    echo json_encode([
        'status' => false,
        'msg' => 'Aba inválida.'
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

$_SESSION['termos_aba_ativa'] = ltrim($tab, '#');

echo json_encode([
    'status' => true
], JSON_UNESCAPED_UNICODE);
