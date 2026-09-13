<?php

session_start();

include_once "../includes/conexao_gerar.php";
include_once "../includes/debug.php";
include_once "../includes/f_upload_seguro.php";

$idUsuario = $_SESSION['idUsuario'];
$chaveApp  = $_POST['chaveApp'];

//
//- Recupera dados antes da ALTERAÇÃO
//
$sql = "SELECT U.* FROM rh_usuarios U WHERE idUsuario = :id";
$stmt = $conn->prepare($sql);
$stmt->bindParam('id', $idUsuario, PDO::PARAM_INT);
$stmt->execute();
$antigo = $stmt->fetch(PDO::FETCH_ASSOC);

if ($antigo) {
    // O registro foi encontrado
    $_foto = $antigo['foto'];
    $dadosAntigos = "Dados Antigos: " . implode(', ', $antigo);
    //
} else {
    // O registro não foi encontrado
    $conn = null;
    $retorna = ["status" => false, "msg" => '<div class="alert alert-success" role="alert">ERRO: Usuário não encontrado!</div>' ];
    echo json_encode($retorna);
}

$status   = true;
$mensagem = '<div class="alert alert-success" role="alert">Processo concluído com sucesso!</div>';

if (isset($_FILES['foto'])) {
    $foto = $_FILES['foto'];
    //
    if (!empty($_FILES["foto"]["tmp_name"])) {
        $validacao = upload_seguro_validar($_FILES['foto'], ['jpg', 'jpeg', 'png', 'gif', 'webp', 'bmp']);
        if ($validacao !== true) {
            $conn = null;
            die(json_encode(["status" => false, "msg" => "<div class='alert alert-danger' role='alert'>ERRO: $validacao</div>"]));
        }
        $nomeTemporario = $_FILES["foto"]["tmp_name"];
        $nomeArquivo = $_FILES["foto"]["name"];
        $extensao = strtolower(pathinfo($nomeArquivo, PATHINFO_EXTENSION));
        // Nome antes era só "usu_<idUsuario>.<ext>", sem parte aleatória —
        // totalmente previsível (facilita adivinhar onde um upload cairia
        // caso alguma validação de tipo seja um dia contornada).
        $novoNome = "usu_" . str_pad($idUsuario, 6, "0", STR_PAD_LEFT) . "_" . bin2hex(random_bytes(4)) . "." . $extensao;
        $caminhoDestino = "../fotos/" . $novoNome;
        //
        //- apaga foto antiga (nunca a imagem padrão compartilhada)
        if (!empty($_foto) && $_foto !== 'perfil.png') {
            $fotoAntiga = "../fotos/" . basename($_foto);
            if (is_file($fotoAntiga)) {
                unlink($fotoAntiga);
            }
        }
        //
        if (move_uploaded_file($nomeTemporario, $caminhoDestino)) {
            //
            $sql = "UPDATE rh_usuarios SET foto = :foto WHERE idUsuario = :idUsuario";
            $stmt = $conn->prepare($sql);
            $stmt->bindParam(':foto', $novoNome);
            $stmt->bindParam(':idUsuario', $idUsuario);
            $result = $stmt->execute();

            // Envie uma resposta JSON de sucesso
            $_SESSION['perfil'] = $novoNome;
            $_foto = $novoNome;
            $status = true;
            $mensagem = '<div class="alert alert-success" role="alert">Sucesso: arquivo salvo!</div>';
            //
        } else {
            // O arquivo não pôde ser movido
            $status = false;
            $mensage = '<div class="alert alert-danger" role="alert">ERRO: erro ao mover o arquivo!</div>';
        }
    }    
}

//
if (!empty($chaveApp)) {
    $sql = "UPDATE rh_usuarios SET chaveApp = :chaveApp WHERE idUsuario = :idUsuario";
    $stmt = $conn->prepare($sql);
    $stmt->bindParam(':chaveApp', $chaveApp);
    $stmt->bindParam(':idUsuario', $idUsuario);
    $result = $stmt->execute();
    $_SESSION['chaveApp'] = $chaveApp;
}

$retorna = ["status" => $status, "msg" => $mensagem, "htmlFoto"=> $_foto ];
echo json_encode($retorna);