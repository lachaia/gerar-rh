<?php
//
//- g_usuario_alt2_aj.php - Cria o Seletor Grupo De Usuário para a Modal em g_usuarios.js/g_usuario.php
//- (C) Chaia, 22/08/2023

session_start();
include_once "../includes/conexao_gerar.php";

// Garante que o ID seja um inteiro para evitar falhas na comparação
$id = 0;
if (isset($_POST['idPessoa']) && !empty($_POST['idPessoa'])) {
    $id = (int) $_POST['idPessoa']; 
}

$consulta = "SELECT idPessoa, nome FROM rh_pessoas ORDER BY nome";

$html = '<div class="input-group">';
// Removi a classe 'danger' e o style fixo para você controlar via JS se necessário
$html .= "<select class='form-select' id='inputAltIDPessoa' name='inputAltIDPessoa' style='border-color: red'>";

try {
    $stmt = $conn->prepare($consulta);
    $stmt->execute();
    
    if ($stmt->rowCount() < 1) {
        $html .=  "<option value='0'>Sem registros...</option>";
    } else {
        $html .=  "<option value='0'>Selecione uma pessoa...</option>";
        
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $idPessoa = (int) $row['idPessoa']; // Força inteiro aqui também
            $nome = $row['nome'];
            
            // Comparação limpa
            $selected = ($id === $idPessoa) ? "selected" : "";
            
            $html .=  "<option value='$idPessoa' $selected>$nome</option>";
        }
    }
} catch (PDOException $e) {
    $html .=  "<option value='0'>Erro: " . $e->getMessage() . "</option>";
}

$html .=  "</select>";
$html .=  '<span class="input-group-text"><a href="javascript:void(0)" onClick="f_incluiPessoa()"><i class="fas fa-user-plus"></i></a></span>';
$html .=  "</div>";

echo $html;

?>