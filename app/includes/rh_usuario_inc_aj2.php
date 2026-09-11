<?php
//
//- g_usuario_alt2_aj.php - Cria o Seletor Grupo De Usuário para a Modal em g_usuarios.js/g_usuario.php
//- (C) Chaia, 22/08/2023

session_start();

include_once "../includes/conexao_gerar.php";

$id = 0;
if ( isset($_POST['idPessoa'])){
    $id = $_POST['idPessoa'];
}

$consulta = "SELECT idPessoa, nome FROM rh_pessoas WHERE ativo=1 ORDER BY nome";
echo '<div class="input-group">';
echo "<select class='form-select danger' id='inputIDPessoa' name='inputIDPessoa' style='border-color: red'>";
try {
    $stmt = $conn->prepare($consulta);
    $stmt->execute();
    if ($stmt->rowCount() < 1) {
        echo "<option value='0'>Sem registros...</option>";
    } else {
        echo "<option value='0'>Selecione uma pessoa...</option>";
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $idPessoa = $row['idPessoa'];
            $nome = $row['nome'];
            if ($id == $idPessoa)
                echo "<option value='$idPessoa' selected>$nome</option>";
            else
                echo "<option value='$idPessoa'>$nome</option>";
        }
    }
} catch (PDOException $e) {
    echo "Erro: " . $e->getMessage();
}
echo "</select>";
echo '<span class="input-group-text"><a href="#" onClick="f_incluiPessoa()"><i class="fas fa-user-plus"></i></a></span>';
echo "</div>";
