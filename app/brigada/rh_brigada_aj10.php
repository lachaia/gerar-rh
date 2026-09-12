<?PHP
//
//- rh_brigada_aj10.php | Recupera dados do ATENDIMENTO para a Modal Visualizar
// (C)haia, 24/06/2025

session_start();

$idModulo = 10; // Brigada

if (!isset($_SESSION['idLogin']) || (empty($_SESSION['dcBrigada']) && (int) ($_SESSION['idGrupo'] ?? 0) !== 9)) {
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

$sql = "SELECT
    S.identificador AS dsSubSede,
    A.*,
    O.descricao AS dsOcorrencia,

    -- Lista de nomes
    (
        SELECT GROUP_CONCAT(P2.nome ORDER BY P2.nome SEPARATOR ', ')
        FROM rh_brigada_atend_membros M2
        INNER JOIN rh_brigadistas B2 ON B2.id = M2.idBrigadista
        INNER JOIN rh_pessoas P2 ON P2.idPessoa = B2.idPessoa
        WHERE M2.idAtendimento = A.id
    ) AS membros,

    -- Lista de IDs dos brigadistas participantes
    (
        SELECT GROUP_CONCAT(M2.idBrigadista ORDER BY M2.idBrigadista SEPARATOR ',')
        FROM rh_brigada_atend_membros M2
        WHERE M2.idAtendimento = A.id
    ) AS idMembros

FROM rh_atendimentos A
INNER JOIN rh_subsedes S ON S.subsede_id = A.idSubSede
INNER JOIN rh_brigada_tipo_ocorrencia O ON O.id = A.tipo_ocorrencia
WHERE A.id = :idAtendimento;";

$stmt = $conn->prepare($sql);   
$stmt->execute(['idAtendimento' => $id]); // Substitua $id pelo valor desejado
$dados = $stmt->fetch(PDO::FETCH_ASSOC); // Obtém os dados como um array associativo

$retorno = [
    "status" => true,
    "dados" => $dados
];

$conn = null; // Fecha a conexão com o banco de dados

die(json_encode($retorno));