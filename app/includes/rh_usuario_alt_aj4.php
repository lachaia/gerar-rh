<?php
//
//- g_usuario_alt4_aj.php - Cria o Seletor COLABORADORES para a Modal em g_usuarios.js/g_usuario.php
//- (C) Chaia, 06/05/2025

session_start();

include_once "../includes/conexao_gerar.php";

$id = 0;
if ( isset($_POST['idColab'])){
    $id = $_POST['idColab'];
    if( empty($id)) $id=0;
}

$consulta = "SELECT C.idColab, P.nome
                FROM rh_colaboradores C
                INNER JOIN rh_pessoas P on P.idPessoa = C.idPessoa
                WHERE C.idRescisao is null
                ORDER BY P.nome";
                
$html = '<div class="input-group">';
$html .= "<select class='form-select' id='inputAltIDColab' name='inputAltIDColab'>";
try {
    $stmt = $conn->prepare($consulta);
    $stmt->execute();
    if ($stmt->rowCount() < 1) {
        $html .= "<option value='0'>Sem registros...</option>";
    } else {
        if ($id == 0) $selected = "selected"; else $selected = "";
        $html .= "<option value='0' $selected>Selecione uma pessoa...</option>";
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            extract( $row );
            if ($id == $idColab) $selected = "selected"; else $selected = "";
            $html .= "<option value='$idColab' $selected>$nome</option>";
        }
    }
} catch (PDOException $e) {
    $html .= "Erro: " . $e->getMessage();
}

echo $html;