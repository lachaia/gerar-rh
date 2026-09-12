<?PHP
//
// rh_brigada_aj23.php | Exclui Registro de AÇÃO de Brigada
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

/*
include_once "debug.php";
debug( json_encode($dados, JSON_PRETTY_PRINT));
echo json_encode(["status" => false, "msg" => "<div class='alert alert-primary'><strong>Success!</strong>Teste de Sistema</div>"]);
$conn = null;
die;
/*
 rh_brigada_aj23.php | 2025-06-30 16:51:52 
{
    "id": "4"
}
*/

//
//- RECUPERA DADOS DA AÇÃO ANTES DA EXCLUSÃO
//
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
        WHERE id = :id";
$stmt = $conn->prepare($sql);
$stmt->execute(['id' => $id]); // Substitua $idCargo pelo valor desejado
$dados = $stmt->fetch(PDO::FETCH_ASSOC); // Obtém os dados como um array associativo

if ($dados) {
    // Cria uma string com os valores separados por vírgula
    $stringDados = json_encode($dados);
} else {
    $stringDados = "Nenhum dado encontrado.";
}
//
//- EXCLUI LINHA DE TEMPO das PESSOAS
//
    $sql = "DELETE FROM rh_pessoas_ldt WHERE idAcaoTipo = 33 and idOrigem = :id";
    $stmt = $conn->prepare($sql);
    $stmt->execute(['id' => $id]);
//
//- EXCLUI OS MEMBROS DA AÇÃO
//
    $sql = "DELETE FROM rh_brigada_acoes_membros WHERE idAcao = :id";
    $stmt = $conn->prepare($sql);
    $stmt->execute(['id' => $id]);
//
//- EXCLUI A AÇÃO
//
    $sql = "DELETE FROM rh_brigada_acoes WHERE id = :id";
    $stmt = $conn->prepare($sql);
    $stmt->execute(['id' => $id]);
//
//- EXCLUI O ANEXO DE ATA - se existir
//
    if( !empty( $dados['acao_arquivo'] ))
    {
        $caminhoArquivo = "../docs/brigada/" . basename($dados['acao_arquivo']);
        if (file_exists($caminhoArquivo)) {
            unlink($caminhoArquivo); // Exclui o arquivo do servidor
        }
    }

f_log("EXC", "EXCLUSÃO de AÇÃO de Brigada, ID: $id | Dados Excluídos( $stringDados )", "rh_brigada_acoes", $idModulo, $id);

$retorno = [
    "status" => true,
    "msg" => "<div class='alert alert-success'><strong>Successo!</strong> Registro Excluído.</div>"
];
die( json_encode($retorno) );