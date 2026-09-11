<?php
require_once "../includes/conexao_gerar.php";

$id = $_POST['id'];

$sql = "SELECT  S.*, IFNULL(S.responsavel, 'Não Informado') AS responsavel, 
                C.nome as cidade, C.uf as uf, C.pais as pais
                FROM rh_subsedes S
                left outer join rh_cidades C on C.idCidade = S.cidade_id
                WHERE S.id = ?";
$stmt = $conn->prepare($sql);
$stmt->execute([$id]);
$dados = $stmt->fetch(PDO::FETCH_ASSOC);

if($dados) {
    echo json_encode(['status' => true, 'dados' => $dados]);
} else {
    echo json_encode(['status' => false, 'msg' => 'Não encontrado']);
}