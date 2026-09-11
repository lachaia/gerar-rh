<?PHP
//
// rh_cipa_aj28.php | gerar seletor de tipos de documentos
// (C)haia, 16/07/2025
//  

session_start();

$idModulo = 11; // CIPA

$dados = filter_input_array(INPUT_POST, FILTER_DEFAULT);
if ($dados) extract($dados);

include_once "../includes/conexao_gerar.php";

$consulta = "SELECT idTipoDoc, nome
                FROM rh_docs_tipo
                WHERE ativo = 1
                order by nome";
$stmt = $conn->prepare($consulta);
$stmt->execute();
$html = "<select class='form-select obrigatorio' id='idTipoDoc' name='idTipoDoc'>";
if( $id == 0) $selected = "selected"; else $selected = "";
$html .= "<option value='0' $selected>Selecione um tipo...</option>";
while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
    if($id == $row['idTipoDoc']) $selected = "selected"; else $selected = "";
    $html .= "<option value='{$row['idTipoDoc']}' $selected>{$row['nome']}</option>";
}
$html .= "</select>";
$conn = null;
die( $html );