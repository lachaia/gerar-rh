<?php
//
//- rh_cv_exp_aj1.php | Recupera dados CURRÍCULO: EXPERIÊNCIA PROFISSIONAL para Edição/Visualização
//- (C)haia, 26/03/2025
//

session_start();

$idModulo = 7; // CURRICULUM

$parametros = filter_input_array(INPUT_POST, FILTER_DEFAULT);
if (isset($parametros)) extract($parametros);

if (empty($id)) {
    $retorno = [
        "status" => false,
        "msg" => '<div class="alert alert-danger">
            <strong>Erro!</strong> Faltou parâmetros!
            </div>'
    ];
    die(json_encode($retorno));
}

if (isset($_SESSION['idLogin'])) {
    $idLogin = $_SESSION['idLogin'];
    include_once "../includes/conexao_gerar.php";
    include_once "../includes/f_logs.php";
} else {
    header("location: logout.php");
}

//- Recupera dados do Órgão
//
$sql = "SELECT E.*, P.nome, DATE_FORMAT(L.dtLogin, '%d/%m/%Y %H:%i') AS dtLogin, U.login
            FROM rh_cv_exp E
            INNER JOIN rh_pessoas P ON P.idPessoa = E.idPessoa
            INNER JOIN rh_logins L on L.idLogin = E.idLogin
            INNER JOIN rh_usuarios U on U.idUsuario = L.idUsuario
                WHERE E.id = :id";
$consulta = $conn->prepare($sql);
$consulta->bindParam(':id', $id);
$consulta->execute();
$linha = $consulta->fetch(PDO::FETCH_ASSOC);
$nome = $linha['nome'];

if (isset($origem) && $origem == "visualizar") {
    $dados = implode( ", ", $linha);
    f_log("VIS", "VISUALIZAÇÃO de Experiência Profissional de $nome: Dados:( $dados )", "rh_cv_exp", $idModulo, $id);
}

$conn = null;
die(json_encode($linha, JSON_PRETTY_PRINT));
