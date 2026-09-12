<?PHP
//
// rh_cipa_aj16.php | Exclui Registro de REUNIÃO de CIPA
// (C)haia, 26/06/2025 | 14/07/2025
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

/*
include_once "debug.php";
debug( json_encode($dados, JSON_PRETTY_PRINT));
echo json_encode(["status" => false, "msg" => "<div class='alert alert-primary'><strong>Success!</strong>Teste de Sistema</div>"]);
$conn = null;
die;
 rh_cipa_aj16.php | 2025-06-26 15:11:46 
{
    "id": "1"
}
*/

//
//- RECUPERA DADOS DA REUNIÃO ANTES DA EXCLUSÃO
//
$sql = "SELECT R.*, S.identificador as dsSubSede,
    -- Subquery para trazer a lista de nomes dos participantes
    (
        SELECT GROUP_CONCAT(P.nome ORDER BY P.nome SEPARATOR ', ')
        FROM rh_cipa_reuniao_membros M
        INNER JOIN rh_cipeiros B ON B.id = M.idCipeiro
        INNER JOIN rh_pessoas P ON P.idPessoa = B.idPessoa
        WHERE M.idReuniao = R.id AND M.presente = 1
    ) AS nomesParticipantes
    FROM rh_cipa_reunioes R
    INNER JOIN rh_subsedes S ON S.subsede_id = R.idSubSede
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
//- EXCLUI OS MEMBROS DA REUNIÃO
//
    $sql = "DELETE FROM rh_cipa_reuniao_membros WHERE idReuniao = :id";
    $stmt = $conn->prepare($sql);
    $stmt->execute(['id' => $id]);
//
//- EXCLUI A REUNIÃO
//
    $sql = "DELETE FROM rh_cipa_reunioes WHERE id = :id";
    $stmt = $conn->prepare($sql);
    $stmt->execute(['id' => $id]);
//
//- EXCLUI O ANEXO DE ATA - se existir
//
    if( !empty( $dados['ata_arquivo'] ))
    {   //- ../docs/cipa/
        $arquivo = $dados['ata_arquivo'];
        $caminhoArquivo = "../docs/cipa/" . basename($arquivo);
        if (file_exists($caminhoArquivo)) {
            unlink($caminhoArquivo); // Exclui o arquivo do servidor
            //
            $sql = "DELETE FROM rh_documentos WHERE arquivo like :arquivo";
            $stmt = $conn->prepare($sql);
            $stmt->execute(['arquivo' => $arquivo]);
        }
    }

f_log("EXC", "EXCLUSÃO de REUNIÃO de CIPA, ID: $id | Dados Excluídos( $stringDados )", "rh_cipa_reunioes", $idModulo, $id);

$retorno = [
    "status" => true,
    "msg" => "<div class='alert alert-success'><strong>Successo!</strong> Registro Excluído.</div>"
];
die( json_encode($retorno) );