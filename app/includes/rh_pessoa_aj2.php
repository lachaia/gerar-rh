<?php
//
//- rh_pessoa_aj2.php | Insere Nova PESSOA na tabela - ENDEREÇOS
//- (C)haia, 26/02/2025
//

session_start();

//include_once "../includes/debug.php";

$idModulo = 2; // Pessoas

$parametros = filter_input_array(INPUT_POST, FILTER_DEFAULT);

// Converte as strings para arrays associativos
if (!empty($parametros['dados_1'])) {
    parse_str($parametros['dados_1'], $dados_1);
    extract($dados_1); // Agora você pode acessar diretamente as variáveis de dados_1
}

if (!empty($parametros['dados_2'])) {
    parse_str($parametros['dados_2'], $dados_2);
    extract($dados_2); // Agora você pode acessar diretamente as variáveis de dados_2
}

//
if (isset($_SESSION['idLogin'])) {
    $idLogin = $_SESSION['idLogin'];
    $idEmpresa = $_SESSION['idEmpresa'];
    //
    include_once "../includes/conexao_gerar.php";
    include_once "../includes/f_logs.php";
} else {
    header("location: logout.php");
}

    //debug( json_encode($dados_1, JSON_PRETTY_PRINT) );
    //debug( json_encode($dados_2, JSON_PRETTY_PRINT) );
    //$resposta = [ 'msg' => '<div class="alert alert-primary"><strong>OK!</strong> TESTADO!</div>' ];
    //die( json_encode($resposta, JSON_PRETTY_PRINT ));

$cpf = preg_replace("/\D/", "", $cpf); // Remove tudo que não for número
$cep = preg_replace("/\D/", "", $cep); // Remove tudo que não for número

//-- VERIFICA se já Existe CPF na base
//
$sql = "SELECT idPessoa FROM rh_pessoas WHERE cpf = :cpf";
$stmt = $conn->prepare($sql);
$stmt->bindParam(':cpf', $cpf, PDO::PARAM_STR);
$stmt->execute();
$result = $stmt->fetch(PDO::FETCH_ASSOC);
if ($result) $idPessoa = $result['idPessoa'];
else $idPessoa = 0;

//if( empty( $idPessoa )){
//    $resposta = [ 'msg' => '<div class="alert alert-danger"><strong>NOT OK!</strong> eiiiiiiiiiiiita!</div>' ];
//    die( json_encode($resposta, JSON_PRETTY_PRINT ));
//}

//-- INSERE NA BASE
//
if ($idPessoa == 0) {
    //--- Insere Pessoa
    $sql = "INSERT INTO `rh_pessoas` 
                ( `idEmpresa`, `nome`, `nomeSocial`, `cpf`, `telefone`, `email`, `sexo`, `dtNascimento`, `idEstadoCivil`, 
                    `nacionalidade`, `rg`, `titulo_eleitor`, `camiseta`, `idEtnia`, `ativo`, `idLogin` )
            VALUES (:idEmpresa, :nome, :nomeSocial, :cpf, :telefone, :email, :sexo, :dataNascimento, :idEstadoCivil,  
                :nacionalidade, :rg, :tituloEleitor, :tamanhoCamiseta, :idEtnia, 1, :idLogin );";
    $stmt = $conn->prepare($sql);
    //
    $stmt->bindParam(':idEmpresa',  $idEmpresa);
    $stmt->bindParam(':idLogin',    $idLogin);
    $stmt->bindParam(':nome',       $nome);
    $stmt->bindParam(':nomeSocial', $nomeSocial);
    $stmt->bindParam(':cpf',        $cpf);
    $stmt->bindParam(':telefone',   $telefone);
    $stmt->bindParam(':email',      $email);
    $stmt->bindParam(':sexo',       $sexo);
    $stmt->bindParam(':dataNascimento',  $dataNascimento);
    $stmt->bindParam(':idEstadoCivil',   $idEstadoCivil);
    $stmt->bindParam(':nacionalidade',   $nacionalidade);
    $stmt->bindParam(':rg',              $rg);
    $stmt->bindParam(':tituloEleitor',   $tituloEleitor);
    $stmt->bindParam(':tamanhoCamiseta', $tamanhoCamiseta);
    $stmt->bindParam(':idEtnia',         $idEtnia);
    //
    if ($stmt->execute()) {
        $idPessoa = $conn->lastInsertId();
        //
        f_log("INC", "INCLUSÃO de Pessoa no Sistema: Dados( $dados_1 )", "rh_pessoas", $idModulo, $idPessoa);
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

//-- Insere Endereço

    $sql = "INSERT INTO rh_enderecos ( idEmpresa, idPessoa, idTipoEndereco, cep, logradouro, numero, complemento, bairro, cidade, uf, idLogin
            ) VALUES (
                :idEmpresa, :idPessoa, :idTipoEndereco, :cep, :logradouro, :numero, :complemento, :bairro, :cidade, :uf, :idLogin
            )";

    $stmt = $conn->prepare($sql);
    //
    $stmt->bindParam(':idEmpresa', $idEmpresa, PDO::PARAM_INT);
    $stmt->bindParam(':idPessoa', $idPessoa, PDO::PARAM_INT);
    $stmt->bindParam(':idLogin', $idLogin, PDO::PARAM_INT);
    $stmt->bindParam(':idTipoEndereco', $idTipoEndereco, PDO::PARAM_INT);
    $stmt->bindParam(':cep', $cep, PDO::PARAM_STR);
    $stmt->bindParam(':logradouro', $logradouro, PDO::PARAM_STR);
    $stmt->bindParam(':numero', $numero, PDO::PARAM_STR);
    $stmt->bindParam(':complemento', $complemento, PDO::PARAM_STR);
    $stmt->bindParam(':bairro', $bairro, PDO::PARAM_STR);
    $stmt->bindParam(':cidade', $cidade, PDO::PARAM_STR);
    $stmt->bindParam(':uf', $uf, PDO::PARAM_STR);
    //
    if (! $stmt->execute()) {
        $resposta = [
            'msg' => '<div class="alert alert-danger">
                            <strong>Erro!</strong> Falha ao inserir Endereço!
                            </div>',
            'status' => false
        ];
        //
        $idEndereco = $conn->lastInsertId();
        f_log("INC", "INCLUSÃO de Endereço: Dados( $dados )", "rh_enderecos", $idModulo, $idEndereco);
        //
        $tipo = 10; // Inserido Endereço
        $descricao = "Endereço incluído no Sistema";
        f_ldt( $tipo, $idPessoa, $descricao);
        //
        $conn = null;
        die(json_encode($resposta));
    }

//-- retorna vetor Endereço
//

$idEndereco = $conn->lastInsertId();

$retorno = [
    "status" => true,
    "msg" => '<div class="alert alert-success">
                <strong>Successo!</strong> Pessoa inserida com sucesso!
                </div>',
    'dados' => $dados_1,
    'idEndereco' => $idEndereco,
    'idPessoa'=> $idPessoa
];

$conn = null;
die(json_encode($retorno, JSON_PRETTY_PRINT));
