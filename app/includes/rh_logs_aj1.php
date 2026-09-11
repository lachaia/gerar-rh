<?php
//
//- rh_logs_aj1.php | Cria Seletor Usuários
// (C)haia, 11/02/2025

session_start();

$idEmpresa = $_SESSION["idEmpresa"];

include "conexao_gerar.php";

$sql = "SELECT distinct C.login, C.idUsuario FROM rh_logs A
            INNER JOIN rh_logins B ON B.idLogin = A.idLogin
            INNER JOIN rh_usuarios C on C.idUsuario = B.idUsuario
            ORDER BY C.login";
$stmt = $conn->prepare($sql);
$stmt->execute();

//
// CAMPOS de até
//

$html = "<div class='d-flex align-items-center gap-2'>";

$html .= "<label for='dtFrom' class='mb-0'>De</label>"; 
$html .= "<input type='date' class='form-control form-control-sm' id='dtFrom' onBlur='selecionou()'>";

$html .= "<label for='dtTo' class='mb-0'>até</label>"; 
$html .= "<input type='date' class='form-control form-control-sm' id='dtTo' onBlur='selecionou()'>"; 

$html .= "<select name='idUsuario' id='idUsuario' class='form-select form-control-sm fs-13' onChange='selecionou()'>";
$html .= "<option value='0' selected>Qual usuário?</option>";

while( $linha = $stmt->fetch(PDO::FETCH_ASSOC) ){
    extract( $linha );
    $html .= "<option value='$idUsuario'>$login</option>";
}

$html .= "</select>";
$html .= "</div>"; // Fecha a div flexível

$conn = null;
die( $html );
