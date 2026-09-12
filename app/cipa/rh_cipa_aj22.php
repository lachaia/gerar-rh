<?PHP
//
// rh_cipa_aj22.php | SALVA ALTERAÇÃO de AÇÃO de CIPA
// (C)haia, 27/06/2025
//

session_start();

$idModulo = 11; // CIPA

if (!isset($_SESSION['idLogin'])) {
    header('location: ../logout.php');
    exit();
}else{
    include_once "../includes/conexao_gerar.php";
    include_once "../includes/f_logs.php";
    include_once "../includes/f_linha_do_tempo.php";
    include_once "../includes/f_ocr.php";
    //include_once "../includes/debug.php";
    //
    $idLogin = $_SESSION['idLogin'];
    $nmLogin = $_SESSION['nmLogin'];
    $idEmpresa = $_SESSION['idEmpresa'];
}

$dados = filter_input_array(INPUT_POST, FILTER_DEFAULT);
if( $dados ){
    extract($dados);
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
                FROM rh_cipa_acoes_membros M
                INNER JOIN rh_cipeiros B ON B.id = M.idCipeiro
                INNER JOIN rh_pessoas P ON P.idPessoa = B.idPessoa
                WHERE M.idAcao = A.id AND M.presente = 1
            ) AS nomesParticipantes,

            -- Lista de IDs dos cipeiros participantes
            (
                SELECT GROUP_CONCAT(M.idCipeiro ORDER BY M.idCipeiro SEPARATOR ',')
                FROM rh_cipa_acoes_membros M
                WHERE M.idAcao = A.id AND M.presente = 1
            ) AS idParticipantes

        FROM rh_cipa_acoes A
        INNER JOIN rh_subsedes S ON S.subsede_id = A.idSubSede
        WHERE A.id = :id";

$stmt = $conn->prepare($sql);
$stmt->execute(['id' => $idAcaoAlt]); // Substitua $idCargo pelo valor desejado
$dadosOld = $stmt->fetch(PDO::FETCH_ASSOC); // Obtém os dados como um array associativo
$arquivo_anterior = $dadosOld['acao_arquivo'];
$strDadosOld = json_encode($dadosOld, JSON_UNESCAPED_UNICODE);

$ids = $_POST['idCipeiro'] ?? []; // Garante que seja array

try {
    //
    //- ALTERA os dados da AÇÃO
    //

    $sql = "UPDATE rh_cipa_acoes 
            SET  
                data_acao = :data_acao, 
                idSubSede = :idSubSede, 
                assunto = :assunto, 
                observacoes = :observacoes 
            WHERE id = :idAcaoAlt";  

    $stmt = $conn->prepare($sql);
    $stmt->bindParam(':data_acao', $data_acao, PDO::PARAM_STR );
    $stmt->bindParam(':assunto', $assunto, PDO::PARAM_STR );
    $stmt->bindParam(':observacoes', $observacoes, PDO::PARAM_STR );
    $stmt->bindParam(':idAcaoAlt', $idAcaoAlt, PDO::PARAM_INT );
    $stmt->bindParam(':idSubSede', $idSubSede, PDO::PARAM_INT );

    $result = $stmt->execute();

    if ($result) {
        // Inserção bem-sucedida

        f_log("ALT", "Alterou AÇÃO de CIPA: Antes: ($strDadosOld) | Depois: ($strDados)", "rh_cipa_acoes", $idModulo, $idAcaoAlt);
        $retorno = "<div class='alert alert-success'>Alteração bem-sucedida!</div>";
        $status = true;
        
        //
        //- INSERIR PARTICIPANTES DA AÇÃO
        //
            //
            //- EXCLUI OS MEMBROS DA AÇÃO
            //
                $sql = "DELETE FROM rh_cipa_acoes_membros WHERE idAcao = :idAcaoAlt";
                $stmt = $conn->prepare($sql);
                $stmt->execute(['idAcaoAlt' => $idAcaoAlt]);
            //        
            $presente = 1; // Presente por padrão
            foreach ($ids as $idCipeiro) {
                // Verifica se o idCipeiro é válido
                if (is_numeric($idCipeiro) && $idCipeiro > 0) {
                    // Prepara a inserção para cada cipeiro
                    $sql = "INSERT INTO rh_cipa_acoes_membros (idAcao, idCipeiro, presente) 
                            VALUES (:idAcao, :idCipeiro, :presente)";
                    $stmt = $conn->prepare($sql);
                    $stmt->bindParam(':idAcao', $idAcaoAlt, PDO::PARAM_INT);
                    $stmt->bindParam(':idCipeiro', $idCipeiro, PDO::PARAM_INT);
                    $stmt->bindParam(':presente', $presente, PDO::PARAM_INT);
                    
                    if (!$stmt->execute()) {
                        // Se falhar, registra o erro e continua com os outros
                        f_log("ERR", "Erro ao inserir membro na AÇÃO: " . implode(", ", $stmt->errorInfo()), "rh_cipa_acoes_membros", $idModulo, $idAcao);
                    }
                }
            }   
        
        //
        //- INSERe REGISTRO NA LINHA DO TEMPO DE CADA cipeiro
        //
            //
            //- EXCLUI LINHA DE TEMPO das PESSOAS
            //
                $sql = "DELETE FROM rh_pessoas_ldt WHERE idAcaoTipo = 33 and idOrigem = :idAcaoAlt";
                $stmt = $conn->prepare($sql);
                $stmt->execute(['idAcaoAlt' => $idAcaoAlt]);
            //        
            foreach ($ids as $idCipeiro) {
                // Verifica se o idCipeiro é válido
                if (is_numeric($idCipeiro) && $idCipeiro > 0) {
                    //
                    //- recupera idPessoa do cipeiro
                    //
                    $sqlPessoa = "SELECT idPessoa FROM rh_cipeiros WHERE id = :idCipeiro";
                    $stmtPessoa = $conn->prepare($sqlPessoa);
                    $stmtPessoa->bindParam(':idCipeiro', $idCipeiro, PDO::PARAM_INT);
                    $stmtPessoa->execute();
                    $dados = $stmtPessoa->fetch(PDO::FETCH_ASSOC);
                    if (!$dados) {
                        // Se não encontrar o cipeiro, continua para o próximo
                        continue;
                    }

                    $idPessoa = $dados['idPessoa'];
                    $tipo = 33; // Evento de CIPA
                    $descricao = "Participou em $data_acao da AÇÃO de CIPA, assunto: $assunto";
                    f_ldt( $tipo, $idPessoa, $descricao, $idAcaoAlt, $data_acao);
                }
            }
        //
        //- INSERE O ARQUIVO NO DIRETÓRIO PADRÃO PARA ARQUIVOS DA CIPA
        //
            // Diretório onde o arquivo será salvo
            $dirDestino = "../docs/cipa"; // Ajuste o caminho conforme necessário

            // Verifica se o diretório existe, senão cria
            if (!is_dir($dirDestino)) {
                mkdir($dirDestino, 0755, true);
            }

            // Verifica se o arquivo foi enviado
            if (isset($_FILES['acao_arquivo']) && $_FILES['acao_arquivo']['error'] === UPLOAD_ERR_OK) {
                //
                //- ELIMINA ARQUIVO ANTERIOR
                //
                    if( !empty( $arquivo_anterior ))
                    {
                        $caminhoArquivo = "../docs/cipa/" . basename($arquivo_anterior);
                        if (file_exists($caminhoArquivo)) {
                            unlink($caminhoArquivo); // Exclui o arquivo do servidor
                        }
                    }
                //
                //- SALVA NOVO ARQUIVO
                //
                $arquivoTmp = $_FILES['acao_arquivo']['tmp_name'];
                $nomeOriginal = basename($_FILES['acao_arquivo']['name']);
                $tamanho = $_FILES['acao_arquivo']['size']; // em bytes

                // Gera nome seguro (com data e hash para evitar duplicidade)
                $ext = pathinfo($nomeOriginal, PATHINFO_EXTENSION);
                $nomeSeguro = 'acao_' . date('Ymd_His') . '_' . uniqid() . '.' . strtolower($ext);

                $caminhoFinal = $dirDestino . '/' . $nomeSeguro;

                // Move o arquivo
                if (move_uploaded_file($arquivoTmp, $caminhoFinal)) {
                    // Arquivo salvo com sucesso
                    $acao_arquivo_nome = $nomeSeguro; // salvar esse valor no banco
                    //
                    // ATUALIZA o nome do arquivo na tabela de Ações
                    //
                        $sqlUpdate = "UPDATE rh_cipa_acoes SET acao_arquivo = :acao_arquivo WHERE id = :idAcao";
                        $stmtUpdate = $conn->prepare($sqlUpdate);
                        $stmtUpdate->bindParam(':acao_arquivo', $nomeSeguro, PDO::PARAM_STR);
                        $stmtUpdate->bindParam(':idAcao', $idAcaoAlt, PDO::PARAM_INT);
                        $stmtUpdate->execute();
                    //
                    //- Exclui arquivo anterior, se existir
                    //
                        if( !empty( $arquivo_anterior ))
                        {   //- ../docs/cipa/
                            $caminhoArquivo = "../docs/cipa/" . basename($arquivo_anterior);
                            if (file_exists($caminhoArquivo)) {
                                unlink($caminhoArquivo); // Exclui o arquivo do servidor
                                //
                                $sql = "DELETE FROM rh_documentos WHERE arquivo like :arquivo_anterior";
                                $stmt = $conn->prepare($sql);
                                $stmt->execute(['arquivo_anterior' => $arquivo_anterior]);
                            }
                        }

                    //
                    //- INCLUI no BD o novo arquivo
                    //
                    $idTipoDoc = 24; //- ata de reunião
                    //
                    $dataOriginal = $_POST['data_acao']; // Exemplo: "2025-06-26"
                    $data = new DateTime($dataOriginal);
                    $data->modify('+10 years');
                    $dataMais10Anos = $data->format('Y-m-d'); // Resultado: "2035-06-26"
                    //
                    $idPessoa = $_SESSION['idPessoa'];
                    $tags = '#CIPA #Ação da CIPA';
                    //
                    $texto_ocr = ocr($caminhoFinal);
                    $texto_ocr = "$nomeOriginal | " . addslashes($texto_ocr);
                    //
                    $sql = "INSERT INTO rh_documentos (idEmpresa, idPessoa, idTipoDoc, data, descricao, data_validade, arquivo,
                                nome_original, ocr, tags, extensao, tamanho, status, idLoginAprova, origem)
                                VALUES
                                (:idEmpresa, :idPessoa, :idTipoDoc, :data, :descricao, :data_validade, :arquivo,
                                :nome_original, :ocr, :tags, :extensao, :tamanho, 1, :idLogin, 'CIP')";
                    //debug( $sql );
                    $stmt = $conn->prepare($sql);
                    $stmt->bindParam(':idEmpresa', $idEmpresa);
                    $stmt->bindParam(':idPessoa', $idPessoa);
                    $stmt->bindParam(':idTipoDoc', $idTipoDoc);
                    $stmt->bindParam(':data', $dataOriginal);
                    $stmt->bindParam(':descricao', $assunto);
                    $stmt->bindParam(':data_validade', $dataMais10Anos);
                    $stmt->bindParam(':arquivo', $nomeSeguro);
                    $stmt->bindParam(':nome_original', $nomeOriginal);
                    $stmt->bindParam(':ocr', $texto_ocr);
                    $stmt->bindParam(':tags', $tags);
                    $stmt->bindParam(':extensao', $ext);
                    $stmt->bindParam(':tamanho', $tamanho);
                    $stmt->bindParam(':idLogin', $idLogin);
                    $stmt->execute();
                    //

                } else {
                    die(json_encode(["status" => false, "msg" => "<div class='alert alert-danger'>Erro ao mover o arquivo enviado.</div>"]));
                }

            } else {
                $acao_arquivo_nome = null; // Nenhum arquivo enviado
            }

    } else {
        // Ocorreu um erro durante a inserção
        $retorno = "<div class='alert alert-danger'>Erro ao inserir AÇÃO de CIPA.</div>";
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

