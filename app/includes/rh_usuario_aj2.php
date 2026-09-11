<?php
//-----
// rh_usuario_aj2.php | Gera select dos grupos
// (C)haia, 18/02/2025

session_start();

$idModulo  = 1; // rh_usuarios.php

$idEmpresa = $_SESSION['idEmpresa'];

$dados = filter_input_array(INPUT_POST, FILTER_DEFAULT);

if( $dados ) extract($dados);

include_once "conexao_gerar.php";

$consulta = "SELECT idUsuarioGrupo, descricao 
                FROM rh_usuariosgrupo 
                WHERE idEmpresa = $idEmpresa AND ativo=1 
                ORDER BY descricao";
$stmt = $conn->prepare($consulta);
$stmt->execute();

$html = '<div class="input-group">';
$html .= "<select class='form-select obrigatorio' id='inputIdUsuarioGrupo' name='inputIdUsuarioGrupo'>";
$html .= "<option value='0'>Selecione um Grupo...</option>";

while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
    extract($row);
    if( $idGrupo == $idUsuarioGrupo ) $selected = "selected"; else $selected = "";
    $html .= "<option value='$idUsuarioGrupo' $selected>$descricao</option>";
}
$html .= "</select>";
$html .= '<a href="#" class="btn btn-outline-primary" onClick="f_incluiGrupo()"><i class="fa-solid fa-plus"></i></a>';
$html .= '</div>';

$conn = null;
echo $html;