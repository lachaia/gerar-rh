<?PHP
//
// rh_cipa_aj21.php | RECUPERA dados de AÇÃO de CIPA
// (C)haia, 30/06/2025
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

$sql = "SELECT 
            A.*,
            S.identificador as dsSubSede,

            -- Lista de nomes dos participantes
            (
                SELECT GROUP_CONCAT(P.nome ORDER BY P.nome SEPARATOR ', ')
                FROM rh_cipa_acoes_membros M
                INNER JOIN rh_cipeiros B ON B.id = M.idCipeiro
                INNER JOIN rh_pessoas P ON P.idPessoa = B.idPessoa
                WHERE M.idAcao = A.id AND M.presente = 1
            ) AS nomesParticipantes,

            -- Lista de IDs dos cipeiros participantes
            (
                SELECT GROUP_CONCAT(M.idCipeiro ORDER BY M.idCipeiro SEPARATOR ',')
                FROM rh_cipa_acoes_membros M
                WHERE M.idAcao = A.id AND M.presente = 1
            ) AS idParticipantes

        FROM rh_cipa_acoes A
        INNER JOIN rh_subsedes S ON S.subsede_id = A.idSubSede
        WHERE A.id = :id";

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