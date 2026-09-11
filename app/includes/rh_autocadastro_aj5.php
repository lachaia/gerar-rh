<?PHP 
//
//- rh_autocadatro_aj4.php | Retorna dados do AutoCadastro para view na modal
// (C)haia, 27/10/2025;
//

session_start();

include_once "../includes/conexao_gerar.php";

$idModulo = 21; //-Autocadastro

$parametros = filter_input_array(INPUT_POST, FILTER_DEFAULT);
if( $parametros ) extract( $parametros);

$sql = "SELECT * FROM rh_autocadastro WHERE id = :id";
$stmt = $conn->prepare($sql);  
$stmt->bindParam(':id', $id, PDO::PARAM_STR);
$stmt->execute();
$dados = $stmt->fetch(PDO::FETCH_ASSOC);

echo json_encode($dados);