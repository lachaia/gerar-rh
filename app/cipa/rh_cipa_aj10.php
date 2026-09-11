<?PHP
//
//- rh_cipa_aj10.php | Recupera dados do ATENDIMENTO para a Modal Visualizar
// (C)haia, 24/06/2025

session_start();

$idModulo = 11; // CIPA

if (!isset($_SESSION['idLogin'])) {
    header('location: ../logout.php');
    exit();
} else {
    include_once "../includes/conexao_gerar.php";
    include_once "../includes/f_logs.php";
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

$sql = "SELECT S.identificador as dsSubSede, P.nome as nmCipeiro, A.*, O.descricao as dsOcorrencia
            FROM RH.rh_cipa_atendimentos A
            INNER JOIN rh_subsedes S on S.subsede_id = A.idSubSede
            INNER JOIN rh_cipeiros B on B.id = A.idCipeiro
            INNER JOIN rh_pessoas P on P.idPessoa = B.idPessoa
            INNER JOIN rh_cipa_tipo_ocorrencia O on O.id = A.tipo_ocorrencia
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