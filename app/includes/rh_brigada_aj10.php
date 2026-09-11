<?PHP
//
//- rh_brigada_aj10.php | Recupera dados do ATENDIMENTO para a Modal Visualizar
// (C)haia, 24/06/2025

session_start();

$idModulo = 10; // Brigada

if (!isset($_SESSION['idLogin'])) {
    header('Location: logout.php');
    exit();
} else {
    include_once "conexao_gerar.php";
    include_once "f_logs.php";
    //
    $idLogin = $_SESSION['idLogin'];
    $nmLogin = $_SESSION['nmLogin'];
    $idEmpresa = $_SESSION['idEmpresa'];
}

$dados = filter_input_array(INPUT_POST, FILTER_DEFAULT);
if ($dados) {
    extract($dados);
} else {
    die(json_encode(["status" => false, "msg" => "Faltaram Parâmetros!"]));
}

$sql = "SELECT S.dsSubSede, P.nome as nmBrigadista, A.*, O.descricao as dsOcorrencia 
            FROM RH.rh_atendimentos A
            INNER JOIN rh_subsedes S on S.idSubSede = A.idSubSede
            INNER JOIN rh_brigadistas B on B.id = A.idBrigadista
            INNER JOIN rh_pessoas P on P.idPessoa = B.idPessoa
            INNER JOIN rh_brigada_tipo_ocorrencia O on O.id = A.tipo_ocorrencia
        WHERE A.id = :id";
$stmt = $conn->prepare($sql);   
$stmt->execute(['id' => $id]); // Substitua $id pelo valor desejado
$dados = $stmt->fetch(PDO::FETCH_ASSOC); // Obtém os dados como um array associativo

$retorno = [
    "status" => true,
    "dados" => $dados
];

$conn = null; // Fecha a conexão com o banco de dados

die(json_encode($retorno));