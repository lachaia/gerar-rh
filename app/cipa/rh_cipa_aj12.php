<?PHP
//
// rh_cipa_aj12.php | Exclui Registro de ATENDIMENTO de CIPA
// (C)haia, 24/06/2025
//

session_start();

$idModulo = 11; // CIPA

if (!isset($_SESSION['idLogin']) || (empty($_SESSION['dcCIPA']) && (int) ($_SESSION['idGrupo'] ?? 0) !== 9)) {
    header('location: ../logout.php');
    exit();
}else{
    include_once "../includes/conexao_gerar.php";
    include_once "../includes/f_logs.php";
    //
    $idLogin = $_SESSION['idLogin'];
    $nmLogin = $_SESSION['nmLogin'];
    $idEmpresa = $_SESSION['idEmpresa'];
}

$dados = filter_input_array(INPUT_POST, FILTER_DEFAULT);
if( $dados ){
    extract($dados);
} else{
    die(json_encode(["status" => false, "msg" => "Faltaram Parâmetros!"]));
}
/*
include_once "debug.php";
debug( json_encode($dados, JSON_PRETTY_PRINT));
$conn = null;
die(json_encode(["status" => false, "msg" => "<div class='alert alert-primary'><strong>Success!</strong>Teste de Sistema</div>"]));
*/

//
// - RECUPERA dados antes de excluir
//
$sql = "SELECT * FROM rh_cipa_atendimentos WHERE id = :id";
$stmt = $conn->prepare($sql);
$stmt->execute(['id' => $id]); // Substitua $idCargo pelo valor desejado
$dados = $stmt->fetch(PDO::FETCH_ASSOC); // Obtém os dados como um array associativo

if ($dados) {
    // Cria uma string com os valores separados por vírgula
    $stringDados = implode(", ", $dados);
} else {
    $stringDados = "Nenhum dado encontrado.";
}

$sql = "DELETE FROM rh_cipa_atendimentos WHERE id = :id";
$stmt = $conn->prepare($sql);
$stmt->execute(['id' => $id]);

f_log("EXC", "EXCLUSÃO de Atendimento de CIPA, ID: $id | Dados Excluídos( $stringDados )", "rh_cipa_atendimentos", $idModulo, $id);

$retorno = [
    "status" => true,
    "msg" => "<div class='alert alert-success'><strong>Successo!</strong> Registro Excluído.</div>"
];
die( json_encode($retorno) );