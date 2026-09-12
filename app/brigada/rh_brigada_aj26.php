<?PHP
//
// rh_brigada_aj26.php | RECUPERA dados de DOCUMENTO para visualização
// (C)haia, 16/07/2025
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
} else{
    die(json_encode(["status" => false, "msg" => "Faltaram Parâmetros!"]));
}

$sql = "SELECT D.*, T.nome as dsTipoDoc
                FROM rh_documentos D
                INNER JOIN rh_docs_tipo T on T.idTipoDoc = D.idTipoDoc
                WHERE D.idDoc = :id";

$stmt = $conn->prepare($sql);
$stmt->execute(['id' => $id]); // Substitua $idCargo pelo valor desejado
$dados = $stmt->fetch(PDO::FETCH_ASSOC); // Obtém os dados como um array associativo

if ($dados) {
    // Se os dados foram encontrados, retorna como JSON
    echo json_encode(["status" => true, "dados" => $dados]);
} else {
    // Se não encontrou, retorna um erro
    echo json_encode(["status" => false, "msg" => "Ação não encontrada!"]);
}
$conn = null; // Fecha a conexão com o banco de dados
exit; // Encerra o script