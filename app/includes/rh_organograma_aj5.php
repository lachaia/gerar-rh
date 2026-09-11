<?php
//
//- rh_organograma_aj5.php | Salva Alteração de Órgão no Organograma
//- (C)haia, 25/02/2025
//

session_start();

$idModulo = 3; // Organograma

$parametros = filter_input_array(INPUT_POST, FILTER_DEFAULT);
if( isset($parametros)){
    extract( $parametros );
} else{
    $resposta = '<div class="alert alert-danger">
                <strong>Erro!</strong> Faltou parâmetros!
                </div>';
    die( json_encode($resposta) );
}

if( isset($_SESSION['idLogin'])){
    $idLogin = $_SESSION['idLogin'];
    include_once "../includes/conexao_gerar.php";
    include_once "../includes/f_logs.php";
} else{
    header("location: logout.php");
}

$sql = "SELECT * FROM rh_organograma WHERE idOrgao = :id";
$consulta = $conn->prepare($sql);
$consulta->bindParam(':id', $_id);
$consulta->execute();
$dados = $consulta->fetch(PDO::FETCH_ASSOC);
$old_dados = implode(", ", $dados);

$sql = "UPDATE rh_organograma SET 
            descricao = :descricao, 
            nivel = :nivel, 
            idSupervisor = :idSupervisor, 
            staff = :staff, 
            estrategico = :estrategico, 
            nivel_1 = :nivel1, 
            nivel_2 = :nivel2, 
            nivel_3 = :nivel3, 
            nivel_4 = :nivel4, 
            nivel_5 = :nivel5, 
            nivel_6 = :nivel6,
            nivel_7 = :nivel7
        WHERE idOrgao = :id"; 

$consulta = $conn->prepare($sql);
$consulta->bindParam(':id', $_id);
$consulta->bindParam(':descricao', $_nome);
$consulta->bindParam(':nivel', $_nivel);
$consulta->bindParam(':idSupervisor', $idSupervisor);
$consulta->bindParam(':staff', $_staff);
$consulta->bindParam(':estrategico', $_estrategico);
$consulta->bindParam(':nivel1', $_nivel1);
$consulta->bindParam(':nivel2', $_nivel2);
$consulta->bindParam(':nivel3', $_nivel3);
$consulta->bindParam(':nivel4', $_nivel4);
$consulta->bindParam(':nivel5', $_nivel5);
$consulta->bindParam(':nivel6', $_nivel6);
$consulta->bindParam(':nivel7', $_nivel7);

if( $consulta->execute() ){
    $resposta = '<div class="alert alert-success">
                <strong>Successo!</strong> Órgão Alterado com sucesso!
                </div>';
    $dados = implode(", ", $parametros);
    f_log("ALT", "ALTERAÇÃO de Órgão no Organograma: Dados Anteriores ( $old_dados ) | Dados Novos: ($dados)", "rh_organograma", $idModulo, $_id);
    //
} else{
    $resposta = '<div class="alert alert-danger">
                <strong>Erro!</strong> Falha ao Alterar dados do Órgão!
                </div>';
}

$conn = null;
die( $resposta );
