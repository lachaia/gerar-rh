<?php
//
//- rh_organograma_aj1.php | Cria o SELECT Órgão Supervisor
//- (C)haia, 24/02/2025
//

session_start();

$idModulo = 3; // Organograma

if( isset($_SESSION['idLogin'])){
    $idLogin = $_SESSION['idLogin'];
    include_once "../includes/conexao_gerar.php";
} else{
    header("location: logout.php");
}

$parametros = filter_input_array(INPUT_POST, FILTER_DEFAULT);
if( isset($parametros)) extract( $parametros );

if( ! isset($idOrgao)){
    $_idOrgao = 0;
} else{
    $_idOrgao = $idOrgao;
}

//
//- ASSUNTOS
//
    $sql = "SELECT *
                FROM rh_organograma
                ORDER BY nivel_1, nivel_2, nivel_3, nivel_4, nivel_5, nivel_6";
    $stmt = $conn->prepare($sql);
    $stmt->execute();
    $select = '<label for="_idSupervisor" class="col-form-label">Órgão Supervisor</label>';
    $select .= "<select class='form-select fs-13' id='idSupervisor' name='idSupervisor' required onchange='selecionou_orgao(this)'>";
    if( $_idOrgao == 0) $select .= "<option value='0' selected>Selecione um Órgão</option>";
    
    while ($linha = $stmt->fetch(PDO::FETCH_ASSOC)) {
        extract( $linha );
        $margem = $nivel * 3;
        if( $_idOrgao == $idOrgao ) $selected = "selected"; else $selected = "";
        $select .= "<option value='$idOrgao' $selected>".str_repeat("&nbsp;", $margem )."$idOrgao-$descricao</option>";        
    }
    $select .= "</select>";

echo $select;
$conn = null;