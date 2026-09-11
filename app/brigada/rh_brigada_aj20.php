<?PHP
//
//- rh_brigada_aj20.php | Grava Inclusão de AÇÃO de Brigada
// (C)haia, 30/06/2025

session_start();

$idModulo = 10; // Brigada

$dados = filter_input_array(INPUT_POST, FILTER_DEFAULT);

if( $dados ){
    extract($dados);
    $stringDados = json_encode($dados, JSON_UNESCAPED_UNICODE);
    //
    include_once "../includes/conexao_gerar.php";
    include_once "../includes/f_logs.php";
    include_once "../includes/f_linha_do_tempo.php";
    //
    $criado_por = $_SESSION['nmLogin'];
    $idLogin = $_SESSION['idLogin'];
    $idSubSede = $_SESSION['idSubSede'] ?? 0; // Pega a SubSede do usuário logado
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
/*
 rh_brigada_aj20.php | 2025-06-30 14:25:19 
{
    "data_acao": "2025-06-30",
    "assunto": "teste",
    "observacoes": "<p>teste<\/p>",
    "idBrigadista": [
        "12",
        "9",
        "10"
    ]
}
*/
// die( json_encode(["status" => true, "msg" => "<div class='alert alert-primary'>Teste de inclusão de REUNIÃO</div>"]));

$ids = $_POST['idBrigadista'] ?? []; // Garante que seja array
$nomes = [];

if (!empty($ids) && is_array($ids)) {
    $sql = "SELECT p.nome 
            FROM RH.rh_brigadistas b
            INNER JOIN rh_pessoas p ON p.idPessoa = b.idPessoa
            WHERE b.id = ?";

    $stmt = $conn->prepare($sql);

    foreach ($ids as $id) {
        $stmt->execute([$id]);
        $nome = $stmt->fetchColumn();
        if ($nome) {
            $nomes[] = $nome;
        }
    }

    // Cria a string com os nomes separados por vírgula
    $listaNomes = implode(", ", $nomes);
}

try {
    $sql = "INSERT INTO rh_brigada_acoes (idSubSede, data_acao, assunto, observacoes, criado_por, idLogin) 
                        VALUES (:idSubSede, :data_acao, :assunto, :observacoes, :criado_por, :idLogin)";
    $stmt = $conn->prepare($sql);
    $stmt->bindParam(':idSubSede', $idSubSede, PDO::PARAM_INT );
    $stmt->bindParam(':data_acao', $data_acao, PDO::PARAM_STR );
    $stmt->bindParam(':assunto', $assunto, PDO::PARAM_STR );
    $stmt->bindParam(':observacoes', $observacoes, PDO::PARAM_STR );
    $stmt->bindParam(':criado_por', $criado_por, PDO::PARAM_STR );
    $stmt->bindParam(':idLogin', $idLogin, PDO::PARAM_STR );

    $result = $stmt->execute();
    $idAcao = $conn->lastInsertId();

    if ($result) {
        // Inserção bem-sucedida

        f_log("INC", "Incluiu Ação de Brigada: $stringDados", "rh_brigada_acoes", $idModulo, $idAcao);
        $retorno = "<div class='alert alert-success'>Inclusão bem-sucedida!</div>";
        $status = true;
        
        //
        //- INSERIR PARTICIPANETES DA REUNIÃO
        //
            $presente = 1; // Presente por padrão
            foreach ($ids as $idBrigadista) {
                // Verifica se o idBrigadista é válido
                if (is_numeric($idBrigadista) && $idBrigadista > 0) {
                    // Prepara a inserção para cada brigadista
                    $sql = "INSERT INTO rh_brigada_acoes_membros (idAcao, idBrigadista, presente) 
                            VALUES (:idAcao, :idBrigadista, :presente)";
                    $stmt = $conn->prepare($sql);
                    $stmt->bindParam(':idAcao', $idAcao, PDO::PARAM_INT);
                    $stmt->bindParam(':idBrigadista', $idBrigadista, PDO::PARAM_INT);
                    $stmt->bindParam(':presente', $presente, PDO::PARAM_INT);
                    
                    if (!$stmt->execute()) {
                        // Se falhar, registra o erro e continua com os outros
                        f_log("ERR", "Erro ao inserir membro na ação: " . implode(", ", $stmt->errorInfo()), "rh_brigada_acao_membros", $idModulo, $idAcao);
                    }
                }
            }   
        
        //
        //- INSERe REGISTRO NA LINHA DO TEMPO DE CADA BRIGADISTA
        //
            foreach ($ids as $idBrigadista) {
                // Verifica se o idBrigadista é válido
                if (is_numeric($idBrigadista) && $idBrigadista > 0) {
                    //
                    //- recupera idPessoa do brigadista
                    //
                    $sqlPessoa = "SELECT idPessoa FROM rh_brigadistas WHERE id = :idBrigadista";
                    $stmtPessoa = $conn->prepare($sqlPessoa);
                    $stmtPessoa->bindParam(':idBrigadista', $idBrigadista, PDO::PARAM_INT);
                    $stmtPessoa->execute();
                    $dados = $stmtPessoa->fetch(PDO::FETCH_ASSOC);
                    if (!$dados) {
                        // Se não encontrar o brigadista, continua para o próximo
                        continue;
                    }

                    $idPessoa = $dados['idPessoa'];
                    $tipo = 33; // Evento de Brigada
                    $descricao = "Participou da Ação de Brigada: $assunto em $data_acao";
                    f_ldt( $tipo, $idPessoa, $descricao, $idAcao);
                }
            }
        //
        //- INSERE O ARQUIVO NO DIRETÓRIO PADRÃO PARA ARQUIVOS DA BRIGADA
        //
            // Diretório onde o arquivo será salvo
            $dirDestino = "../docs/brigada"; // Ajuste o caminho conforme necessário

            // Verifica se o diretório existe, senão cria
            if (!is_dir($dirDestino)) {
                mkdir($dirDestino, 0755, true);
            }

            // Verifica se o arquivo foi enviado
            if (isset($_FILES['acao_arquivo']) && $_FILES['acao_arquivo']['error'] === UPLOAD_ERR_OK) {
                $arquivoTmp = $_FILES['acao_arquivo']['tmp_name'];
                $nomeOriginal = basename($_FILES['acao_arquivo']['name']);

                // Gera nome seguro (com data e hash para evitar duplicidade)
                $ext = pathinfo($nomeOriginal, PATHINFO_EXTENSION);
                $nomeSeguro = 'acao_' . date('Ymd_His') . '_' . uniqid() . '.' . strtolower($ext);

                $caminhoFinal = $dirDestino . '/' . $nomeSeguro;

                // Move o arquivo
                if (move_uploaded_file($arquivoTmp, $caminhoFinal)) {
                    // Arquivo salvo com sucesso
                    $acao_arquivo_nome = $nomeSeguro; // salvar esse valor no banco
                } else {
                    die(json_encode(["status" => false, "msg" => "<div class='alert alert-danger'>Erro ao mover o arquivo enviado.</div>"]));
                }
                //
                // ATUALIZA o nome do arquivo na tabela de reuniões
                //
                    $sqlUpdate = "UPDATE rh_brigada_acoes SET acao_arquivo = :acao_arquivo WHERE id = :idAcao";
                    $stmtUpdate = $conn->prepare($sqlUpdate);
                    $stmtUpdate->bindParam(':acao_arquivo', $nomeSeguro, PDO::PARAM_STR);
                    $stmtUpdate->bindParam(':idAcao', $idAcao, PDO::PARAM_INT);
                    $stmtUpdate->execute();
            } else {
                $acao_arquivo_nome = null; // Nenhum arquivo enviado
            }

    } else {
        // Ocorreu um erro durante a inserção
        $retorno = "<div class='alert alert-danger'>Erro ao inserir Ação de Brigada.</div>";
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