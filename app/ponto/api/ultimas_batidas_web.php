<?php
//
// ultimas_batidas.php | Endpoint API do Módulo de Ponto Eletrônico
// (C)haia, 30/10/2025
//

session_start();
header('Content-Type: text/html; charset=utf-8');

if (!isset($_POST['idColab'])) {
    echo "Parâmetro ausente.";
    exit;
}

include "../../includes/conexao_gerar.php";

$idColab = intval($_POST['idColab']);

if(empty($idColab)) die("<p class='text-red-600 text-sm text-center'>Colaborador não encontrado.</p>");

// Consulta as 9 últimas batidas do usuário
$sql = "SELECT data_hora, tipo, endereco_texto, origem 
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

    $icon_web = '<i class="fa-solid fa-desktop"></i>';
    $icon_mobile = '<i class="fa-solid fa-mobile-screen"></i>';

    foreach ($batidas as $b) {
        $data = date('d/m', strtotime($b['data_hora']));
        $hora = date('H:i', strtotime($b['data_hora']));
        $tipo = $b['tipo'];
        $endereco = $b['endereco_texto'];

        $icon_origem = '<i class="fa-regular fa-hand"></i>';
        if( $b['origem']=='web'  ) $icon_origem = $icon_web;
        if( $b['origem']=='app'  ) $icon_origem = $icon_mobile;

        echo "<tr>
                <td class='text-gray-500 text-sm text-center'>" . $data . " " . $hora . "</td>
                <td class='text-gray-500 text-sm text-center'>" . $icon_origem . "</td>
                <td class='text-gray-500 text-sm text-left'>" . $tipo . "</td>
                <td class='text-gray-500 text-sm text-left'>" . $endereco . "</td>
            </tr>
        ";
    }

    echo "</div>";
} else {
    echo "<p class='text-gray-500 text-sm text-center'>Nenhuma batida encontrada.</p>";
}
?>
