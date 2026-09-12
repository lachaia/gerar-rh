<?PHP

session_start();
if (!isset($_SESSION['idLogin']) || !in_array((int) ($_SESSION['idGrupo'] ?? 0), [4, 7, 9], true)) {
    http_response_code(403);
    die('');
}

include_once "../includes/conexao_gerar.php";

    $sql = "SELECT id, descricao FROM rh_equip_tipos";
    $stmt = $conn->prepare($sql);
    $stmt->execute();

    $options = '<select name="idTipoEquip[]" class="form-select">
                    <option value="" selected>-- selecione um tipo --</option>';
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $options .= '<option value="' . $row['id'] . '">' . htmlspecialchars($row['descricao']) . '</option>';
    }
    $options .= '</select>';

    echo $options;
