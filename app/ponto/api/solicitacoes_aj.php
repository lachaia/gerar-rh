<?PHP 
//
//- solicitacoes_aj.php | Recupera dados da Solicitação
//- (C)haia, 17/11/2025
//

session_start();

include "../../includes/conexao_gerar.php";

$solicitacao_id = $_POST['solicitacao_id'];

$sql = "SELECT * FROM rh_ponto_solicitacoes WHERE id = :id";
$stmt = $conn->prepare($sql);
$stmt->bindParam(':id', $solicitacao_id, PDO::PARAM_INT);
$stmt->execute();
$linha = $stmt->fetch(PDO::FETCH_ASSOC);

$dsTipo = $linha['tipo'];

if( $dsTipo == 'ABO' ) $dsTipo = "Abonar Falta"; 
if( $dsTipo == 'INC' ) $dsTipo = "Incluir Ponto"; 
if( $dsTipo == 'ALT' ) $dsTipo = "Alterar Ponto"; 
if( $dsTipo == 'DEL' ) $dsTipo = "Excluir Ponto"; 

$descisao_obs = $linha['decisao_obs'] ? $linha['decisao_obs'] : "Sem Observação";

$linha['dsTipo'] = $dsTipo;
$linha['decisao_obs'] = $descisao_obs;

echo json_encode($linha);