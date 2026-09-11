<?PHP
//
//- rh_notificacoes_aj.php | Gera lista das notificações do Usuário
//- (C)haia, 05/08/2025

session_start();

include "conexao_gerar.php";

$idUsuario = $_SESSION['idUsuario'];

$sql = "SELECT *
          FROM rh_notificacoes N
          INNER JOIN rh_notificacoes_tipo T on T.idTipo = N.idTipo
          WHERE idUsuario = :idUsuario AND lido_em IS NULL
          ORDER BY criado_em DESC";
$stmt = $conn->prepare($sql);
$stmt->bindParam(':idUsuario', $idUsuario, PDO::PARAM_INT);
$stmt->execute();

$notificacoes_html = [];

while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
    $icone = $row['icone'] ?? 'fas fa-info-circle';
    $cor = $row['cor'] ?? 'secondary';
    $mensagem = htmlspecialchars($row['mensagem']);
    $link = trim($row['link']);

    $html = "<div class='alert alert-{$cor} d-flex align-items-center mb-3' data-id='{$row['id']}'>";
    $html .= "<input type='checkbox' class='me-2 fx-checkbox-nota' />";
    $html .= "<i class='{$icone} me-2'></i>";

    if (!empty($link)) {
        $html .= "<div><a href='{$link}' class='text-reset text-decoration-none'>{$mensagem}</a></div>";
    } else {
        $html .= "<div>{$mensagem}</div>";
    }
    $html .= "</div>";

    $notificacoes_html[] = $html;
}

echo json_encode($notificacoes_html);
