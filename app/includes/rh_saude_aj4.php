<?php
//
//- rh_saude_aj4.php | EXCLUIR Registro do Exame
//- (C)haia, 24/04/2025
//

session_start();

$idModulo = 8; // Saude Ocupacional

$parametros = filter_input_array(INPUT_POST, FILTER_DEFAULT);
if( $parametros ) extract( $parametros);

extract($parametros);

// Validação básica
if (!isset($id )) {
    $msg = "<div class='alert alert-danger'><strong>Erro: </strong> Faltou Parâmetros!</div>";
    $response = ["status" => false, "msg" => $msg ];    
    die(json_encode($response));
}

if (isset($_SESSION['idLogin']) && !empty($_SESSION['idLogin'])) {
    $idLogin = $_SESSION['idLogin'];
    $idEmpresa = $_SESSION['idEmpresa'];
    require_once "conexao_gerar.php"; // Inclua sua conexão com o banco de dados
    include_once "f_logs.php";
    //
} else {
    header("Location: ../logout.php");
}

$sql = "SELECT E.*, T.nmExame as dsExame, P.idPessoa, P.nome, D.arquivo
            FROM rh_exames E
            INNER JOIN rh_exames_tipos T on T.idExameTipo = E.idExameTipo
            INNER JOIN rh_colaboradores C on C.idColab = E.idColab
            INNER JOIN rh_pessoas P on P.idPessoa = C.idPessoa
            LEFT OUTER JOIN rh_documentos D ON D.idDoc = E.idDoc 
            WHERE idExame = :id";
$stmt = $conn->prepare($sql);
$stmt->execute(['id' => $id]); // Substitua $idCargo pelo valor desejado
$dados = $stmt->fetch(PDO::FETCH_ASSOC); // Obtém os dados como um array associativo
$antigos = json_encode($dados);
extract( $dados);

$sql = "DELETE FROM rh_exames WHERE idExame = $id";
$stmt = $conn->prepare($sql);

// Executa a inserção
if ($stmt->execute()) {
    $msg = "<div class='alert alert-success'><strong>Successo!</strong> ao Excluir Registro</div>";
    $response = ["status" => true, "msg" => $msg ];
    echo(json_encode($response));
} else {
    $msg = "<div class='alert alert-danger'><strong>Erro </strong> ao excluir registro!</div>";
    $response = ["status" => false, "msg" => $msg ];
    echo(json_encode($response));
}

if( ! empty($idDoc) ){
    $sql = "DELETE FROM rh_documentos WHERE idDoc = $idDoc";
    $stmt = $conn->prepare($sql);
    $stmt->execute();
    //
    if( file_exists("../docs/pessoa_$idPessoa/$arquivo" ) ){
        unlink("../docs/pessoa_$idPessoa/$arquivo");
    }
}

f_log("EXC", "EXCLUSÃO de Exame no Sistema, ID: $id | Dados( $antigos )", "rh_exames", $idModulo, $id);

$conn = null;
exit;