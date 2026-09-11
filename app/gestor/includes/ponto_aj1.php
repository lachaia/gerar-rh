<?PHP 
//
//- ponto_aj1.php | Retorna dados da Batida e colaborador para modal de aprovação
// (C)haia, 13/11/2025
//

session_start();

if (isset($_SESSION['idLogin'])) {
    $idLogin = $_SESSION['idLogin'];
    include_once "../../includes/conexao_gerar.php";
} else {
    header("location: ../logout.php");
}

$idSolicitacao = $_POST['id'];

$sql = "SELECT P.nome, S.*, date(S.data_hora) as data_referencia 
        FROM rh_ponto_solicitacoes S
        INNER JOIN rh_colaboradores C on C.idColab = S.colaborador_id
        INNER JOIN rh_pessoas P on P.idPessoa = C.idPessoa 
        WHERE id = :idSolicitacao";

$stmt = $conn->prepare($sql);
$stmt->bindParam(':idSolicitacao', $idSolicitacao);
$stmt->execute();

$linha = $stmt->fetch(PDO::FETCH_ASSOC);

    $dsTipo = $linha['tipo'];
    if( $linha['tipo'] == 'ABO' ) $dsTipo = "Abonar"; 
    if( $linha['tipo'] == 'INC' ) $dsTipo = "Incluir"; 
    if( $linha['tipo'] == 'ALT' ) $dsTipo = "Alterar"; 
    if( $linha['tipo'] == 'DEL' ) $dsTipo = "Excluir";

$linha['dsTipo'] = $dsTipo;

//
//- Pontos do dia
//
    $sql = "SELECT time(data_hora) as hora, tipo
            FROM rh_ponto_registros 
            WHERE colaborador_id = :idColab AND date(data_hora) = :data";
    $stmt = $conn->prepare($sql);
    $stmt->bindParam(':idColab', $linha['colaborador_id'], PDO::PARAM_INT);
    $stmt->bindParam(':data', $linha['data_referencia'], PDO::PARAM_STR);
    $stmt->execute();
    $batidas = "";
    while($linhaPonto = $stmt->fetch(PDO::FETCH_ASSOC)){
        $batidas .= $linhaPonto['tipo'] . " - " . $linhaPonto['hora'] . "<br>";
    }

    $linha['batidas'] = $batidas;
die( json_encode($linha) );