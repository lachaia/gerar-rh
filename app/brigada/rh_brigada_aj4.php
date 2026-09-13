<?PHP
//
// rh_brigada_aj4.php | Exclui Registro de Membro de Brigada
// (C)haia, 17/06/2025
//


session_start();

$idModulo = 10; // Brigada

if (!isset($_SESSION['idLogin']) || (empty($_SESSION['dcBrigada']) && (int) ($_SESSION['idGrupo'] ?? 0) !== 9)) {
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
    // reafirma identidade da sessão depois do extract() — POST não deve conseguir sobrescrever
    $idLogin = $_SESSION['idLogin'];
    $nmLogin = $_SESSION['nmLogin'];
    $idEmpresa = $_SESSION['idEmpresa'];
} else{
    die(json_encode(["status" => false, "msg" => "Faltaram Parâmetros!"]));
}

/*
$retorno = [
    "status" => false,
    "msg" => "<div class='alert alert-success'><strong>Success!</strong> Indicates a successful or positive action.</div>"
];
die( json_encode($retorno) );
*/
/*
include_once "debug.php";
debug( json_encode($dados, JSON_PRETTY_PRINT));
echo json_encode(["status" => false, "msg" => "<div class='alert alert-primary'><strong>Success!</strong>Teste de Sistema</div>"]);
$conn = null;
die;
 rh_brigada_aj4.php | 2025-06-17 14:50:55 
{
    "id": "6"
}
*/

$sql = "SELECT * FROM rh_brigadistas WHERE id = :id";
$stmt = $conn->prepare($sql);
$stmt->execute(['id' => $id]); // Substitua $idCargo pelo valor desejado
$dados = $stmt->fetch(PDO::FETCH_ASSOC); // Obtém os dados como um array associativo

if ($dados) {
    $arquivoFoto = $dados['foto'];
    // Cria uma string com os valores separados por vírgula
    $stringDados = implode(", ", $dados);
} else {
    $stringDados = "Nenhum dado encontrado.";
}

$sql = "DELETE FROM rh_brigadistas WHERE id = :id";
$stmt = $conn->prepare($sql);
$stmt->execute(['id' => $id]);

if( ! empty($arquivoFoto) ){
    $caminhoFoto = "../docs/brigada/" . $arquivoFoto;
    if (file_exists($caminhoFoto)) {
        if (unlink($caminhoFoto)) {
            f_log("EXC", "Foto de Membro de brigada excluída: $arquivoFoto", "rh_brigadistas", $idModulo, $id);
        } else {
            f_log("ERR", "Erro ao excluir foto de Membro de brigada: $arquivoFoto", "rh_brigadistas", $idModulo, $id);
        }
    } else {
        f_log("ERR", "Arquivo de foto não encontrado: $caminhoFoto", "rh_brigadistas", $idModulo, $id);
    }   
}

f_log("EXC", "EXCLUSÃO de Membro de Brigada, ID: $id | Dados Excluídos( $stringDados )", "rh_brigadistas", $idModulo, $id);

$retorno = [
    "status" => true,
    "msg" => "<div class='alert alert-success'><strong>Successo!</strong> Registro Excluído.</div>"
];
die( json_encode($retorno) );