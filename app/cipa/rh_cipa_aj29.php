<?PHP
//
// rh_cipa_aj29.php | Exclui Registro de DOCUMENTO
// (C)haia, 17/07/2025
//

session_start();

$idModulo = 11; // CIPA

if (!isset($_SESSION['idLogin'])) {
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
include_once "../includes/debug.php";
debug( json_encode($dados, JSON_PRETTY_PRINT));
echo json_encode(["status" => false, "msg" => "<div class='alert alert-primary'><strong>Success!</strong>Teste de Sistema</div>"]);
$conn = null;
die;
 rh_cipa_aj29.php | 2025-07-17 09:22:56 
{
    "id": "147"
}
*/

//
//- RECUPERA DADOS DA AÇÃO ANTES DA EXCLUSÃO
//
$sql = "SELECT D.*, T.nome as dsTipoDoc
            FROM rh_documentos D
            INNER JOIN rh_docs_tipo T on T.idTipoDoc = D.idTipoDoc
            WHERE D.idDoc = :id";
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
    $sql = "DELETE FROM rh_documentos WHERE idDoc = :id";
    $stmt = $conn->prepare($sql);
    $stmt->execute(['id' => $id]);

//
//- EXCLUI O ANEXO DE ATA - se existir
//
    if( !empty( $dados['arquivo'] ))
    {
        $arquivo = $dados['arquivo'];
        $caminhoArquivo = "../docs/cipa/" . basename($arquivo);
        if (file_exists($caminhoArquivo)) {
            unlink($caminhoArquivo); // Exclui o arquivo do servidor
        }
    }

f_log("EXC", "EXCLUSÃO de DOCUMENTO de CIPA, ID: $id | Dados Excluídos( $stringDados )", "rh_documentos", $idModulo, $id);

$retorno = [
    "status" => true,
    "msg" => "<div class='alert alert-success'><strong>Successo!</strong> Registro Excluído.</div>"
];
die( json_encode($retorno) );