<?php
// includes/rh_avaliacoes_aj4.php
// (C)haia, 09/09/2025

session_start();

require 'conexao_gerar.php';

header('Content-Type: application/json');

try {
    if (!isset($_POST['idTipo']) || !isset($_POST['ids'])) {
        throw new Exception("Dados incompletos.");
    }

    $idTipo = intval($_POST['idTipo']);
    $ids = $_POST['ids']; // vetor de idColab

    if (!is_array($ids) || count($ids) === 0) {
        throw new Exception("Nenhum colaborador informado.");
    }

    // supondo que você tenha $login ou $_SESSION["user"]
    $usuario = $_SESSION['nmLogin']; // ajuste para pegar o usuário logado
    $idLogin = $_SESSION['idLogin'];
    $agora = date("Y-m-d H:i:s");
    //
    //- SIMPLES 90º (SUPERVISOR AVALIA)
    if ($idTipo == 1) {
        //- pelo supervisor
        $sql = "INSERT INTO rh_avaliacoes (idColab, idTipo, criado_em, criado_por, idLogin, idColabSuper)
            VALUES (?, ?, ?, ?, ?, ?)";
        $stmt = $conn->prepare($sql);

        foreach ($ids as $idColab) {
            $idColabSuper = supervisor( $idColab );
            $stmt->execute([$idColab, $idTipo, $agora, $usuario, $idLogin, $idColabSuper]);
        }
        echo json_encode(["status" => true, "msg" => "Avaliações criadas."]);
    } elseif( $idTipo == 2){
        //- autovaliação
        $sql = "INSERT INTO rh_avaliacoes (idColab, idTipo, criado_em, criado_por, idLogin, idColabSuper)
            VALUES (?, ?, ?, ?, ?, ?)";
        $stmt = $conn->prepare($sql);

        foreach ($ids as $idColab) {
            $idColabSuper = supervisor( $idColab );
            $stmt->execute([$idColab, $idTipo, $agora, $usuario, $idLogin, $idColabSuper]);
        }
        echo json_encode(["status" => true, "msg" => "Avaliações criadas."]);
    } else{
        echo json_encode(["status" => false, "msg" => "Avaliações NÃO criadas."]);
    }

    
} catch (Exception $e) {
    echo json_encode(["status" => false, "msg" => $e->getMessage()]);
}
die();

function supervisor( $idColab ){
    global $conn;
    $sql = "SELECT (SELECT C2.idColab FROM rh_colaboradores C2 where C2.idOrgao = O.idSupervisor) as idColabSuper
        FROM rh_colaboradores C 
        LEFT OUTER JOIN rh_organograma O on O.idOrgao = C.idOrgao
        WHERE C.idColab = $idColab";
    $stmt = $conn->prepare($sql);
    $stmt->execute();
    $linha = $stmt->fetch(PDO::FETCH_ASSOC);
    return $linha['idColabSuper'];
}