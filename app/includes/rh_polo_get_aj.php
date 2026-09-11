<?php
//
//- unidade_get_aj.php | Lê dados da Unidade (ver, editar)
//= (C)haia, 15/07/2026
//

require_once "conexao_gerar.php";
$id = $_POST['id'];

$sql = "SELECT S.identificador as subsede_ds, U.*, C.nome as cidade
                FROM rh_polos U
                INNER JOIN rh_subsedes S on S.subsede_id = U.subsede_id
                INNER JOIN rh_cidades C on C.idCidade = U.cidade_id
                WHERE U.id = ?";
$stmt = $conn->prepare($sql);
$stmt->execute([$id]);
$dados = $stmt->fetch(PDO::FETCH_ASSOC);

if($dados) {
    if( empty($dados['responsavel']) ) $dados['responsavel'] = 'Não Informado';
    echo json_encode(['status' => true, 'dados' => $dados]);
} else {
    echo json_encode(['status' => false, 'msg' => 'Não encontrado']);
}