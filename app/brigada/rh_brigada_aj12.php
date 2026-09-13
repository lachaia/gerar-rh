<?PHP
//
// rh_brigada_aj12.php | Exclui Registro de ATENDIMENTO de Brigada
// (C)haia, 24/06/2025
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
include_once "debug.php";
debug( json_encode($dados, JSON_PRETTY_PRINT));
$conn = null;
die(json_encode(["status" => false, "msg" => "<div class='alert alert-primary'><strong>Success!</strong>Teste de Sistema</div>"]));
*/

//
// - RECUPERA dados antes de excluir
//
    $sql = "SELECT
                    S.identificador as dsSubSede,
                    A.*,
                    O.descricao AS dsOcorrencia,
                    GROUP_CONCAT(P.nome ORDER BY P.nome SEPARATOR ', ') AS membros
                FROM rh_atendimentos A
                INNER JOIN rh_subsedes S ON S.subsede_id = A.idSubSede
                INNER JOIN rh_brigada_tipo_ocorrencia O ON O.id = A.tipo_ocorrencia
                LEFT JOIN rh_brigada_atend_membros M ON M.idAtendimento = A.id 
                LEFT JOIN rh_brigadistas B2 ON B2.id = M.idBrigadista
                LEFT JOIN rh_pessoas P ON P.idPessoa = B2.idPessoa
                WHERE A.id = :idAtendimento
                GROUP BY A.id";
    $stmt = $conn->prepare($sql);
    $stmt->execute(['idAtendimento' => $id]); // Substitua $id pelo valor desejado
    $dados = $stmt->fetch(PDO::FETCH_ASSOC); // Obtém os dados como um array associativo

    if ($dados) {
        // Cria uma string com os valores separados por vírgula
        $stringDados = implode(", ", $dados);
    } else {
        $stringDados = "Nenhum dado encontrado.";
    }

//
//- EXCLUI REGISTRO DE ATENDIMENTO
//
    $sql = "DELETE FROM rh_atendimentos WHERE id = :id";
    $stmt = $conn->prepare($sql);
    $stmt->execute(['id' => $id]);

//
//- EXCLUI BRIGADISTAS QUE FIZERAM O ATENDIMENTO
//
    $sql = "DELETE FROM rh_brigada_atend_membros WHERE idAtendimento = :id";
    $stmt = $conn->prepare($sql);
    $stmt->execute(['id' => $id]);

f_log("EXC", "EXCLUSÃO de Atendimento de Brigada, ID: $id | Dados Excluídos( $stringDados )", "rh_atendimentos", $idModulo, $id);

$retorno = [
    "status" => true,
    "msg" => "<div class='alert alert-success'><strong>Successo!</strong> Registro Excluído.</div>"
];
die( json_encode($retorno) );