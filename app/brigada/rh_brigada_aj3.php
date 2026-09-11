<?PHP
//
//- rh_brigada_aj3.php | Grava Inclusão de Membro de Brigada
// (C)haia, 17/06/2025

session_start();

$idModulo = 10; // Brigada

$dados = filter_input_array(INPUT_POST, FILTER_DEFAULT);
if( $dados ){
    extract($dados);
    $stringDados = implode(", ", $dados);
    //
    include_once "../includes/conexao_gerar.php";
    include_once "../includes/f_logs.php";
    include_once "../includes/f_linha_do_tempo.php";
    //
    $criado_por = $_SESSION['nmLogin'];
    $idLogin = $_SESSION['idLogin'];
    //    
}else{
    die(json_encode(["status" => false, "msg" => "Faltaram Parâmetros!"]));
}

/*
include_once "../includes/debug.php";
debug( json_encode($dados, JSON_PRETTY_PRINT));
echo json_encode(["status" => true, "msg" => "Teste de inclusão de usuário"]);
$conn = null;
die;
 rh_brigada_aj3.php | 2025-06-17 11:41:58 
{
    "nmPessoa": "Morgan Freeman",
    "idPessoa": "88",
    "dtInicio": "2020-01-01",
    "dtFinal": "",
    "idCargo": "1",
    "idSubSede": "101"
}
*/

if (empty($dados['idPessoa']) || intval($dados['idPessoa']) <= 0) {
    die(json_encode(["status" => false, "msg" => "É necessário selecionar a Pessoa!"]));
}

if (empty($dados['idSubSede']) || intval($dados['idSubSede']) <= 0) {
    die(json_encode(["status" => false, "msg" => "É necessário informar a SubSede!"]));
}

if (empty($dados['idCargo']) || intval($dados['idCargo']) <= 0) {
    die(json_encode(["status" => false, "msg" => "É necessário informar o Cargo!"]));
}

if (empty($dados['dtInicio'])) {
    die(json_encode(["status" => false, "msg" => "Informe a Data de Início!"]));
}

$dtInicioStr = $dados['dtInicio'];
$dtFinalStr = !empty($dados['dtFinal']) ? $dados['dtFinal'] : null;

// Se dtFinal vier preenchida, deve ser maior ou igual à dtInicio
if (!empty($dados['dtFinal'])) {
    $dtInicio = strtotime($dados['dtInicio']);
    $dtFinal  = strtotime($dados['dtFinal']);

    if ($dtFinal < $dtInicio) {
        die(json_encode(["status" => false, "msg" => "A Data Final não pode ser anterior à Data de Início!"]));
    }
}

try {
    $sql = "INSERT INTO rh_brigadistas (idPessoa, data_inicio, data_final, idCargoBrigada, idSubSede, criado_por, idLogin) 
                        VALUES ( :idPessoa, :data_inicio, :data_final, :idCargoBrigada, :idSubSede, :criado_por, :idLogin)";
    $stmt = $conn->prepare($sql);
    $stmt->bindParam(':idPessoa',       $idPessoa,    PDO::PARAM_INT );
    $stmt->bindParam(':data_inicio',    $dtInicioStr, PDO::PARAM_STR );
    $stmt->bindParam(':data_final',     $dtFinalStr,  PDO::PARAM_STR );
    $stmt->bindParam(':idCargoBrigada', $idCargo,     PDO::PARAM_INT );
    $stmt->bindParam(':idSubSede',      $idSubSede,   PDO::PARAM_INT );
    $stmt->bindParam(':criado_por',     $criado_por,  PDO::PARAM_STR );
    $stmt->bindParam(':idLogin',        $idLogin,     PDO::PARAM_INT );
    $result = $stmt->execute();
    $idMembro = $conn->lastInsertId();

    if ($result) {
        // Inserção bem-sucedida
        // Se houver foto, trata o upload
        if (isset($_FILES['foto']) && $_FILES['foto']['error'] === UPLOAD_ERR_OK) {
            $fotoTmp = $_FILES['foto']['tmp_name'];
            $ext = pathinfo($_FILES['foto']['name'], PATHINFO_EXTENSION);
            $ext = strtolower($ext);
            $nomeArquivo = "perfil_" . $idMembro . "_" . date("Ymd_His") . "." . $ext;
            $destino = "../docs/brigada/" . $nomeArquivo;

            if (!is_dir("../docs/brigada")) {
                mkdir("../docs/brigada", 0777, true);
            }

            if (move_uploaded_file($fotoTmp, $destino)) {
                // Atualiza a tabela com o nome da foto
                $sqlFoto = "UPDATE rh_brigadistas SET foto = :foto WHERE id = :id";
                $stmtFoto = $conn->prepare($sqlFoto);
                $stmtFoto->bindParam(':foto', $nomeArquivo);
                $stmtFoto->bindParam(':id', $idMembro, PDO::PARAM_INT);
                $stmtFoto->execute();
            } else {
                $retorno .= " (Atenção: não foi possível salvar a foto)";
            }
        }

        f_log("INC", "Incluiu Membro de Brigada: $stringDados", "rh_brigadistas", $idModulo, $idMembro);
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
        $retorno = "Erro ao inserir Membro de Brigada.";
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



echo json_encode(["status" => $status, "msg" => $retorno]);
$conn = null;