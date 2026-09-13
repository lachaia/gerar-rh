<?php
//
//- index_aj15.php | Salva alteração de Status na Grid dos Termos
//- (C)haia, 06/04/2026
//

session_start();

if( isset($_SESSION['idLogin']) && in_array((int) ($_SESSION['idGrupo'] ?? 0), [4, 7, 9], true) ){
    $idLogin = $_SESSION['idLogin'];
    include_once "../includes/conexao_gerar.php";
    include_once "../includes/f_logs.php";
} else{
    header("location: logout.php");
    exit;
}

$dados = filter_input_array(INPUT_POST, FILTER_DEFAULT);

if( $dados ){
    extract($dados);
    // reafirma identidade da sessão depois do extract() — POST não deve conseguir sobrescrever
    $idLogin = $_SESSION['idLogin'];
    // Nota: Certifique-se que $id e $status existem após o extract
} else {
    die(json_encode(["status" => false, "msg" => "Faltaram Parâmetros!"]));
}

// Preparando a query com PDO para maior segurança
try {
    $sql = "UPDATE rh_equip_termos SET status = :status WHERE id = :id";
    $stmt = $conn->prepare($sql);
    
    // Vinculando os parâmetros
    $stmt->bindParam(':status', $status);
    $stmt->bindParam(':id', $id);

    if ($stmt->execute()) {
        $retorno = [
            "status" => true,
            "msg" => "<div class='alert alert-success text-center'>Alterado com sucesso!</div>"
        ];
    } else {
        $retorno = [
            "status" => false,
            "msg" => "<div class='alert alert-danger'>Erro na execução da atualização!</div>"
        ];
    }
} catch (PDOException $e) {
    $retorno = [
        "status" => false,
        "msg" => "<div class='alert alert-danger'>Erro no banco de dados: " . $e->getMessage() . "</div>"
    ];
}

echo json_encode($retorno);