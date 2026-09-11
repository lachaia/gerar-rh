<?php
//
//- rh_cargo_aj15.php | EXCLUI DDIR - Declaração de Dependentes para IR
//- (C)haia, 15/04/2025
//

session_start();

$idModulo = 4; // COLABORADORES

$parametros = filter_input_array(INPUT_POST, FILTER_DEFAULT);
if( $parametros ) extract( $parametros);

extract($parametros);

// Validação básica
if (!isset($idColab) || empty($idColab)) {
    $msg = "<div class='alert alert-danger'><strong>Erro: </strong> Faltou Parâmetros!</div>";
    $response = ["status" => false, "msg" => $msg ];    
    die(json_encode($response));
}

if (isset($_SESSION['idLogin']) && !empty($_SESSION['idLogin'])) {
    $idLogin = $_SESSION['idLogin'];
    $idEmpresa = $_SESSION['idEmpresa'];
    require_once "conexao_gerar.php"; // Inclua sua conexão com o banco de dados
    include_once "f_linha_do_tempo.php"; // Inclua sua função de linha do tempo
    include_once "f_logs.php";
    //
} else {
    header("Location: ../logout.php");
}

$sql = "SELECT arquivo_ddir, idPessoa
            FROM rh_colaboradores  
            WHERE idColab = $idColab";
$stmt = $conn->prepare($sql);
$stmt->execute();

$dados = $stmt->fetch(PDO::FETCH_ASSOC); // Obtém os dados como um array associativo
$arquivo_ddir = $dados['arquivo_ddir'];
$idPessoa = $dados['idPessoa'];

if( ! empty($arquivo_ctps)){
    $caminho = "docs/pessoa_$idPessoa/$arquivo_ddir";
    if (file_exists($caminho)) {
        unlink($caminho); // Exclui o arquivo
    }
    //
    $sql = "UPDATE rh_colaboradores SET arquivo_ddir = NULL WHERE idColab = $idColab";
    $stmt = $conn->prepare($sql);  
    $stmt->execute();
    die("Arquivo excluído com sucesso!");
    //
    //- LINHA DO TEMPO
    //
    $tipo = 29; // DOCUMENTO EXCLUIDO
    $descricao = "Excluiu o arquivo DDIR (Declaração de Dependentes para IR)";
    f_ldt( $tipo, $idPessoa, $descricao);
} else{
    die("Arquivo não encontrado!");
}
