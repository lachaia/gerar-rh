<?php 
//
//- localizacao_aj.php | Salva Nova Localização - API
// (C)haia, 31/10/2025
//

session_start();

include dirname(__DIR__) . '/../includes/conexao_gerar.php';

$parametros = filter_input_array(INPUT_POST, FILTER_DEFAULT);
if ($parametros) extract($parametros);
/*
include "../../includes/debug.php";
debug(json_encode($parametros, JSON_PRETTY_PRINT));

 localizacao_aj.php | 2025-10-31 11:04:49 
{
    "nomeEndereco": "Trabalho",
    "lat": "-25.49833371706701",
    "lon": "-49.31271491943525",
    "idColab": "1",
    "enderecoReferencia": "Rua Senador Accioly Filho, 511, Cidade Industrial de Curitiba, Curitiba-PR"
}
*/

$sql = "INSERT INTO rh_ponto_enderecos (colaborador_id, nome_local, endereco, criado_em, lat, lon)
        VALUES (:idColab, :nomeEndereco, :enderecoReferencia, NOW(), :lat, :lon)";
$stmt = $conn->prepare($sql);
$stmt->bindValue(':idColab', $idColab, PDO::PARAM_INT);
$stmt->bindValue(':nomeEndereco', $nomeEndereco, PDO::PARAM_STR);
$stmt->bindValue(':enderecoReferencia', $enderecoReferencia, PDO::PARAM_STR);
$stmt->bindValue(':lat', $lat, PDO::PARAM_STR);
$stmt->bindValue(':lon', $lon, PDO::PARAM_STR);
$stmt->execute();

$retorno = [
    "idEndereco" => $conn->lastInsertId(),
    "dsEndereco" => $enderecoReferencia,
    "status" => true,
    "msg" => '
        <div class="bg-blue-100 border border-blue-300 text-blue-800 px-4 py-3 rounded-lg">
            <strong class="font-semibold">OK!</strong>
            <span>Sucesso ao salvar localização!</span>
        </div>'
];

echo json_encode($retorno);