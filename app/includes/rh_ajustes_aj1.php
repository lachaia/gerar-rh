<?php
//
//- rh_ajustes_aj1.php | Retorna dados da Solicitacao ID | Modulo Ponto Eletrônico
//

header('Content-Type: application/json');

session_start();

$idModulo = 22; //| Modulo Ponto Eletrônico

if (!isset($_SESSION['idLogin'])) {
    header('Location: ../logout.php');
    exit();
}

include_once "conexao_gerar.php";

$id = $_POST['id'];

if( empty($id)){
    $retorno = [
        "status" => false,
        "msg" => '<div class="alert alert-danger">
            <strong>Erro!</strong> Faltou parâmetros!
            </div>'];
    die(json_encode($retorno));
}

$pesquisa = "SELECT 
                    P.nome,
                    P2.nome as gestor,
                    S.*,
                    DATEDIFF(
                        COALESCE(S.aprovado_em, NOW()),
                        S.solicitado_em
                    ) AS dias_decorridos
                FROM rh_ponto_solicitacoes S
                INNER JOIN rh_colaboradores C ON C.idColab = S.colaborador_id
                INNER JOIN rh_pessoas P ON P.idPessoa = C.idPessoa
                INNER JOIN rh_pessoas P2 ON P2.idPessoa = S.supervisor_id  
                WHERE S.id = $id";
$stmt = $conn->prepare( $pesquisa );
$stmt->execute();

$dados = $stmt->fetch(PDO::FETCH_ASSOC);

$colaborador_id = $dados['colaborador_id'];

$dsStatus = $dados['status'];
if( $dados['status'] == 'REJEITADO' ) $dsStatus = "<span class='badge bg-danger w-100 status'>REJEITADO</span>";
if( $dados['status'] == 'APROVADO'  ) $dsStatus = "<span class='badge bg-success w-100 status'>APROVADO</span>";
if( $dados['status'] == 'AGUARDANDO') $dsStatus = "<span class='badge bg-warning text-dark w-100 status'>AGUARDANDO</span>";

if(empty($dados['decisao_obs']) ) $dados['decisao_obs'] = 'Nenhuma observação';
if(empty($dados['aplicado_em']) ) $dados['aplicado_em'] = 'Nenhuma data';

$retorno = [
    "status" => true,
    "msg" => "SUCESSO",
    "dados" => $dados,
    'dsStatus' => $dsStatus
];
die(json_encode($retorno));