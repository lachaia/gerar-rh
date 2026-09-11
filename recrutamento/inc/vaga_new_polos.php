<?php
//
//- vaga_new_polos.php | Cria Seletor de Polos para form vaga_new.php
//- (C)haia, 10/08/2026
//

session_start();

if (!isset($_SESSION['idLogin'])) {
  header("location: ../app/logout.php");
}

//- Inclui o arquivo de conexão com o banco de dados
include "../../app/includes/conexao_gerar.php";

$subsede_id = $_POST['subsede_id'];

$sql = "SELECT polo_id, identificador FROM rh_polos WHERE subsede_id = :subsede_id ORDER BY identificador";
$stmt = $conn->prepare($sql);
$stmt->bindParam(':subsede_id', $subsede_id);
$stmt->execute();
echo "
  <select name='polo_id' class='form-select' required>
    <option value='0' class='text-warning'>Selecione...</option>";
while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
  echo "<option value='{$row['polo_id']}'>{$row['identificador']}</option>";
}
echo "</select>";
?>