<?PHP

function notificar_usuarios_evento($idEvento, $mensagem, $link = null, $idTipo = 1) {
    global $conn; // PDO

    $sql = "SELECT idUsuario FROM rh_notificacoes_usuarios WHERE idEvento = :idEvento";
    $stmt = $conn->prepare($sql);
    $stmt->execute([':idEvento' => $idEvento]);

    $usuarios = $stmt->fetchAll(PDO::FETCH_COLUMN); // array de idUsuario

    $agora = date('Y-m-d H:i:s');

    $insert = $conn->prepare("INSERT INTO rh_notificacoes (idUsuario, mensagem, link, criado_em, idTipo)
                              VALUES (:idUsuario, :mensagem, :link, :criado_em, :idTipo)");

    foreach ($usuarios as $idUsuario) {
        $insert->execute([
            ':idUsuario' => $idUsuario,
            ':mensagem' => $mensagem,
            ':link' => $link,
            ':criado_em' => $agora,
            ':idTipo' => $idTipo
        ]);
    }
}

/*
    idEvento = 1 - Férias
    idTipo = 1 - Info
    notificar_usuarios_evento( $idEvento, $mensagem, $link = null, $idTipo = 1)
    notificar_usuarios_evento( 1, 'Solicitação de férias...', 'rh_ferias_aprovar.php?id=123', 2 )
*/