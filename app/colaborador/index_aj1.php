<?PHP
//
// index_aj1.php | Retorna dados do Período para a modal de Agendamento
// (C)haia, 08/05/2025 | (U) 21/05/2025
//

session_start();

include "../includes/conexao_gerar.php";

if (isset($_POST['id'])) $id = $_POST['id'];
else $id = 0;

if (empty($id)) {
    $retorno = [
        "status" => false,
        "msg" => "Faltou Parâmetros"
    ];
    die(json_encode($retorno, JSON_PRETTY_PRINT));
}

$sql = "SELECT F.*,
            FLOOR(LEAST(12, TIMESTAMPDIFF(MONTH, F.inicio_aquisitivo, CURDATE())) * 2.5) AS dias_adquiridos,
            DATEDIFF(F.fim_concessivo, CURDATE()) AS dias_para_vencer 
            FROM rh_ferias F 
            WHERE id = :id
            ORDER BY inicio_aquisitivo DESC";
$consulta = $conn->prepare($sql);
$consulta->bindParam(':id', $id);
$consulta->execute();
$linha = $consulta->fetch(PDO::FETCH_ASSOC);

$retorno = [
    "status" => true,
    "msg" => "Dados Recuperados com Sucesso",
    'dados' => $linha
];

$conn = null;
die(json_encode($retorno, JSON_PRETTY_PRINT));
