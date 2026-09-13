<?PHP
//
// rh_brigada_aj22.php | SALVA ALTERAÇÃO de AÇÃO de Brigada
// (C)haia, 27/06/2025
//

session_start();

$idModulo = 10; // Brigada

if (!isset($_SESSION['idLogin']) || (empty($_SESSION['dcBrigada']) && (int) ($_SESSION['idGrupo'] ?? 0) !== 9)) {
    header('location: ../logout.php');
    exit();
}else{
    include_once "../includes/conexao_gerar.php";
    include_once "../includes/f_logs.php";
    include_once "../includes/f_linha_do_tempo.php";
    include_once "../includes/f_upload_seguro.php";
    //
    $idLogin = $_SESSION['idLogin'];
    $nmLogin = $_SESSION['nmLogin'];
    $idEmpresa = $_SESSION['idEmpresa'];
}

$dados = filter_input_array(INPUT_POST, FILTER_DEFAULT);
if( $dados ){
    extract($dados);
    // reafirma identidade da sessão depois do extract() — POST não deve conseguir sobrescrever
    $idLogin = $_SESSION['idLogin'];
    $nmLogin = $_SESSION['nmLogin'];
    $idEmpresa = $_SESSION['idEmpresa'];
    $strDados = json_encode($dados, JSON_UNESCAPED_UNICODE);
} else{
    die(json_encode(["status" => false, "msg" => "Faltaram Parâmetros!"]));
}

//
//- RECUPERA dados antes da ALTERAÇÃO
//

$sql = "SELECT 
            A.*,
            S.identificador as dsSubSede,

            -- Lista de nomes dos participantes
            (
                SELECT GROUP_CONCAT(P.nome ORDER BY P.nome SEPARATOR ', ')
                FROM rh_brigada_acoes_membros M
                INNER JOIN rh_brigadistas B ON B.id = M.idBrigadista
                INNER JOIN rh_pessoas P ON P.idPessoa = B.idPessoa
                WHERE M.idAcao = A.id AND M.presente = 1
            ) AS nomesParticipantes,

            -- Lista de IDs dos brigadistas participantes
            (
                SELECT GROUP_CONCAT(M.idBrigadista ORDER BY M.idBrigadista SEPARATOR ',')
                FROM rh_brigada_acoes_membros M
                WHERE M.idAcao = A.id AND M.presente = 1
            ) AS idParticipantes

        FROM rh_brigada_acoes A
        INNER JOIN rh_subsedes S ON S.subsede_id = A.idSubSede
        WHERE A.id = :id";

$stmt = $conn->prepare($sql);
$stmt->execute(['id' => $idAcaoAlt]); // Substitua $idCargo pelo valor desejado
$dadosOld = $stmt->fetch(PDO::FETCH_ASSOC); // Obtém os dados como um array associativo
$arquivo_anterior = $dadosOld['acao_arquivo'];
$strDadosOld = json_encode($dadosOld, JSON_UNESCAPED_UNICODE);

$ids = $_POST['idBrigadista'] ?? []; // Garante que seja array

try {
    //
    //- ALTERA os dados da AÇÃO
    //

    $sql = "UPDATE rh_brigada_acoes 
            SET  
                data_acao = :data_acao, 
                assunto = :assunto, 
                observacoes = :observacoes 
            WHERE id = :idAcaoAlt";  

    $stmt = $conn->prepare($sql);
    $stmt->bindParam(':data_acao', $data_acao, PDO::PARAM_STR );
    $stmt->bindParam(':assunto', $assunto, PDO::PARAM_STR );
    $stmt->bindParam(':observacoes', $observacoes, PDO::PARAM_STR );
    $stmt->bindParam(':idAcaoAlt', $idAcaoAlt, PDO::PARAM_INT );

    $result = $stmt->execute();

    if ($result) {
        // Inserção bem-sucedida

        f_log("ALT", "Alterou AÇÃO de Brigada: Antes: ($strDadosOld) | Depois: ($strDados)", "rh_brigada_acoes", $idModulo, $idAcaoAlt);
        $retorno = "<div class='alert alert-success'>Alteração bem-sucedida!</div>";
        $status = true;
        
        //
        //- INSERIR PARTICIPANTES DA AÇÃO
        //
            //
            //- EXCLUI OS MEMBROS DA AÇÃO
            //
                $sql = "DELETE FROM rh_brigada_acoes_membros WHERE idAcao = :idAcaoAlt";
                $stmt = $conn->prepare($sql);
                $stmt->execute(['idAcaoAlt' => $idAcaoAlt]);
            //        
            $presente = 1; // Presente por padrão
            foreach ($ids as $idBrigadista) {
                // Verifica se o idBrigadista é válido
                if (is_numeric($idBrigadista) && $idBrigadista > 0) {
                    // Prepara a inserção para cada brigadista
                    $sql = "INSERT INTO rh_brigada_acoes_membros (idAcao, idBrigadista, presente) 
                            VALUES (:idAcao, :idBrigadista, :presente)";
                    $stmt = $conn->prepare($sql);
                    $stmt->bindParam(':idAcao', $idAcaoAlt, PDO::PARAM_INT);
                    $stmt->bindParam(':idBrigadista', $idBrigadista, PDO::PARAM_INT);
                    $stmt->bindParam(':presente', $presente, PDO::PARAM_INT);
                    
                    if (!$stmt->execute()) {
                        // Se falhar, registra o erro e continua com os outros
                        f_log("ERR", "Erro ao inserir membro na AÇÃO: " . implode(", ", $stmt->errorInfo()), "rh_brigada_acoes_membros", $idModulo, $idAcao);
                    }
                }
            }   
        
        //
        //- INSERe REGISTRO NA LINHA DO TEMPO DE CADA BRIGADISTA
        //
            //
            //- EXCLUI LINHA DE TEMPO das PESSOAS
            //
                $sql = "DELETE FROM rh_pessoas_ldt WHERE idAcaoTipo = 33 and idOrigem = :idAcaoAlt";
                $stmt = $conn->prepare($sql);
                $stmt->execute(['idAcaoAlt' => $idAcaoAlt]);
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
                    $descricao = "Participou em $data_acao da AÇÃO de Brigada, assunto: $assunto";
                    f_ldt( $tipo, $idPessoa, $descricao, $idAcaoAlt, $data_acao);
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
                $validacao = upload_seguro_validar($_FILES['acao_arquivo'], ['pdf', 'jpg', 'jpeg', 'png', 'gif', 'webp', 'bmp']);
                if ($validacao !== true) {
                    $conn = null;
                    die(json_encode(["status" => false, "msg" => $validacao]));
                }
                //
                //- ELIMINA ARQUIVO ANTERIOR
                //
                    if( !empty( $arquivo_anterior ))
                    {
                        $caminhoArquivo = "../docs/brigada/" . basename($arquivo_anterior);
                        if (file_exists($caminhoArquivo)) {
                            unlink($caminhoArquivo); // Exclui o arquivo do servidor
                        }
                    }
                //
                //- SALVA NOVO ARQUIVO
                //
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
                // ATUALIZA o nome do arquivo na tabela de Ações
                //
                    $sqlUpdate = "UPDATE rh_brigada_acoes SET acao_arquivo = :acao_arquivo WHERE id = :idAcao";
                    $stmtUpdate = $conn->prepare($sqlUpdate);
                    $stmtUpdate->bindParam(':acao_arquivo', $nomeSeguro, PDO::PARAM_STR);
                    $stmtUpdate->bindParam(':idAcao', $idAcaoAlt, PDO::PARAM_INT);
                    $stmtUpdate->execute();
            } else {
                $acao_arquivo_nome = null; // Nenhum arquivo enviado
            }

    } else {
        // Ocorreu um erro durante a inserção
        $retorno = "<div class='alert alert-danger'>Erro ao inserir AÇÃO de Brigada.</div>";
        $status = false;
        echo json_encode(["status" => $status, "msg" => $retorno]);
        $conn = null;
        die();
    }
} catch (PDOException $e) {

    $retorno = "Erro no banco de dados: " . $e->getMessage() . 
               " na linha " . $e->getLine() . 
               " do arquivo " . $e->getFile();

    // Captura e trata exceções do PDO (erros no banco de dados)
    $status = false;
    echo json_encode(["status" => $status, "msg" => $retorno]);
    $conn = null;
    die();
}

echo json_encode(["status" => $status, "msg" => $retorno]);
$conn = null;
exit();
