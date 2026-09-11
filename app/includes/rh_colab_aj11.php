<?php
//
//- rh_colab_aj11.php | Salva Novo DEPENDENTE de COLABORADOR
//- (C)haia, 14/04/2025
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
/*
rh_colab_aj11.php | 2025-04-17 09:11:20 
{
    "nmDependente": "Anna Caroline Chaia",
    "idPessoaDep": "63",
    "idParentesco": "0",
    "dataNascimento": "2001-10-22",
    "usaPlanoSaude": "1",
    "usaPlanoOdonto": "1",
    "usaCreche": "1",
    "ir": "1",
    "cpf": "04331383199",
    "idColab": "8"
}
*/

if ( empty($idPessoaDep) || empty($idParentesco) || empty($dataNascimento) || empty($idColab) || empty($cpf) ) {
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

$cpf_limpo = preg_replace('/\D+/', '', $cpf); // Resultado: 12345678909

//- Atualiza o CPF lá no rh_pessoas
//
    $sql = "UPDATE rh_pessoas SET cpf = :cpf WHERE idPessoa = :idPessoaDep";
    $stmt = $conn->prepare($sql);
    $stmt->bindValue(':idPessoaDep', $idPessoaDep, PDO::PARAM_INT);
    $stmt->bindValue(':cpf', $cpf_limpo, PDO::PARAM_STR);
    $stmt->execute();

//- INSERE NO BD
//
$sql = "INSERT INTO rh_dependentes ( idColab, idPessoaDep, idParentesco, dataNascimento, usaPlanoSaude, usaPlanoOdonto, usaCreche, ir, ativo, idLogin) 
                  values ( :idColab, :idPessoaDep, :idParentesco, :dataNascimento, :usaPlanoSaude, :usaPlanoOdonto, :usaCreche, :ir, :ativo, :idLogin)";
$stmt = $conn->prepare($sql);
$stmt->bindValue(':idColab', $idColab, PDO::PARAM_INT);
$stmt->bindValue(':idPessoaDep', $idPessoaDep, PDO::PARAM_INT);
$stmt->bindValue(':idParentesco', $idParentesco, PDO::PARAM_INT);
$stmt->bindValue(':dataNascimento', $dataNascimento, PDO::PARAM_STR);
$stmt->bindValue(':usaPlanoSaude', $usaPlanoSaude, PDO::PARAM_INT);
$stmt->bindValue(':usaPlanoOdonto', $usaPlanoOdonto, PDO::PARAM_INT);
$stmt->bindValue(':usaCreche', $usaCreche, PDO::PARAM_INT);
$stmt->bindValue(':ir', $ir, PDO::PARAM_INT);
$stmt->bindValue(':ativo', 1, PDO::PARAM_INT);
$stmt->bindValue(':idLogin', $idLogin,  PDO::PARAM_INT);

if( $stmt->execute() ) {
    $idDependente = $conn->lastInsertId();
    $retorno = [
        'status'=> true,
        "msg" => "<div class='alert alert-success'><strong>Successo!</strong> Dependente inserido com sucesso!.</div>"
    ];
    //
} else {
    $retorno = [
        'status'=> false,
        "msg" => "<div class='alert alert-danger'><strong>Erro!</strong> Não foi possível inserir o Dependente!.</div>"
    ];
}

// Busca a descrição do tipo de PARENTESCO
//
$sql = "SELECT D.*, PD.nome as nmDependente, C.idPessoa, PC.nome as nmColab, 
            TIMESTAMPDIFF(YEAR, D.dataNascimento, CURDATE()) AS idade, P.dsParentesco
        FROM rh_dependentes D
        INNER JOIN rh_pessoas PD on PD.idPessoa = D.idPessoaDep
        INNER JOIN rh_colaboradores C ON C.idColab = D.idColab
        INNER JOIN rh_pessoas PC on PC.idPessoa = C.idPessoa
        INNER JOIN rh_parentescos P on P.idParentesco = D.idParentesco
        WHERE idDependente = $idDependente";
$stmt = $conn->prepare($sql);
$stmt->execute();

$row = $stmt->fetch(PDO::FETCH_ASSOC);
extract($row);

$dados = [
    'idDependente' => $idDependente,
    'nome' => $nmDependente,
    'parentesco' => $dsParentesco,
    "idade" => $idade,
    "ir" => $ir,
    "saude"  => $usaPlanoSaude,
    "odonto" => $usaPlanoOdonto,
    "creche" => $usaCreche
];
$retorno['dados'] = $dados;

die( json_encode( $retorno, JSON_PRETTY_PRINT ) );
