<?PHP
//
//- rh_cipa_aj5.php | Recupera dados do Membro para a Modal Visualizar
// (C)haia, 17/06/2025

session_start();

$idModulo = 11; // CIPA

if (!isset($_SESSION['idLogin']) || (empty($_SESSION['dcCIPA']) && (int) ($_SESSION['idGrupo'] ?? 0) !== 9)) {
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

$sql = "SELECT P.nome, P.idPessoa, B.id as idMembro, B.*, C.dsCargo, S.identificador as dsSubSede,
                ifnull(P.email,'ND') as email, ifnull(P.telefone,'ND') as telefone
                FROM rh_cipeiros B
                INNER JOIN rh_pessoas P ON P.idPessoa = B.idPessoa
                INNER JOIN rh_cipa_cargos C on C.idCargo = B.idCargo
                INNER JOIN rh_subsedes S on S.subsede_id = B.idSubSede
        WHERE id = :id";
$stmt = $conn->prepare($sql);   
$stmt->execute(['id' => $id]); // Substitua $id pelo valor desejado
$dados = $stmt->fetch(PDO::FETCH_ASSOC); // Obtém os dados como um array associativo

$retorno = [
    "status" => true,
    "dados" => $dados
];

$conn = null; // Fecha a conexão com o banco de dados

die(json_encode($retorno));