<?PHP
// rh_colab_aj6.php - Devolve Seletor tipo de endereços
// (C)haia, 09/04/2025

session_start();

$idModulo = 4; // colaboradores

include "conexao_gerar.php";

$sql = "SELECT * FROM rh_enderecos_tipo";
$stmt = $conn->prepare($sql);
$stmt->execute();
    
$select = "<select class='form-select fs-13 obrigatorio' id='idTipoEndereco' name='idTipoEndereco'>";
$select .= "<option value='0' selected>Selecione</option>";

while ($linha = $stmt->fetch(PDO::FETCH_ASSOC)) {
    extract($linha);
    $select .= "<option value='$idTipoEndereco'>$dsTipoEndereco</option>";
}
$select .= "</select>";
echo $select;
