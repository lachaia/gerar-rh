<?php
//
//- g_usuario_alt4_aj.php - Cria o Seletor COLABORADORES para a Modal em g_usuarios.js/g_usuario.php
//- (C) Chaia, 06/05/2025

session_start();

include_once "../includes/conexao_gerar.php";

$id = 0;
if ( isset($_POST['idColab'])){
    $id = $_POST['idColab'];
}

$consulta = "SELECT C.idColab, P.nome
                FROM rh_colaboradores C
                INNER JOIN rh_pessoas P on P.idPessoa = C.idPessoa
                WHERE C.idRescisao is null
                ORDER BY P.nome";
echo '<div class="input-group">';
echo "<select class='form-select' id='inputIDColab' name='inputIDColab'>";
try {
    $stmt = $conn->prepare($consulta);
    $stmt->execute();
    if ($stmt->rowCount() < 1) {
        echo "<option value='0'>Sem registros...</option>";
    } else {
        echo "<option value='0'>Selecione uma pessoa...</option>";
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $idColab = $row['idColab'];
            $nome = $row['nome'];
            if ($id == $idColab)
                echo "<option value='$idColab' selected>$nome</option>";
            else
                echo "<option value='$idColab'>$nome</option>";
        }
    }
} catch (PDOException $e) {
    echo "Erro: " . $e->getMessage();
}
