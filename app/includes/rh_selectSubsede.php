<?php
//
//- selectSubsede.php | Cria o SELECT "subsedes"
//- (C) 2024-02-06 by Chaia

session_start();

include_once "../includes/conexao_gerar.php";

$parametros = filter_input_array(INPUT_POST, FILTER_DEFAULT);
if( isset($parametros)) extract( $parametros );

if( ! isset($idSubSede)){
    $idSubSede = 0;
}

//
//- ASSUNTOS
//
    $sql = "SELECT subsede_id as id, identificador as dsSubSede
                FROM rh_subsedes
                ORDER BY subsede_id";
    $stmt = $conn->prepare($sql);
    $stmt->execute();

    $select = "<select class='form-select fs-13' id='idSubSede' name='idSubSede'>";
    if( $idSubSede == 0) $select .= "<option value='0' selected>Selecione a subsede</option>";
    
    while ($linha = $stmt->fetch(PDO::FETCH_ASSOC)) {
        extract( $linha );
        if( $idSubSede == $id ){ 
            $select .= "<option value='$id' selected>$dsSubSede</option>";
        } else{
            $select .= "<option value='$id'>$dsSubSede</option>";
        }
        
    }
    $select .= "</select>";

echo $select;
$conn = null;