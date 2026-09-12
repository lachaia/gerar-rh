<?php
// ponto_aj.php | Grid de Consulta das Solicitações de Ajustes de Ponto
// by (C)haia, 13/11/2025
//

$idModulo = 16; // Portal do Gestor

session_start();

if (isset($_SESSION['idLogin'])) {
    $idLogin = $_SESSION['idLogin'];
    include_once "../../includes/conexao_gerar.php";
} else {
    header("location: ../logout.php");
}

$chk_pendentes = $_POST['chk_pendentes'] ?? 0;
// supervisor_id é sempre o do gestor logado — nunca o que o cliente mandar,
// senão qualquer gestor consegue ver solicitações de qualquer outro.
$supervisor_id = (int) ($_SESSION['idColab'] ?? 0);

$onde = "supervisor_id = :supervisor_id AND status = 'AGUARDANDO'";
if( $chk_pendentes == 0 ){
    $onde = "supervisor_id = :supervisor_id";
}

$pesquisa = "SELECT P.nome, S.*
    FROM rh_ponto_solicitacoes S
    INNER JOIN rh_colaboradores C on C.idColab = S.colaborador_id
    INNER JOIN rh_pessoas P on P.idPessoa = C.idPessoa
    WHERE $onde
    ORDER BY data_hora DESC";

$stmt = $conn->prepare($pesquisa);
$stmt->bindParam(':supervisor_id', $supervisor_id, PDO::PARAM_INT);
$stmt->execute();
$recordsFiltered = $stmt->rowCount();

$dados = array();
while ($linha = $stmt->fetch(PDO::FETCH_ASSOC)) {
    extract($linha);
    $dado = array();
    //
    $botoes = "<a href='#!' class='btn btn-outline-primary btn-sm' onClick='f_decidir($id)'><i class='fa-regular fa-eye'></i></a>";
    //
    $dsTipo = $tipo;
    if( $tipo == 'ABO' ) $dsTipo = "Abonar"; 
    if( $tipo == 'INC' ) $dsTipo = "Incluir"; 
    if( $tipo == 'ALT' ) $dsTipo = "Alterar"; 
    if( $tipo == 'DEL' ) $dsTipo = "Excluir"; 
    //
    $dsStatus = $status;
    if( $status == 'AGUARDANDO' ) $dsStatus = "<spam class='badge bg-warning w-100 text-dark status'><i class='fa-regular fa-hourglass'></i> Gestor</spam>";
    if( $status == 'APROVADO' ) $dsStatus = "<spam class='badge bg-success w-100 status'>Aprovado</spam>";
    if( $status == 'REJEITADO' ) $dsStatus = "<spam class='badge bg-danger w-100 status'>Rejeitado</spam>";
    //
    $dado[] = $id;                
    $dado[] = $nome;          
    $dado[] = $solicitado_em; 
    $dado[] = $dsTipo; 
    $dado[] = $data_hora;     
    $dado[] = $aprovado_em ? $aprovado_em : "Pendente";    
    $dado[] = $dsStatus; 
    //       
    $dado[] = $botoes;        
    //
    $dados[] = $dado;
}

//- Criar um vetor para retornar ao Javascript
//

$output = array(
    "draw" => 1,
    "recordsTotal" => intval($recordsFiltered),
    "recordsFiltered" => intval($recordsFiltered),
    "data" => $dados
);
$conteudo = json_encode($output);
echo $conteudo;
exit;
