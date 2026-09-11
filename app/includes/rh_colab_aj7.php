<?php
//
//- rh_colab_aj7.php | Salva Novo ENDEREÇO de COLABORADOR
//- (C)haia, 09/04/2025
//

session_start();

$idModulo = 4; // colaboradores

$parametros = filter_input_array(INPUT_POST, FILTER_DEFAULT);
if (isset($parametros)) {
    extract($parametros);
    $dados_novos = implode(", ", array_map('strval', $parametros)); // Garante que todos sejam strings
}

/*
// TESTE DE RECEBIMENTO DE DADOS
include "debug.php";
debug( json_encode($parametros, JSON_PRETTY_PRINT) );
$retorno = [
    'status'=> true,
    "msg" => "<div class='alert alert-success'><strong>Successo!</strong> THE TEST WAS OK!.</div>"
];
die( json_encode( $retorno, JSON_PRETTY_PRINT ) );

rh_colab_aj7.php | 2025-04-09 14:28:42 
{
    "idTipoEndereco": "2",
    "cep": "81310-000",
    "logradouro": "Rua Senador Accioly Filho",
    "numero": "511",
    "complemento": "",
    "bairro": "Cidade Industrial",
    "cidade": "Curitiba",
    "uf": "PR",
    "idPessoa": "1"
}
*/

if ( empty($idColab) || empty($idPessoa) || empty($idTipoEndereco) || empty($logradouro) || empty($numero) || 
        empty($bairro) || empty($cidade) || empty($uf) ) {
    $retorno = [
        "status" => false,
        "msg" => '<div class="alert alert-danger">
            <strong>Erro!</strong> Faltou parâmetros!
            </div>'
    ];
    die(json_encode($retorno));
}

if (isset($_SESSION['idLogin'])) {
    $idLogin = $_SESSION['idLogin'];
    $idEmpresa = $_SESSION['idEmpresa'];
    $agora = date("Y-m-d H:i:s");
    //
    include_once "../includes/conexao_gerar.php";
    include_once "../includes/f_logs.php";
} else {
    header("location: logout.php");
}
$cep = preg_replace('/\D/', '', $cep);

$sql = "INSERT INTO rh_enderecos ( idEmpresa, idPessoa, idTipoEndereco, logradouro, numero, complemento, cep, bairro, cidade, uf, idLogin) 
            values ( :idEmpresa, :idPessoa, :idTipoEndereco, :logradouro, :numero, :complemento, :cep, :bairro, :cidade, :uf, :idLogin )";
$stmt = $conn->prepare($sql);
$stmt->bindValue(':idEmpresa', $idEmpresa, PDO::PARAM_INT);
$stmt->bindValue(':idPessoa', $idPessoa, PDO::PARAM_INT);
$stmt->bindValue(':idTipoEndereco', $idTipoEndereco, PDO::PARAM_INT);
$stmt->bindValue(':idLogin', $idLogin, PDO::PARAM_INT);
$stmt->bindValue(':cep', $cep, PDO::PARAM_STR);
$stmt->bindValue(':logradouro', $logradouro, PDO::PARAM_STR);
$stmt->bindValue(':numero', $numero, PDO::PARAM_STR);
$stmt->bindValue(':complemento', $complemento,  PDO::PARAM_STR);
$stmt->bindValue(':bairro', $bairro, PDO::PARAM_STR);
$stmt->bindValue(':cidade', $cidade, PDO::PARAM_STR);
$stmt->bindValue(':uf', $uf, PDO::PARAM_STR);

if( $stmt->execute() ) {
    $idEndereco = $conn->lastInsertId();
    $retorno = [
        'status'=> true,
        "msg" => "<div class='alert alert-success'><strong>Successo!</strong> Endereço inserido com sucesso!.</div>"
    ];
    //- Atualiza Colaboradores
    //
        $sql = "UPDATE rh_colaboradores SET idEnderecoTrab = $idEndereco WHERE idColab = $idColab";
        $stmt = $conn->prepare($sql);
        $stmt->execute();
    //
} else {
    $retorno = [
        'status'=> false,
        "msg" => "<div class='alert alert-danger'><strong>Erro!</strong> Não foi possível inserir o Endereço!.</div>"
    ];
}

// Busca a descrição do tipo de endereço
//
$sql = "SELECT * FROM rh_enderecos_tipo WHERE idTipoEndereco = ?";
$stmt = $conn->prepare($sql);
$stmt->execute([$idTipoEndereco]);

$row = $stmt->fetch(PDO::FETCH_ASSOC);
$dsTipoEndereco = $row['dsTipoEndereco'] ?? '';

$dsEndereco = "$dsTipoEndereco: $logradouro, $numero, $complemento, $bairro, $cidade-$uf ";

$dados = [
    "idEndereco" => $idEndereco,
    "dsEndereco" => $dsEndereco
];
$retorno['dados'] = $dados;

die( json_encode( $retorno, JSON_PRETTY_PRINT ) );