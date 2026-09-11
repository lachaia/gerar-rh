<?php 
//
//- localizacao_aj1.php | EXCLUI Localização - API
// (C)haia, 31/10/2025
//

session_start();

include dirname(__DIR__) . '/../includes/conexao_gerar.php';

$parametros = filter_input_array(INPUT_POST, FILTER_DEFAULT);
if ($parametros) extract($parametros);

$sql = "DELETE FROM rh_ponto_enderecos WHERE id = :id";
$stmt = $conn->prepare($sql);
$stmt->bindValue(':id', $id, PDO::PARAM_INT);
$stmt->execute();

$retorno = [
    "idEndereco" => $conn->lastInsertId(),
    "status" => true,
    "msg" => '
        <div class="bg-blue-100 border border-blue-300 text-blue-800 px-4 py-3 rounded-lg">
            <strong class="font-semibold">OK!</strong>
            <span>Sucesso ao Excluir localização!</span>
        </div>'
];

echo json_encode($retorno);