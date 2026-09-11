<?php
//
//- rh_pessoa_aj3.php | EXCLUI novo ENDEREÇO na tabela - ENDEREÇOS
//- (C)haia, 03/03/2025
//

session_start();

include_once "../includes/debug.php";

$idModulo = 2; // Pessoas

$parametros = filter_input_array(INPUT_POST, FILTER_DEFAULT);

if (isset($parametros)) {
    extract($parametros);
} else {
    if( empty($idEndereco) ){
        $resposta = '<div class="alert alert-danger">
        <strong>Erro!</strong> Faltou parâmetros!
        </div>';
        die($resposta);
    }
}

if (isset($_SESSION['idLogin'])) {
    $idLogin = $_SESSION['idLogin'];
    $idEmpresa = $_SESSION['idEmpresa'];
    //
    include_once "../includes/conexao_gerar.php";
    include_once "../includes/f_logs.php";
} else {
    header("location: logout.php");
}

//
//- Recupera dados antes de deletar
//

    $sql = "SELECT * FROM rh_enderecos WHERE idEndereco = $idEndereco";
    $stmt = $conn->prepare($sql);
    if( $stmt->execute() ){
        $dados = $stmt->fetch(PDO::FETCH_ASSOC);
        $idPessoa = $dados['idPessoa'];
    }

$sql = "DELETE FROM rh_enderecos WHERE idEndereco = $idEndereco";
$stmt = $conn->prepare($sql);
if( $stmt->execute() ){
    $resposta = [
        'msg' => '<div class="alert alert-success">
                        <strong>Sucesso!</strong> ao Excluir Registro
                        </div>',
        'status' => true
    ];
    f_log("EXC", "EXCLUSÃO de Endereço: Dados( $dados )", "rh_enderecos", $idModulo, $idEndereco);
    //
    $tipo = 28; // Inserido Endereço
    $descricao = "Endereço Excluído no Sistema";
    f_ldt( $tipo, $idPessoa, $descricao);
    //
} else{
    $resposta = [
        'msg' => '<div class="alert alert-danger">
                        <strong>Erro!</strong> Falha ao Excluir Endereço!
                        </div>',
        'status' => false
    ];
}

$conn = null;
die(json_encode($resposta));