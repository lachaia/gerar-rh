<?php
//
//- index_aj5.php | Insere Novo COMPROVANTE DE ENDEREÇOS
//- (C)haia, 03/03/2025 | (U) 2025-05-16
//

session_start();

$idModulo = 13; // Colaborador

$parametros = filter_input_array(INPUT_POST, FILTER_DEFAULT);
if ($parametros) {
    extract($parametros);
} else {
    $resposta = [
        'msg' => '<div class="alert alert-danger"><strong>ERRO!</strong> Faltou parâmetros!</div>',
        'status' => false
    ];
    die(json_encode($resposta, JSON_PRETTY_PRINT));
}

if (empty($_FILES['ce_arquivo']['name'])) {
    $resposta = [
        'msg' => '<div class="alert alert-danger"><strong>ERRO!</strong> Faltou o Arquivo!</div>',
        'status' => false
    ];
    die(json_encode($resposta, JSON_PRETTY_PRINT));
}
/*
include_once "../includes/debug.php";
debug( json_encode($parametros, JSON_PRETTY_PRINT) );
$resposta = [ 'msg' => '<div class="alert alert-primary"><strong>OK!</strong> TESTADO!</div>' ];
die( json_encode($resposta, JSON_PRETTY_PRINT ));
*/
//
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

if (!empty($_FILES['ce_arquivo']['name'])) {
    $file = $_FILES['ce_arquivo'];

    // Obtendo informações do arquivo
    $fileName = basename($file['name']);
    $fileExt = pathinfo($fileName, PATHINFO_EXTENSION); // Obtém a extensão
    $fileTmpPath = $file['tmp_name'];
    $fileSize = $file['size'];
    $fileType = $file['type'];

    //-- NOVO nome de arquivo (padronizado)
    $d = date("YmdHis");
    $arquivo = "arq-$d.$fileExt";

    // Definindo o diretório de destino
    $uploadDir = "../docs/pessoa_$idPessoa/";

    // Criando o diretório se não existir
    if (!file_exists($uploadDir)) {
        mkdir($uploadDir, 0777, true);
    }

    // Definindo o caminho completo do arquivo
    $destPath = $uploadDir . $arquivo;

    // Movendo o arquivo para o diretório de destino
    if (move_uploaded_file($fileTmpPath, $destPath)) {
        $resposta = [
            'msg' => '<div class="alert alert-success"><strong>OK!</strong> Salvo com Sucesso!</div>',
            'status' => true
        ];
        

        // Atualiza o banco com o caminho do arquivo

        $idTipoDoc = 1; // Tipo: 1 --> Comprovante de Endereço
        $sql = "SELECT * FROM rh_docs_tipo WHERE idTipoDoc = $idTipoDoc";
        $stmt = $conn->prepare($sql);
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        if( empty($result['validade']) ) $meses = 120; else $meses = $result['validade']; // número de meses a adicionar à data
        
        $validade = new DateTime($ce_data); // Converter a string em um objeto DateTime
        if( $meses > 0) $validade->modify("+$meses months");
        $validade_str = $validade->format("Y-m-d"); // Converte para string no formato YYYY-MM-DD

        $status = 1; // Sobe como Válido (pois foi o próprio RH que subiu)

        $sql = "INSERT INTO rh_documentos (idEmpresa, idPessoa, idTipoDoc, data, data_validade, arquivo, extensao, tamanho, status, idLoginAprova, nome_original)
                    VALUES ( :idEmpresa, :idPessoa, :idTipoDoc, :data, :data_validade, :arquivo, :extensao, :tamanho, :status, :idLogin, :original )";
        $stmt = $conn->prepare($sql);
        $stmt->bindParam(':idEmpresa', $idEmpresa, PDO::PARAM_INT);
        $stmt->bindParam(':idPessoa', $idPessoa, PDO::PARAM_INT);
        $stmt->bindParam(':idTipoDoc', $idTipoDoc, PDO::PARAM_INT);
        $stmt->bindParam(':data', $ce_data, PDO::PARAM_STR);
        $stmt->bindParam(':data_validade', $validade_str, PDO::PARAM_STR);
        $stmt->bindParam(':arquivo', $arquivo, PDO::PARAM_STR);
        $stmt->bindParam(':extensao', $fileExt, PDO::PARAM_STR);
        $stmt->bindParam(':tamanho', $fileSize, PDO::PARAM_STR);
        $stmt->bindParam(':status', $status, PDO::PARAM_INT);
        $stmt->bindParam(':idLogin', $idLogin, PDO::PARAM_INT);
        $stmt->bindParam(':original', $fileName, PDO::PARAM_STR);
        $stmt->execute();
        $idDoc = $conn->lastInsertId();
        //
        $retorno = [
            "status" => true,
            "msg" => '<div class="alert alert-success">
                        <strong>Successo!</strong> Arquivo inserido com sucesso!
                        </div>',
            'original' => $fileName,
            'arquivo' => $arquivo,
            'idDoc' => $idDoc,
            'idPessoa' => $idPessoa
        ];
        f_log("INC", "INCLUSÃO de Documento: Comprovante de Endereço - Dados( $fileName)", "rh_pessoas", $idModulo, $idDoc);
        //
        $tipo = 14; // Documento anexado
        $descricao = "Inserido Comprovante de Endereço";
        f_ldt( $tipo, $idPessoa, $descricao);
        //
        $conn = null;
        die(json_encode($retorno, JSON_PRETTY_PRINT));
        //
    } else {
        echo "Erro ao mover o arquivo.";
    }
    $conn = null;
    die(json_encode($resposta, JSON_PRETTY_PRINT));
} else{
    $resposta = [
        'msg' => '<div class="alert alert-danger"><strong>ERRO!</strong> Faltou o Arquivo!</div>',
        'status' => false
    ];
    $conn = null;
    die(json_encode($resposta, JSON_PRETTY_PRINT));
}
