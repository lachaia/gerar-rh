<?php
//
//- rh_pessoa_aj7.php | INCLUI novo ARQUIVO - ANTECEDENTES CRIMINAIS (AA)
//- (C)haia, 06/03/2025
//

session_start();

include_once "../includes/debug.php";

$idModulo = 2; // Pessoas

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

if (empty($_FILES['aa_arquivo']['name'])) {
    $resposta = [
        'msg' => '<div class="alert alert-danger"><strong>ERRO!</strong> Faltou o Arquivo!</div>',
        'status' => false
    ];
    die(json_encode($resposta, JSON_PRETTY_PRINT));
}

/*
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

$cpf = preg_replace("/\D/", "", $cpf); // Remove tudo que não for número

//-- VERIFICA se já Existe CPF na base
//
if (empty($idPessoa)) {
    $sql = "SELECT idPessoa FROM rh_pessoas WHERE cpf = :cpf";
    $stmt = $conn->prepare($sql);
    $stmt->bindParam(':cpf', $cpf, PDO::PARAM_STR);
    $stmt->execute();
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    if ($result) $idPessoa = $result['idPessoa'];
    else $idPessoa = 0;
}

//-- INSERE NA BASE
//
if ($idPessoa == 0) {
    //--- Insere Pessoa
    $sql = "INSERT INTO `rh_pessoas` 
                ( `idEmpresa`, `nome`, `nomeSocial`, `cpf`, `telefone`, `email`, `sexo`, `dtNascimento`, `idEstadoCivil`, 
                    `nacionalidade`, `rg`, `titulo_eleitor`, `camiseta`, `idEtnia`, `dcAtivo`, `idLogin` )
            VALUES (:idEmpresa, :nome, :nomeSocial, :cpf, :telefone, :email, :sexo, :dataNascimento, :idEstadoCivil,  
                :nacionalidade, :rg, :tituloEleitor, :tamanhoCamiseta, :idEtnia, 1, :idLogin );";
    $stmt = $conn->prepare($sql);
    //
    $stmt->bindParam(':idEmpresa', $idEmpresa);
    $stmt->bindParam(':idLogin', $idLogin);
    $stmt->bindParam(':nome', $nome);
    $stmt->bindParam(':nomeSocial', $nomeSocial);
    $stmt->bindParam(':cpf', $cpf);
    $stmt->bindParam(':telefone', $telefone);
    $stmt->bindParam(':email', $email);
    $stmt->bindParam(':sexo', $sexo);
    $stmt->bindParam(':dataNascimento', $dataNascimento);
    $stmt->bindParam(':idEstadoCivil', $idEstadoCivil);
    $stmt->bindParam(':nacionalidade', $nacionalidade);
    $stmt->bindParam(':rg', $rg);
    $stmt->bindParam(':tituloEleitor', $tituloEleitor);
    $stmt->bindParam(':tamanhoCamiseta', $tamanhoCamiseta);
    $stmt->bindParam(':idEtnia', $idEtnia);
    //
    if ($stmt->execute()) {
        $idPessoa = $conn->lastInsertId();
        f_log("INC", "INCLUSÃO de Pessoa: Dados( $parametros )", "rh_documentos", $idModulo, $idPessoa);
        //
        $tipo = 1; // Dados Pessoais Incluídos
        $descricao = "$nome incluída no Sistema";
        f_ldt( $tipo, $idPessoa, $descricao);
        //        
    } else {
        $resposta = [
            'msg' => '<div class="alert alert-danger">
                            <strong>Erro!</strong> Falha ao inserir Pessoa!
                            </div>',
            'status' => false
        ];
        $conn = null;
        die(json_encode($resposta));
    }
}

// Verifica se um arquivo foi enviado
if (!empty($_FILES['aa_arquivo']['name'])) {
    $file = $_FILES['aa_arquivo'];

    // Obtendo informações do arquivo
    $fileName = basename($file['name']);
    $fileExt = pathinfo($fileName, PATHINFO_EXTENSION); // Obtém a extensão
    $fileTmpPath = $file['tmp_name'];
    $fileSize = $file['size'];
    $fileType = $file['type'];

    //-- NOVO nome de arquivo (padronizado)
    $d = date("YmdHis");
    $arquivo = "arq_aa-$d.$fileExt";

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

        $idTipoDoc = 2; // Tipo: 2 --> Atestado de Antecedentes Criminais
        $sql = "SELECT * FROM rh_docs_tipo WHERE idTipoDoc = $idTipoDoc";
        $stmt = $conn->prepare($sql);
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        if( empty($result['validade']) ) $meses = 120; else $meses = $result['validade']; // número de meses a adicionar à data
        
        $validade = new DateTime($aa_data); // Converter a string em um objeto DateTime
        if( $meses > 0) $validade->modify("+$meses months");
        $validade_str = $validade->format("Y-m-d"); // Converte para string no formato YYYY-MM-DD

        $status = 1; // Sobe como Válido (pois foi o próprio RH que subiu)

        $sql = "INSERT INTO rh_documentos (idEmpresa, idPessoa, idTipoDoc, data, data_validade, arquivo, extensao, tamanho, status, idLoginAprova, nome_original)
                    VALUES ( :idEmpresa, :idPessoa, :idTipoDoc, :data, :data_validade, :arquivo, :extensao, :tamanho, :status, :idLogin, :original )";
        $stmt = $conn->prepare($sql);
        $stmt->bindParam(':idEmpresa', $idEmpresa, PDO::PARAM_INT);
        $stmt->bindParam(':idPessoa', $idPessoa, PDO::PARAM_INT);
        $stmt->bindParam(':idTipoDoc', $idTipoDoc, PDO::PARAM_INT);
        $stmt->bindParam(':data', $aa_data, PDO::PARAM_STR);
        $stmt->bindParam(':data_validade', $validade_str, PDO::PARAM_STR);
        $stmt->bindParam(':arquivo', $arquivo, PDO::PARAM_STR);
        $stmt->bindParam(':extensao', $fileExt, PDO::PARAM_STR);
        $stmt->bindParam(':tamanho', $fileSize, PDO::PARAM_STR);
        $stmt->bindParam(':status', $status, PDO::PARAM_INT);
        $stmt->bindParam(':idLogin', $idLogin, PDO::PARAM_INT);
        $stmt->bindParam(':original', $fileName, PDO::PARAM_STR);
        $stmt->execute();
        $idDoc = $conn->lastInsertId();
        f_log("INC", "INCLUSÃO de Documento: Antecedentes Criminais - Dados( $fileName )", "rh_documentos", $idModulo, $idDoc);
        //
        $tipo = 14; // Documento anexado
        $descricao = "Inserido Doc Antecedentes Criminais";
        f_ldt( $tipo, $idPessoa, $descricao);
        //
        $retorno = [
            "status" => true,
            "msg" => '<div class="alert alert-success">
                        <strong>Successo!</strong> Arquivo inserido com sucesso!
                        </div>',
            'arquivo' => $fileName,
            'idDoc' => $idDoc,
            'idPessoa' => $idPessoa
        ];
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

