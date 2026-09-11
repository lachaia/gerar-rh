<?php
//
//- rh_pessoa_aj8.php | Insere Nova PESSOA na tabela + CONTATO DE EMERGÊNCIA
//- (C)haia, 06/03/2025
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
/*
    debug( json_encode($dados_1, JSON_PRETTY_PRINT) );
    debug( json_encode($dados_2, JSON_PRETTY_PRINT) );
    $resposta = [ 'msg' => '<div class="alert alert-primary"><strong>OK!</strong> TESTADO!</div>' ];
    die( json_encode($resposta, JSON_PRETTY_PRINT ));

 rh_pessoa_aj8.php | 2025-03-06 17:41:34 
{
    "emg_nome": "ANNA CAROLINE CHAIA",
    "emg_grau": "FILHA",
    "emg_telefone": "42 3622 6697",
    "emg_celular": "42 9 9101 8668",
    "emg_endereco": "AV RUBEM SIQUEIRA RIBAS, 371, AP 306, Trianon\r\n85012075 - Guarapuava - Pr. "
}

*/

$cpf = preg_replace("/\D/", "", $cpf); // Remove tudo que não for número

//-- VERIFICA se já Existe CPF na base
//
$sql = "SELECT idPessoa FROM rh_pessoas WHERE cpf = :cpf";
$stmt = $conn->prepare($sql);
$stmt->bindParam(':cpf', $cpf, PDO::PARAM_STR);
$stmt->execute();
$result = $stmt->fetch(PDO::FETCH_ASSOC);
if ($result) $idPessoa = $result['idPessoa'];
else $idPessoa = 0;

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
        f_log("INC", "INCLUSÃO de Pessoa: Dados( $dados_2 )", "rh_documentos", $idModulo, $idPessoa);
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

//-- Insere CONTATO DE EMERGÊNCIA

    $sql = "INSERT INTO rh_pessoas_emg ( idEmpresa, idPessoa, nome, grau, telefone, celular, endereco, ativo, idLogin)
            VALUES (:idEmpresa, :idPessoa, :nome, :grau, :telefone, :celular, :endereco, :ativo, :idLogin )";

    $stmt = $conn->prepare($sql);
    $ativo = 1;
    //
    $stmt->bindParam(':idEmpresa', $idEmpresa,    PDO::PARAM_INT);
    $stmt->bindParam(':idPessoa',  $idPessoa,     PDO::PARAM_INT);
    $stmt->bindParam(':nome',      $emg_nome,     PDO::PARAM_STR);
    $stmt->bindParam(':grau',      $emg_grau,     PDO::PARAM_STR);
    $stmt->bindParam(':telefone',  $emg_telefone, PDO::PARAM_STR);
    $stmt->bindParam(':celular',   $emg_celular,  PDO::PARAM_STR);
    $stmt->bindParam(':endereco',  $emg_endereco, PDO::PARAM_STR);
    $stmt->bindParam(':ativo',     $ativo,        PDO::PARAM_INT);
    $stmt->bindParam(':idLogin',   $idLogin,      PDO::PARAM_INT);
    //
    if (! $stmt->execute()) {
        $resposta = [
            'msg' => '<div class="alert alert-danger">
                            <strong>Erro!</strong> Falha ao inserir Contato!
                            </div>',
            'status' => false
        ];
        $idContato = $conn->lastInsertId();
        f_log("INC", "INCLUSÃO de Contato de Emergência: Dados( $dados_1 )", "rh_pessoas_emg", $idModulo, $idContato);
        //
        $tipo = 12; // Documento anexado
        $descricao = "Inserido Contato de Emergência";
        f_ldt( $tipo, $idPessoa, $descricao);
        //        
        $conn = null;
        die(json_encode($resposta));
    }

//-- retorna vetor Endereço
//

$idContato = $conn->lastInsertId();

$retorno = [
    "status" => true,
    "msg" => '<div class="alert alert-success">
                <strong>Successo!</strong> Contato inserido com sucesso!
                </div>',
    'dados' => $dados_1,
    'idContato' => $idContato,
    'idPessoa'=> $idPessoa
];

$conn = null;
die(json_encode($retorno, JSON_PRETTY_PRINT));
