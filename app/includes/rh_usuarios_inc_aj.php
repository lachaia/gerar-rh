<?PHP
//- g_usuarios_inc_aj.php
//- Grava dados de Inclusão da Modal Inclui Usuários (g_usuarios.php)
// (C)haia, 01/03/2025

session_start();

if (!isset($_SESSION['idLogin'])) {
    header('Location: ../logout.php');
    exit();
} else {
    $criado_por = $_SESSION['nmLogin'];
}
include_once "../includes/conexao_gerar.php";
include_once "../includes/f_logs.php";
include_once "../includes/f_linha_do_tempo.php";
include_once "../includes/f_upload_seguro.php";
//

$dados = filter_input_array(INPUT_POST, FILTER_DEFAULT);
if( $dados ) extract($dados);

if( empty( $dados['idUsuarioGrupo'] ) ){
    die( json_encode(["status" => false, "msg" => "É necessário selecionar o Grupo de usuários!"]) );
}
if( empty( $dados['idEmpresa'] ) ){
    die( json_encode(["status" => false, "msg" => "É necessário selecionar a Organização!"]) );
}
if( empty( $dados['idPessoa'] ) ){
    die( json_encode(["status" => false, "msg" => "É necessário selecionar a Pessoa do usuário!"]) );
}
if( empty( $dados['login'] ) ){
    die( json_encode(["status" => false, "msg" => "É necessário informar o Login do usuário!"]) );
}
if( empty( $dados['senha'] ) ){
    die( json_encode(["status" => false, "msg" => "É necessário informar a Senha de Acesso!"]) );
}
if( empty( $dados['idSubSede'] ) ){
    die( json_encode(["status" => false, "msg" => "É necessário informar a SubSede do Usuário!"]) );
}

if( empty($chkBrigada)) $chkBrigada = 0;
if( empty($chkCipa)) $chkCipa = 0;

//
//- Verifica se Login já existe
//
    $sql = "SELECT idUsuario FROM rh_usuarios WHERE login like :login";
    $stmt = $conn->prepare( $sql );
    $stmt->bindParam( 'login', $dados['login'], PDO::PARAM_STR );
    $result = $stmt->execute();
    if($stmt->rowCount()>0){
        die( json_encode(["status" => false, "msg" => "Usuário Já existe, tente outro!"]) );
    }

$ksenha = password_hash( $dados['senha'], PASSWORD_DEFAULT );

try {
    $sql = "INSERT INTO rh_usuarios (idEmpresa, idUsuarioGrupo, idPessoa, idColab, login,  senha, ativo, foto, chaveApp, idSubSede, dcCIPA, dcBrigada, criado_por) 
                        VALUES ( :idEmpresa, :idUsuarioGrupo, :idPessoa, :idColab, :login, :ksenha, 1, 'perfil.png', :chaveApp, :idSubSede, :dcCIPA, :dcBrigada, :criado_por)";

    $stmt = $conn->prepare($sql);
    $stmt->bindParam(':idUsuarioGrupo', $dados['idUsuarioGrupo'], PDO::PARAM_INT );
    $stmt->bindParam(':idSubSede',      $dados['idSubSede'],      PDO::PARAM_INT );
    $stmt->bindParam(':idPessoa',       $dados['idPessoa'],       PDO::PARAM_INT );
    $stmt->bindParam(':idColab',        $dados['idColab'],        PDO::PARAM_INT );
    $stmt->bindParam(':login',          $dados['login'],          PDO::PARAM_STR );
    $stmt->bindParam(':ksenha',         $ksenha,                  PDO::PARAM_STR );
    $stmt->bindParam(':chaveApp',       $dados['chaveApp'],       PDO::PARAM_STR );
    $stmt->bindValue(':idEmpresa',      $dados['idEmpresa'],      PDO::PARAM_INT );
    $stmt->bindValue(':dcCIPA',         $chkCipa,                 PDO::PARAM_INT );
    $stmt->bindValue(':dcBrigada',      $chkBrigada,              PDO::PARAM_INT );
    $stmt->bindValue(':criado_por',     $criado_por,              PDO::PARAM_STR );
    $result = $stmt->execute();  
    $idUsuario = $conn->lastInsertId();

    if ($result) {
        // Inserção bem-sucedida
        f_log("INC", "Incluiu usuário " . $dados['login'], "usuarios", 0, $idUsuario);
        $retorno = "Inclusão bem-sucedida!";
        $status = true;
        //
        $idPessoa = $dados['idPessoa'];
        $tipo = 7; // Usuário Criado
        $descricao = "Criado Usuário de Sistema";
        f_ldt( $tipo, $idPessoa, $descricao);
        //          
    } else {
        // Ocorreu um erro durante a inserção
        $retorno = "Erro ao inserir usuário.";
        $status = false;
        echo json_encode(["status" => $status, "msg" => $retorno]);
        $conn = null;
        die();
    }
} catch (PDOException $e) {
    // Captura e trata exceções do PDO (erros no banco de dados)
    $retorno = "Erro no banco de dados: " . $e->getMessage();
    $status = false;
    echo json_encode(["status" => $status, "msg" => $retorno]);
    $conn = null;
    die();
}

if (isset($_FILES["foto"]) && $_FILES["foto"]["error"] == UPLOAD_ERR_OK) {
    $validacao = upload_seguro_validar($_FILES['foto'], ['jpg', 'jpeg', 'png', 'gif', 'webp', 'bmp']);
    if ($validacao !== true) {
        $conn = null;
        die(json_encode(["status" => false, "msg" => $validacao]));
    }
    //
    $nomeTemporario = $_FILES["foto"]["tmp_name"];
    $nomeArquivo = $_FILES["foto"]["name"];
    $extensao = strtolower(pathinfo($nomeArquivo,PATHINFO_EXTENSION));
    $novoNome = "usu_" . str_pad($idUsuario, 6, "0", STR_PAD_LEFT) . "." . $extensao;
    $caminhoDestino = "../fotos/" . $novoNome;
    //
    if (file_exists($caminhoDestino)) {
        unlink( $caminhoDestino );
    }
    //
    if (move_uploaded_file($nomeTemporario, $caminhoDestino)) {
        // O arquivo foi movido com sucesso, você pode continuar o processamento aqui
        $sql = "UPDATE rh_usuarios SET foto = :foto WHERE idUsuario = :idUsuario";
        $stmt = $conn->prepare( $sql );
        $stmt->bindParam(':foto', $novoNome);
        $stmt->bindParam(':idUsuario', $idUsuario);
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

echo json_encode(["status" => $status, "msg" => $retorno]);
$conn = null;