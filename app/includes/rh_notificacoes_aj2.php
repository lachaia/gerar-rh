<?php
//-- rh_notificacoes_aj2.php

session_start();

include "conexao_gerar.php";

$idUsuario = intval($_GET['usuario']);

// 1. Buscar todos os eventos
$sql = "SELECT id, nome FROM rh_notificacoes_eventos";
$stmt = $conn->prepare($sql);
$stmt->execute();
$todos = $stmt->fetchAll(PDO::FETCH_ASSOC);

// 2. Buscar eventos atribuídos
$sql = "SELECT idEvento 
        FROM rh_notificacoes_usuarios 
        WHERE idUsuario = ?";
$stmt = $conn->prepare($sql);
$stmt->execute([$idUsuario]);
$atribuidos = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Extrair os IDs dos eventos atribuídos
$ids = array_column($atribuidos, 'idEvento');

// 3. Separar os eventos atribuídos e disponíveis
$atribuidoEventos = [];
$disponiveisEventos = [];

foreach ($todos as $evento) {
    if (in_array($evento['id'], $ids)) {
        $atribuidoEventos[] = $evento;
    } else {
        $disponiveisEventos[] = $evento;
    }
}

// 4. Retornar como JSON
echo json_encode([
    'atribuidos' => $atribuidoEventos,
    'disponiveis' => $disponiveisEventos
]);
