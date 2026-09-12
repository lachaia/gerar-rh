<?php
// rh_cipa_aj24.php | Cria Seletor Cipeiros
// by (C)haia, 10/07/2025
//

$idModulo = 11; // CIPA de Emergência

session_start();

if( isset($_SESSION['idLogin']) && (!empty($_SESSION['dcCIPA']) || (int) ($_SESSION['idGrupo'] ?? 0) === 9) ){
    $idLogin = $_SESSION['idLogin'];
    include_once "../includes/conexao_gerar.php";
    include_once "../includes/f_logs.php";
} else{
    header("location: ../logout.php");
    exit();
}
    $consulta = "SELECT B.id, P.nome
                    FROM rh_cipeiros B
                    INNER JOIN rh_pessoas P ON P.idPessoa = B.idPessoa
                    WHERE data_final is null ORDER BY P.nome";

    $stmt = $conn->prepare($consulta);
    $stmt->execute();
    $html = "<select class='form-select obrigatorio' id='idMembro' name='idMembro'>";
    $html .= "<option value='0'>Selecione um Cipeiro...</option>";
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $html .= "<option value='{$row['id']}'>{$row['nome']}</option>";
    }
    $html .= "</select>";
    $conn = null;
    die( $html );