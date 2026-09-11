<?php
//
//- rh_afastamento_aj4.php | EXCLUIR Registro do Afastamento
//- (C)haia, 05/08/2025
//

session_start();

$idModulo = 17; // Afastamentos

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

$sql = "SELECT A.*,  P.nome, T.descricao, U.login, C.idPessoa
                FROM rh_afastamentos A
                INNER JOIN rh_colaboradores C on C.idColab = A.idColab
                INNER JOIN rh_pessoas P on P.idPessoa = C.idPessoa
                INNER JOIN rh_afastamento_tipos T on T.id = A.idTipo
                LEFT OUTER JOIN rh_usuarios U on U.idUsuario = A.idUsuario
                WHERE A.id = :id";
$stmt = $conn->prepare($sql);
$stmt->execute(['id' => $id]);
$dados = $stmt->fetch(PDO::FETCH_ASSOC); // Obtém os dados como um array associativo

if ($dados) {
    // Cria uma string com os valores separados por vírgula
    $stringDados = implode(", ", $dados);
    $arquivo_old = $dados['arquivo'];
    $idPessoa = $dados['idPessoa'];
} else {
    $stringDados = "Nenhum dado encontrado.";
}

$sql = "DELETE FROM rh_afastamentos WHERE id = :id";
$stmt = $conn->prepare($sql);
$stmt->execute(['id' => $id]); 

// Executa a inserção
if ($stmt->execute()) {
    $msg = "<div class='alert alert-success'><strong>Successo!</strong> ao Excluir Registro</div>";
    $response = ["status" => true, "msg" => $msg ];
    //
    //- EXCLUI DOCUMENTO ANEXADO
    //
        $idTipoDoc = 27; //- Atestado Médico
        $sql = "DELETE FROM rh_documentos WHERE idPessoa=$idPessoa 
                AND idTipoDoc=$idTipoDoc AND arquivo = '$arquivo_old'";
        $stmt = $conn->prepare($sql);
        $stmt->execute();

        $url = "../docs/pessoa_$idPessoa/" . $arquivo_old;
        if (file_exists($url)) {
            unlink($url); // Apaga o arquivo
        }
    //
    //- EXCLUI LANÇAMENTO NA LINHA DO TEMPO
    //
        $sql = "DELETE FROM rh_pessoas_ldt WHERE origem='AFA' and idOrigem=$id";
        $stmt = $conn->prepare($sql);
        $stmt->execute();

} else {
    $msg = "<div class='alert alert-danger'><strong>Erro </strong> ao excluir registro!</div>";
    $response = ["status" => false, "msg" => $msg ];
}

f_log("EXC", "EXCLUSÃO de Afastamento no Sistema, ID: $id | Dados( $stringDados )", "rh_afastamentos", $idModulo, $id);

$conn = null;
die(json_encode($response));