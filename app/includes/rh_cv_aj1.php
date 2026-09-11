<?php
//
//- rh_cv_aj1.php | CURRÍCULO | Salva CV
//- (C)haia, 01/04/2025
//

session_start();

$idModulo = 7; // CURRICULUM

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
 rh_cv_aj1.php | 2025-05-19 16:53:38 
{
    "idPessoa": "1",
    "idCV": "1",
    "genero": "M",
    "deficiente": "1",
    "fisica": "1",
    "visual": "1",
    "auditiva": "1",
    "mental": "1",
    "intelectual": "1",
    "autista": "1",
    "cid": "1234",
    "linkedin": "https:\/\/www.linkedin.com\/in\/luiz-augusto-chaia-48772b20\/"
}
*/
if (empty($idCV) || empty($idPessoa) || empty($genero)) {
    $retorno = [
        "status" => false,
        "msg" => '<div class="alert alert-danger">
            <strong>Erro!</strong> Faltou parâmetros!
            </div>'
    ];
    die(json_encode($retorno));
}

if (! isset($deficiente)) $deficiente = 0;
if (! isset($fisica)) $fisica = 0;
if (! isset($visual)) $visual = 0;
if (! isset($auditiva)) $auditiva = 0;
if (! isset($mental)) $mental = 0;
if (! isset($intelectual)) $intelectual = 0;
if (! isset($autista)) $autista = 0;

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

$sql = "SELECT * FROM rh_cv WHERE idPessoa = $idPessoa";
$consulta = $conn->prepare($sql);
$consulta->execute();
$registro = $consulta->fetch(PDO::FETCH_ASSOC);
$idCV = $registro['idCV'];

if ($registro) {
    $dados_old = implode(", ", array_map('strval', $registro)); // Garante que todos sejam strings
    //echo $dados_old;
}

if ($registro) {
    // Se já existir, faz UPDATE
    $sql = "UPDATE rh_cv SET 
            ultimaAtualizacao = :ultimaAtualizacao, 
            genero = :genero, 
            deficiente = :deficiente, 
            def_fisica = :def_fisica, 
            def_visual = :def_visual, 
            def_auditiva = :def_auditiva, 
            def_mental = :def_mental, 
            def_intelectual = :def_intelectual, 
            def_autista = :def_autista, 
            cid = :cid, 
            linkedin = :linkedin
        WHERE idPessoa = :idPessoa";

    $stmt = $conn->prepare($sql);

    $stmt->bindParam(':ultimaAtualizacao', $agora);
    $stmt->bindParam(':genero', $genero);
    $stmt->bindParam(':deficiente', $deficiente);
    $stmt->bindParam(':def_fisica', $fisica);
    $stmt->bindParam(':def_visual', $visual);
    $stmt->bindParam(':def_auditiva', $auditiva);
    $stmt->bindParam(':def_mental', $mental);
    $stmt->bindParam(':def_intelectual', $intelectual);
    $stmt->bindParam(':def_autista', $autista);
    $stmt->bindParam(':cid', $cid);
    $stmt->bindParam(':linkedin', $linkedin);
    $stmt->bindParam(':idPessoa', $idPessoa);
    $stmt->execute();

    $msg = "<div class='alert alert-success'><strong>Successo!</strong> ao atualizar registro. ID: $idCV</div>";
    f_log("ALT", "ALTERAÇÃO no CV - Dados Pessoais Dados Anteriores: ( $dados_old ) para: ($dados_novos)", "rh_cv", $idModulo, $idCV);
} 
$response = ["status" => false, "msg" => $msg];

$conn = null;
die(json_encode($response));
