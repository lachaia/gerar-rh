<?PHP
//
// rh_brigada_aj21.php | RECUPERA dados de AÇÃO de Brigada
// (C)haia, 30/06/2025
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

$sql = "SELECT 
            A.*,
            S.identificador as dsSubSede,

            -- Lista de nomes dos participantes
            (
                SELECT GROUP_CONCAT(P.nome ORDER BY P.nome SEPARATOR ', ')
                FROM rh_brigada_acoes_membros M
                INNER JOIN rh_brigadistas B ON B.id = M.idBrigadista
                INNER JOIN rh_pessoas P ON P.idPessoa = B.idPessoa
                WHERE M.idAcao = A.id AND M.presente = 1
            ) AS nomesParticipantes,

            -- Lista de IDs dos brigadistas participantes
            (
                SELECT GROUP_CONCAT(M.idBrigadista ORDER BY M.idBrigadista SEPARATOR ',')
                FROM rh_brigada_acoes_membros M
                WHERE M.idAcao = A.id AND M.presente = 1
            ) AS idParticipantes

        FROM rh_brigada_acoes A
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