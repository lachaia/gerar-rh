<?PHP 
//
//- rh_equipamentos_aj5.php | Retorno dados do TERMO para Visualização em Modal
//- (C)haia, 29/08/2025
//

session_start();

if( isset($_SESSION['idLogin'])){
    $idLogin = $_SESSION['idLogin'];
    include_once "conexao_gerar.php";
}

$sql = "SELECT T.*, P.nome, P.cpf
            FROM rh_equip_termos T
            INNER JOIN rh_pessoas P on P.idPessoa = T.idPessoa
            WHERE T.id = :id";
$stmt = $conn->prepare($sql);
$stmt->bindParam(':id', $_POST['id'], PDO::PARAM_INT);
$stmt->execute();
$dados = $stmt->fetch(PDO::FETCH_ASSOC);
extract( $dados );


    $dsStatus = "Indefinido";
    if( $dados['status'] == 'Assinado')  $dsStatus = "<span class='badge bg-success'>Assinado</span>";
    if( $dados['status'] == 'Pendente')  $dsStatus = "<span class='badge bg-warning text-dark'>Pendente</span>";
    if( $dados['status'] == 'Baixado')   $dsStatus = "<span class='badge bg-danger'>Baixado</span>";

$dados['dsStatus'] = $dsStatus;
//
//-- CRIA BLOCO DO ENDEREÇO
//

$x = "$termo_endereco, $termo_end_numero, $termo_end_cpl, $termo_bairro,<br> $termo_cep - $termo_cidade, $termo_uf";
$dados['endereco'] = $x;

//
//- Monta a Tabela HTML da linha detalhe
//

$sql = "SELECT L.*, T.descricao as dsTipo 
            FROM rh_equip_termos_ld L
            INNER JOIN rh_equip_tipos T on T.id = L.idTipo 
            WHERE idEquipTermo = :id";
$stmt_2 = $conn->prepare($sql);
$stmt_2->bindParam(':id', $_POST['id'], PDO::PARAM_INT);
$stmt_2->execute();

$tabela = "<table class='table table-striped table-bordered w-100'>";
$tabela .= "<tr>";
$tabela .= "<th>Tipo</th><th>Modelo</th><th>Patrimônio</th>";
$tabela .= "</tr>";
while ($linha = $stmt_2->fetch(PDO::FETCH_ASSOC)) {
    extract( $linha );
    $tabela .= "<tr><td>$dsTipo</td><td>$modelo</td><td>$patrimonio</td></tr>";
}
$tabela .= "</table>";

$dados['tabela'] = $tabela;
$dados['termo'] = html_entity_decode($termo_html);

die( json_encode($dados) );

