<?php
//
//- rh_ctr_exp_aj4.php | EXCLUIR Registro do Contrato de Experiência
//- (C)haia, 23/09/2025
//

session_start();

$idModulo = 20; // Contratos de Experiência

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
    include_once "f_erros.php";
    //
} else {
    header("Location: ../logout.php");
}

$sql = "SELECT C.*, P.nome, P.idPessoa 
        FROM rh_ctr_exp C 
        INNER JOIN rh_colaboradores X ON X.idColab = C.idColab
        INNER JOIN rh_pessoas P ON P.idPessoa = X.idPessoa
        WHERE id = :id";
$stmt = $conn->prepare($sql);
$stmt->execute(['id' => $id]); 
$dados = $stmt->fetch(PDO::FETCH_ASSOC); // Obtém os dados como um array associativo

if ($dados) {
    // Cria uma string com os valores separados por vírgula
    $stringDados = implode(", ", $dados);
} else {
    $stringDados = "Nenhum dado encontrado.";
}

$sql = "DELETE FROM rh_ctr_exp WHERE id = $id";
$stmt = $conn->prepare($sql);

// Executa a inserção
if ($stmt->execute()) {
    $msg = "<div class='alert alert-success'><strong>Successo!</strong> ao Excluir Registro</div>";
    $response = ["status" => true, "msg" => $msg ];

    //
    //- EXCLUI O ARQUIVO SE EXISTIR
    //
        $arquivo = $dados['arquivo'];
        $idPessoa = $dados['idPessoa'];
        $diretorio = "../docs/pessoa_$idPessoa";
        $destino   = $diretorio . "/" . $arquivo;

        if (is_file($destino)) {
            if (!unlink($destino)) {
                // opcional: trate erro
                f_erro( "Erro ao excluir arquivo: $destino" );
            } else{
            //
            //- EXCLUI Registro em rh_documentos
            //
                $sql = "DELETE 
                        FROM rh_documentos
                        WHERE idPessoa = $idPessoa
                        AND arquivo = '$arquivo'";
                $stmt = $conn->prepare($sql);
                $stmt->execute();
            }
        }

} else {
    $msg = "<div class='alert alert-danger'><strong>Erro </strong> ao excluir registro!</div>";
    $response = ["status" => false, "msg" => $msg ];
}

f_log("EXC", "EXCLUSÃO de Contrato de Experiência, ID: $id | Dados( $stringDados )", "rh_ctr_exp", $idModulo, $id);

$conn = null;
die(json_encode($response));