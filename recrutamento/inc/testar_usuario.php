<?php
//
//- testar_usuario.php | Verifica status do Usuário
// by (C)haia, 13/08/2026
//

session_start();

$login = $_POST['usuario'];

include_once "../../app/includes/conexao_gerar.php";

$sql = "SELECT C.idOrgao, O.descricao, O.nivel, P.nome, P.email, U.* 
            FROM rh_usuarios U
            INNER JOIN rh_pessoas P on P.idPessoa = U.idPessoa
            INNER JOIN rh_colaboradores C on C.idPessoa = P.idPessoa
            INNER JOIN rh_organograma O on O.idOrgao = C.idOrgao
            WHERE O.nivel < 7 AND (U.login = :login OR P.email_corporativo = login) AND U.ativo = 1 
            LIMIT 1";
$stmt = $conn->prepare($sql);
$stmt->bindParam(':login', $login);
$stmt->execute();

if(  $stmt->rowCount() > 0 ){
    echo json_encode(["status" => true, "msg" => "Usuário Encontrado"]);
}else{
    echo json_encode(["status" => false, "msg" => "Usuário Nao Encontrado"]);
}
exit();