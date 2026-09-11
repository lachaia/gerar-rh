<?PHP
//
// rh_cipa_aj4.php | Exclui Registro de Membro de CIPA
// (C)haia, 17/06/2025
//


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
    $agora = date('Y-m-d H:i:s');
}

$dados = filter_input_array(INPUT_POST, FILTER_DEFAULT);
if ($dados) {
    extract($dados);
} else {
    die(json_encode(["status" => false, "msg" => "Faltaram Parâmetros!"]));
}

/*
$retorno = [
    "status" => false,
    "msg" => "<div class='alert alert-success'><strong>Success!</strong> Indicates a successful or positive action.</div>"
];
die( json_encode($retorno) );
*/
/*
include_once "debug.php";
debug( json_encode($dados, JSON_PRETTY_PRINT));
echo json_encode(["status" => false, "msg" => "<div class='alert alert-primary'><strong>Success!</strong>Teste de Sistema</div>"]);
$conn = null;
die;
 rh_cipa_aj4.php | 2025-06-17 14:50:55 
{
    "id": "6"
}
*/

$sql = "SELECT * FROM rh_cipeiros WHERE id = :id";
$stmt = $conn->prepare($sql);
$stmt->execute(['id' => $id]); // Substitua $idCargo pelo valor desejado
$dados = $stmt->fetch(PDO::FETCH_ASSOC); // Obtém os dados como um array associativo

$arquivoFoto = $dados['foto'];

if ($dados) {
    // Cria uma string com os valores separados por vírgula
    $stringDados = implode(", ", $dados);
}

//
//- VERIFICA SE PODE EXCLUIR
//

$sql = "SELECT * FROM RH.rh_cipa_reuniao_membros WHERE idCipeiro = :id";
$stmt = $conn->prepare($sql);
$stmt->execute(['id' => $id]); // Substitua $idCipeiro pelo valor desejado
$dados = $stmt->fetch(PDO::FETCH_ASSOC); // Obtém os dados como um array associativo

if ($dados) {
    // Se encontrou registro, faz o UPDATE
    $sqlUpdate = "UPDATE rh_cipeiros SET data_final = :agora WHERE id = :id";
    $stmtUpdate = $conn->prepare($sqlUpdate);
    $stmtUpdate->execute([
        'agora' => $agora,
        'id' => $id
    ]);
    //
    $retorno = [
        "status" => true,
        "msg" => "<div class='alert alert-success'><strong>Successo!</strong> Membro Encerrado!</div>"
    ];
    die(json_encode($retorno));
}

$sql = "DELETE FROM rh_cipeiros WHERE id = $id";
$stmt = $conn->prepare($sql);
$stmt->execute();

if (! empty($arquivoFoto)) {
    $caminhoFoto = "../docs/cipa/" . $arquivoFoto;
    if (file_exists($caminhoFoto)) {
        if (unlink($caminhoFoto)) {
            f_log("EXC", "Foto de Membro de CIPA excluída: $arquivoFoto", "rh_cipeiros", $idModulo, $id);
        } else {
            f_log("ERR", "Erro ao excluir foto de Membro de CIPA: $arquivoFoto", "rh_cipeiros", $idModulo, $id);
        }
    } else {
        f_log("ERR", "Arquivo de foto não encontrado: $caminhoFoto", "rh_cipeiros", $idModulo, $id);
    }
}

f_log("EXC", "EXCLUSÃO de Membro de CIPA, ID: $id | Dados Excluídos( $stringDados )", "rh_cipeiros", $idModulo, $id);

$retorno = [
    "status" => true,
    "msg" => "<div class='alert alert-success'><strong>Successo!</strong> Registro Excluído.</div>"
];
die(json_encode($retorno));
