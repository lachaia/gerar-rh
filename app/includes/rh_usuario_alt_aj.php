<?php
// - g_pessoas_alt_aj.php
// - Salva dados em rh_usuarios vindo da Modal ALT da Grade de Usuários
// - 2023-08-23 By Chaia.

session_start();
include_once "../includes/conexao_gerar.php";
include_once "../includes/f_logs.php";

$idModulo  = 1; // rh_usuarios.php

$origem = filter_input_array(INPUT_POST, FILTER_DEFAULT);
if ($origem) extract($origem);

if (empty($idUsuarioGrupo)) {
    die(json_encode(["status" => false, "msg" => "É necessário Selecionar o Grupo de Usuários!"]));
}
if (empty($idPessoa)) {
    die(json_encode(["status" => false, "msg" => "É necessário Selecionar o Nome do Usuário!"]));
}
if (empty($login)) {
    die(json_encode(["status" => false, "msg" => "É necessário informar o Login do usuário!"]));
}
if (empty($ativo)) {
    die(json_encode(["status" => false, "msg" => "FALTOU O \$ativo !"]));
}
if (empty($idSubSede)) {
    die(json_encode(["status" => false, "msg" => "É necessário selecionar a SubSede do Usuário !"]));
}

if ($ativo == "SIM") $kativo = 1;
else $kativo = 0;
if (empty($checkCipa)) $checkCipa = 0;
else $checkCipa = 1;
if (empty($checkBrigada)) $checkBrigada = 0;
else $checkBrigada = 1;
/*
include_once "../includes/debug.php";
debug( json_encode($origem, JSON_PRETTY_PRINT) );
die( json_encode(["status" => true, "msg" => "TESTE REALIZADO COM SUCESSO!"]) );
/*
 rh_usuario_alt_aj.php | 2026-03-13 16:13:35 
{
    "idUsuarioGrupo": "2",
    "idPessoa": "116",
    "idColab": "6",
    "idSubSede": "101",
    "idUsuario": "34",
    "login": "elizete.dreviski",
    "senha": "12344321",
    "ativo": "SIM",
    "checkCipa": "0",
    "checkBrigada": "1",
    "chaveApp": "",
    "foto": "elizete.png"
}
*/

//
//- Recupera dados antes da ALTERAÇÃO
//
$sql = "SELECT U.*, G.descricao as dsGrupo, P.nome, P.nomeSocial, P.cpf, P.ativo as pessoaAtivo
                FROM rh_usuarios U
                INNER JOIN rh_usuariosgrupo G ON G.idUsuarioGrupo = U.idUsuarioGrupo
                INNER JOIN rh_pessoas P ON P.idPessoa = U.idPessoa 
                WHERE idUsuario = :id";
$stmt = $conn->prepare($sql);
$stmt->bindParam('id', $idUsuario, PDO::PARAM_INT);
$stmt->execute();
$antigo = $stmt->fetch(PDO::FETCH_ASSOC);
$dadosAntigos = "Dados Antigos: " . implode(', ', $antigo);
//
//- Verifica se Novo Login Já Existe para outro usuário
//
if ($antigo['login'] != $login) {
    $sql = "SELECT idUsuario, login FROM rh_usuarios WHERE login like '$login' AND idUsuario <> $idUsuairo";
    $stmt = $conn->prepare($sql);
    $stmt->execute();
    //
    if ($stmt->rowCount() > 0) {
        $conn = null;
        die(json_encode(["status" => false, "msg" => "Login já em uso por outro usuário, tente outro!"]));
    }
}

if (! empty($senha)) {
    $ksenha = password_hash($senha, PASSWORD_DEFAULT);
    $sql = "UPDATE rh_usuarios SET 
                idUsuarioGrupo = $idUsuarioGrupo, 
                idPessoa       = $idPessoa, 
                idColab        = $idColab, 
                login          = '$login',                 
                ativo          = $kativo, 
                dcCIPA         = $checkCipa,
                dcBrigada      = $checkBrigada,
                chaveApp       = '$chaveApp', 
                idSubSede      = $idSubSede,
                senha          = '$ksenha'
                WHERE idUsuario = $idUsuario";
} else {
    $sql = "UPDATE rh_usuarios SET 
                idUsuarioGrupo = $idUsuarioGrupo, 
                idPessoa       = $idPessoa, 
                idColab        = $idColab, 
                login          = '$login', 
                ativo          = $kativo , 
                dcCIPA         = $checkCipa,
                dcBrigada      = $checkBrigada,
                chaveApp       = '$chaveApp', 
                idSubSede      = $idSubSede
                WHERE idUsuario = $idUsuario";
}

try {
    $stmt = $conn->prepare($sql);
    if ($stmt->execute()) {
        $retorno = "Alteração bem sucedida! ";
        //
        //- Tratamento do arquivo da foto
        //
        if (! empty($_FILES["foto"]["name"])) {
            //
            //- apaga foto antiga
            //
            if (! empty($antigo['foto'])) {
                $caminhoDestino = "../fotos/" . $antigo['foto'];
                if (file_exists($caminhoDestino)) {
                    unlink($caminhoDestino);
                }
            }
            //
            //- sobe a nova foto
            $nomeTemporario = $_FILES["foto"]["tmp_name"];
            $nomeArquivo = $_FILES["foto"]["name"];
            $extensao = pathinfo($nomeArquivo, PATHINFO_EXTENSION);
            $novoNome = "usu_" . str_pad($idUsuario, 6, "0", STR_PAD_LEFT) . "." . $extensao;
            $caminhoDestino = "../fotos/" . $novoNome;
            //
            //
            if (move_uploaded_file($nomeTemporario, $caminhoDestino)) {
                // O arquivo foi movido com sucesso, você pode continuar o processamento aqui
                $sql = "UPDATE rh_usuarios SET foto = '$novoNome' WHERE idUsuario = $idUsuario";
                $stmt = $conn->prepare($sql);
                $result = $stmt->execute();

                // Envie uma resposta JSON de sucesso
                $status  = true;
                $retorno .= "Foto Reg. c/sucesso.";
            } else {
                // O arquivo não pôde ser movido
                $status  = false;
                $retorno .= "Erro ao mover a foto";
            }
        }
        //
        f_log("ALT", "Alterou Usuário: $dadosAntigos", "usuarios", $idModulo, $idUsuario);
        $conn = null;
        die(json_encode(["status" => true, "msg" => $retorno]));
    }
    //
} catch (PDOException $e) {
    echo "Erro: " . $e->getMessage();
    $errorInfo = $stmt->errorInfo(); // Obtém informações do erro
    $retorno = "Sinto muito, deu erro ao atualizar! Detalhes: " . $errorInfo[2];
    echo json_encode(["status" => false, "msg" => "$retorno"]);
}

$conn = null;
