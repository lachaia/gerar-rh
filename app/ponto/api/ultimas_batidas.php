<?php
//
// ultimas_batidas.php | Endpoint API do Módulo de Ponto Eletrônico
// (C)haia, 30/10/2025
//

session_start();
header('Content-Type: text/html; charset=utf-8');

if (empty($_SESSION['idLogin']) || empty($_SESSION['idColab'])) {
    http_response_code(403);
    die("Sessão inválida.");
}

include "../../includes/conexao_gerar.php";

// Sempre as batidas do próprio usuário logado — nunca de quem o cliente pedir.
$idColab = (int) $_SESSION['idColab'];

if(empty($idColab)) die("<p class='text-red-600 text-sm text-center'>Colaborador não encontrado.</p>");

// Consulta as 9 últimas batidas do usuário
$sql = "SELECT data_hora 
        FROM rh_ponto_registros 
        WHERE colaborador_id = :colaborador_id 
        ORDER BY data_hora DESC 
        LIMIT 9";
$stmt = $conn->prepare($sql);
$stmt->bindParam(':colaborador_id', $idColab);
$stmt->execute();

$batidas = $stmt->fetchAll(PDO::FETCH_ASSOC);

if ($batidas) {
    echo "<div class='flex overflow-x-auto space-x-3 p-2 scrollbar-thin scrollbar-thumb-indigo-300 scrollbar-track-gray-100'>";

    foreach ($batidas as $b) {
        $data = date('d/m', strtotime($b['data_hora']));
        $hora = date('H:i', strtotime($b['data_hora']));

        echo "
        <div class='flex-none w-24 bg-white rounded-lg shadow-md p-3 border border-gray-300 text-center'>
            <p class='text-sm font-semibold text-gray-700'>$data</p>
            <p class='text-lg font-bold text-indigo-600'>$hora</p>
        </div>
        ";
    }

    echo "</div>";
} else {
    echo "<p class='text-gray-500 text-sm text-center'>Nenhuma batida encontrada.</p>";
}
?>
