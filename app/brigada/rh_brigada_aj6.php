<?PHP
//
//- rh_brigada_aj3.php | Grava ALTERAÇÃO de Membro de Brigada
// (C)haia, 17/06/2025

session_start();
if (!isset($_SESSION['idLogin']) || (empty($_SESSION['dcBrigada']) && (int) ($_SESSION['idGrupo'] ?? 0) !== 9)) {
    http_response_code(403);
    die(json_encode(["status" => false, "msg" => "Acesso negado."]));
}


$idModulo = 10; // Brigada

$dados = filter_input_array(INPUT_POST, FILTER_DEFAULT);
if ($dados) {
    extract($dados);
    //
    include_once "../includes/conexao_gerar.php";
    include_once "../includes/f_logs.php";
    include_once "../includes/f_linha_do_tempo.php";
    include_once "../includes/f_upload_seguro.php";
    //
    $criado_por = $_SESSION['nmLogin'];
    $idLogin = $_SESSION['idLogin'];
    //    
} else {
    die(json_encode(["status" => false, "msg" => "Faltaram Parâmetros!"]));
}

/*
include_once "../includes/debug.php";
debug( json_encode($dados, JSON_PRETTY_PRINT));
echo json_encode(["status" => true, "msg" => "Teste de inclusão de usuário"]);
$conn = null;
die;
/*
 rh_brigada_aj6.php | 2025-06-18 17:26:27 
{
    "idMembro": "6",
    "nmPessoa": "Josiane De Oliveira",
    "idPessoa": "53",
    "dtInicio": "2012-12-12",
    "dtFinal": "2014-12-12",
    "idCargo": "1",
    "idSubSede": "107"
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
    $sql = "SELECT B.*, P.nome 
                FROM rh_brigadistas B
                INNER JOIN rh_pessoas as P on P.idPessoa = B.idPessoa
                where B.id = :idMembro";
    $stmt = $conn->prepare($sql);
    $stmt->bindParam(':idMembro', $idMembro, PDO::PARAM_INT);
    $stmt->execute();
    $dadosAntigos = $stmt->fetch(PDO::FETCH_ASSOC);
    //
    $arquivoFoto = $dadosAntigos['foto'];
    $nmPessoa = $dadosAntigos['nome'];
    //
    if ($dados) {
        // Cria uma string com os valores separados por vírgula
        $stringDados = implode(", ", $dados);
    } else {
        $stringDados = "Nenhum dado encontrado.";
    }

    $sql = "UPDATE rh_brigadistas SET 
            data_inicio = :data_inicio,
            data_final = :data_final,
            idCargoBrigada = :idCargoBrigada,
            idPessoa = :idPessoa,
            idSubSede = :idSubSede
        WHERE id = :idMembro";
    $stmt = $conn->prepare($sql);
    
    $stmt->bindParam(':data_inicio',    $dtInicioStr, PDO::PARAM_STR);
    $stmt->bindParam(':data_final',     $dtFinalStr,  PDO::PARAM_STR);
    $stmt->bindParam(':idCargoBrigada', $idCargo,     PDO::PARAM_INT);
    $stmt->bindParam(':idPessoa',       $idPessoa,    PDO::PARAM_INT);
    $stmt->bindParam(':idSubSede',      $idSubSede,   PDO::PARAM_INT);
    $stmt->bindParam(':idMembro',       $idMembro,    PDO::PARAM_INT);
    $result = $stmt->execute();

    if ($result) {
        // Atualização bem-sucedida

        if (isset($_FILES['fotoAlt']) && $_FILES['fotoAlt']['error'] === UPLOAD_ERR_OK) {
            $validacao = upload_seguro_validar($_FILES['fotoAlt'], ['jpg', 'jpeg', 'png', 'gif', 'webp', 'bmp']);
            if ($validacao !== true) {
                $conn = null;
                die(json_encode(["status" => false, "msg" => $validacao]));
            }
            //
            $fotoTmp = $_FILES['fotoAlt']['tmp_name'];
            $ext = pathinfo($_FILES['fotoAlt']['name'], PATHINFO_EXTENSION);
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
                //
                // Exclui a foto antiga, se existir
                //
                    $caminhoFoto = "../docs/brigada/" . $arquivoFoto;
                    if (file_exists($caminhoFoto)) {
                        if (unlink($caminhoFoto)) {
                            f_log("EXC", "Foto de Membro de Brigada ($nmPessoa) excluída: $arquivoFoto", "rh_brigadistas", $idModulo, $idMembro);
                        } else {
                            f_log("ERR", "Erro ao excluir foto de Membro de Brigada ($nmPessoa): $arquivoFoto", "rh_brigadistas", $idModulo, $idMembro);
                        }
                    } else {
                        f_log("ERR", "Arquivo de foto ($nmPessoa) não encontrado: $caminhoFoto", "rh_brigadistas", $idModulo, $idMembro);
                    }                 
            } else {
                $retorno .= " (Atenção: não foi possível salvar a foto)";
            }
        }

        f_log("ALT", "Alteração de  Membro de Brigada | Dados anteriores: $stringDados", "rh_brigadistas", $idModulo, $idMembro);
        $retorno = "Alteração bem-sucedida!";
        $status = true;
        //
        $idPessoa = $dados['idPessoa'];
        $tipo = 32; // Brigada: dados atualizados
        $descricao = "Alterado Membro de Brigada";
        f_ldt($tipo, $idPessoa, $descricao);
        //          
    } else {
        // Ocorreu um erro durante a alteração
        $retorno = "Erro ao alterar Membro de Brigada.";
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
