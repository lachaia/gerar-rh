<?php
//
//- g_usuario_alt2_aj.php - Cria o Seletor Grupo De Usuário para a Modal em g_usuarios.js/g_usuario.php
//- (C) Chaia, 22/08/2023

session_start();

$idModulo  = 1; // rh_usuarios.php

$idEmpresa = $_SESSION['idEmpresa'];

include_once "../includes/conexao_gerar.php";

$id = $_POST['id'];
$consulta = "SELECT idUsuarioGrupo, descricao 
                FROM rh_usuariosgrupo 
                WHERE ativo=1 AND idEmpresa=$idEmpresa
                ORDER BY descricao";
$html =  "<select class='form-select danger' id='inputAltIdUsuarioGrupo' name='inputAltIdUsuarioGrupo' style='border-color: red'>";
try {
    $stmt = $conn->prepare($consulta);
    $stmt->execute();
    if ($stmt->rowCount() < 1) {
        $html .= "<option value='0'>Sem registros...</option>";
    } else {
        $html .=  "<option value='0'>Selecione um Grupo...</option>";
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $idUsuarioGrupo = $row['idUsuarioGrupo'];
            $descricao = $row['descricao'];
            if ($id == $idUsuarioGrupo)
            $html .=  "<option value='$idUsuarioGrupo' selected>$descricao</option>";
            else
            $html .=  "<option value='$idUsuarioGrupo'>$descricao</option>";
        }
    }
} catch (PDOException $e) {
    echo "Erro: " . $e->getMessage();
}
$html .=  "</select>";
$conn = null;
echo $html;
