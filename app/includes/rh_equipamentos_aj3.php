<?PHP 
//
//- rh_equipamentos_aj3.php | Retorno dados da Solicitação para Visualização em Modal
//- (C)haia, 20/08/2025
//

session_start();

if( isset($_SESSION['idLogin'])){
    $idLogin = $_SESSION['idLogin'];
    include_once "conexao_gerar.php";
}

$sql = "SELECT S.*, P.nome 
            FROM RH.rh_equip_solic S
            INNER JOIN rh_pessoas P on P.idPessoa = S.idPessoa
            WHERE S.id = :id";
$stmt = $conn->prepare($sql);
$stmt->bindParam(':id', $_POST['id'], PDO::PARAM_INT);
$stmt->execute();

$linha = $stmt->fetch(PDO::FETCH_ASSOC);

die( json_encode($linha) );

