<?php
//
//- rh_pessoa_aj14.php | EXCLUI um ARQUIVO FOTO na tabela - rh_documentos
//- (C)haia, 07/03/2025
//

session_start();

include_once "../includes/debug.php";

$idModulo = 2; // Pessoas

$parametros = filter_input_array(INPUT_POST, FILTER_DEFAULT);

if (isset($parametros)) {
    extract($parametros);
} else {
    if( empty($idDoc) ){
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
    include_once "../includes/f_linha_do_tempo.php";
} else {
    header("location: logout.php");
}

//-- RECUPERA DADOS do Arquivo
//
$sql = "SELECT * FROM rh_documentos WHERE idDoc = $idDoc";
$stmt = $conn->prepare($sql);
$stmt = $conn->prepare($sql);
$stmt->execute();
$result = $stmt->fetch(PDO::FETCH_ASSOC);

$idPessoa = $result['idPessoa'];
$nome_original = $result['nome_original'];

$uploadDir = "../docs/pessoa_$idPessoa/"; //- diretório onde o arquivo está
$arquivo = $result['arquivo'];

// Caminho completo do arquivo
$filePath = $uploadDir . $arquivo;

// Verifica se o arquivo existe antes de tentar excluir
if (file_exists($filePath)) {
    // Deleta o arquivo
    if (unlink($filePath)) {
        //
        //- EXCLUIR DO BANCO
        //
        $sql = "DELETE FROM rh_documentos WHERE idDoc = $idDoc";
        $stmt = $conn->prepare($sql);
        if( $stmt->execute() ){
            $resposta = [
                'msg' => '<div class="alert alert-success">
                                <strong>Sucesso!</strong> ao Excluir Registro
                                </div>',
                'status' => true
            ];
            f_log("EXC", "EXCLUSÃO de FOTO - Dados( $nome_original )", "rh_documentos", $idModulo, $idDoc);
            //
            $tipo = 29; // Excluido 
            $descricao = "Foto ($nome_original) Excluída";
            f_ldt( $tipo, $idPessoa, $descricao);
            //
        } else{
            $resposta = [
                'msg' => '<div class="alert alert-danger">
                                <strong>Erro!</strong> Falha ao Excluir Registro!
                                </div>',
                'status' => false
            ];
        }

    } else {
        $resposta = [
            'msg' => '<div class="alert alert-danger">
                            <strong>Erro!</strong> ERRO ao Excluir Registro!
                            </div>',
            'status' => false
        ];
    }
} else {
    $resposta = [
        'msg' => '<div class="alert alert-danger">
                        <strong>Erro!</strong> Arquivo não encontrado!
                        </div>',
        'status' => false
    ];
}


$conn = null;
die(json_encode($resposta));