<?PHP
//
//- rh_cipa_aj15.php | Grava Inclusão de REUNIÃO de CIPA
// (C)haia, 26/06/2025

session_start();

$idModulo = 11; // CIPA

$dados = filter_input_array(INPUT_POST, FILTER_DEFAULT);

if( $dados ){
    extract($dados);
    $stringDados = json_encode($dados, JSON_UNESCAPED_UNICODE);
    //
    include_once "../includes/conexao_gerar.php";
    include_once "../includes/f_logs.php";
    include_once "../includes/f_linha_do_tempo.php";
    include_once "../includes/f_ocr.php";
    include_once "../includes/f_upload_seguro.php";
    //
    $idEmpresa = $_SESSION['idEmpresa'];
    $criado_por = $_SESSION['nmLogin'];
    $idLogin = $_SESSION['idLogin'];
    $idPessoa = $_SESSION['idPessoa'];
    $idSubSede = $_SESSION['idSubSede'] ?? 0; // Pega a SubSede do usuário logado
    //    
}else{
    die(json_encode(["status" => false, "msg" => "Faltaram Parâmetros!"]));
}

include_once "../includes/debug.php";
/*
debug( json_encode($dados, JSON_PRETTY_PRINT));
echo json_encode(["status" => true, "msg" => "Teste de inclusão de usuário"]);
$conn = null;
die;
 rh_cipa_aj15.php | 2025-06-26 10:37:10 
{
    "data_reuniao": "2025-06-26",
    "assunto": "asdf",
    "observacoes": "<p>asdf<\/p>",
    "nmcipeiro": "0",
    "idCipeiro": [
        "12",
        "9",
        "11",
        "10"
    ]
}
*/
// die( json_encode(["status" => true, "msg" => "<div class='alert alert-primary'>Teste de inclusão de REUNIÃO</div>"]));

$ids = $_POST['idCipeiro'] ?? []; // Garante que seja array
$nomes = [];

if (!empty($ids) && is_array($ids)) {
    $sql = "SELECT p.nome 
            FROM RH.rh_cipeiros b
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
    $sql = "INSERT INTO rh_cipa_reunioes (idSubSede, data_reuniao, assunto, observacoes, criado_por, idLogin) 
                        VALUES (:idSubSede, :data_reuniao, :assunto, :observacoes, :criado_por, :idLogin)";
    $stmt = $conn->prepare($sql);
    $stmt->bindParam(':idSubSede', $idSubSede, PDO::PARAM_INT );
    $stmt->bindParam(':data_reuniao', $data_reuniao, PDO::PARAM_STR );
    $stmt->bindParam(':assunto', $assunto, PDO::PARAM_STR );
    $stmt->bindParam(':observacoes', $observacoes, PDO::PARAM_STR );
    $stmt->bindParam(':criado_por', $criado_por, PDO::PARAM_STR );
    $stmt->bindParam(':idLogin', $idLogin, PDO::PARAM_STR );

    $result = $stmt->execute();
    $idReuniao = $conn->lastInsertId();

    if ($result) {
        // Inserção bem-sucedida

        f_log("INC", "Incluiu Reunião de CIPA: $stringDados", "rh_cipa_reunioes", $idModulo, $idReuniao);
        $retorno = "<div class='alert alert-success'>Inclusão bem-sucedida!</div>";
        $status = true;
        
        //
        //- INSERIR PARTICIPANETES DA REUNIÃO
        //
            $presente = 1; // Presente por padrão
            foreach ($ids as $idCipeiro) {
                // Verifica se o idCipeiro é válido
                if (is_numeric($idCipeiro) && $idCipeiro > 0) {
                    // Prepara a inserção para cada cipeiro
                    $sql = "INSERT INTO rh_cipa_reuniao_membros (idReuniao, idCipeiro, presente) 
                            VALUES (:idReuniao, :idCipeiro, :presente)";
                    $stmt = $conn->prepare($sql);
                    $stmt->bindParam(':idReuniao', $idReuniao, PDO::PARAM_INT);
                    $stmt->bindParam(':idCipeiro', $idCipeiro, PDO::PARAM_INT);
                    $stmt->bindParam(':presente', $presente, PDO::PARAM_INT);
                    
                    if (!$stmt->execute()) {
                        // Se falhar, registra o erro e continua com os outros
                        f_log("ERR", "Erro ao inserir membro na reunião: " . implode(", ", $stmt->errorInfo()), "rh_cipa_reuniao_membros", $idModulo, $idReuniao);
                    }
                }
            }   
            $sql = "INSERT INTO rh_cipa_reuniao_membros ( idReuniao, idCipeiro, presente )
                    VALUES (:idReuniao, :idCipeiro, :presente)";
            $stmt = $conn->prepare($sql);
            $stmt->bindParam(':idReuniao', $idReuniao, PDO::PARAM_INT);
            $stmt->bindParam(':idCipeiro', $idCipeiro , PDO::PARAM_INT);
            $stmt->bindParam(':presente', $presente, PDO::PARAM_INT);
        
        //
        //- INSERe REGISTRO NA LINHA DO TEMPO DE CADA cipeiro
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
                    $descricao = "Participou da Reunião de CIPA: $assunto em $data_reuniao";
                    f_ldt( $tipo, $idPessoa, $descricao, $idReuniao);
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
            if (isset($_FILES['ata_arquivo']) && $_FILES['ata_arquivo']['error'] === UPLOAD_ERR_OK) {
                $validacao = upload_seguro_validar($_FILES['ata_arquivo'], ['pdf', 'jpg', 'jpeg', 'png', 'gif', 'webp', 'bmp']);
                if ($validacao !== true) {
                    $conn = null;
                    die(json_encode(["status" => false, "msg" => $validacao]));
                }
                $arquivoTmp = $_FILES['ata_arquivo']['tmp_name'];
                $nomeOriginal = basename($_FILES['ata_arquivo']['name']);
                $tamanho = $_FILES['ata_arquivo']['size']; // em bytes

                // Gera nome seguro (com data e hash para evitar duplicidade)
                $ext = pathinfo($nomeOriginal, PATHINFO_EXTENSION);
                $nomeSeguro = 'ata_' . date('Ymd_His') . '_' . uniqid() . '.' . strtolower($ext);

                $caminhoFinal = $dirDestino . '/' . $nomeSeguro;

                // Move o arquivo
                if (move_uploaded_file($arquivoTmp, $caminhoFinal)) {
                    // Arquivo salvo com sucesso
                    $ata_arquivo_nome = $nomeSeguro; // salvar esse valor no banco
                } else {
                    die(json_encode(["status" => false, "msg" => "<div class='alert alert-danger'>Erro ao mover o arquivo enviado.</div>"]));
                }
                //
                // ATUALIZA o nome do arquivo na tabela de reuniões
                //
                    $sqlUpdate = "UPDATE rh_cipa_reunioes SET ata_arquivo = :ata_arquivo WHERE id = :idReuniao";
                    $stmtUpdate = $conn->prepare($sqlUpdate);
                    $stmtUpdate->bindParam(':ata_arquivo', $nomeSeguro, PDO::PARAM_STR);
                    $stmtUpdate->bindParam(':idReuniao', $idReuniao, PDO::PARAM_INT);
                    $stmtUpdate->execute();
                //
                //- INSERE na tabela DOCUMENTOS
                //
                    $idTipoDoc = 24; //- ata de reunião
                //
                    $dataOriginal = $_POST['data_reuniao']; // Exemplo: "2025-06-26"
                    $data = new DateTime($dataOriginal);
                    $data->modify('+10 years');
                    $dataMais10Anos = $data->format('Y-m-d'); // Resultado: "2035-06-26"
                //
                    $idPessoa = $_SESSION['idPessoa'];
                    $tags = '#CIPA #Ata de Reunião';
                //
                    $texto_ocr = ocr($caminhoFinal);
                    $texto_ocr = "$nomeOriginal | " . addslashes($texto_ocr);
                //
                $sql = "INSERT INTO rh_documentos (idEmpresa, idPessoa, idTipoDoc, data, descricao, data_validade, arquivo,
                            nome_original, ocr, tags, extensao, tamanho, status, idLoginAprova, origem)
                            VALUES
                            (:idEmpresa, :idPessoa, :idTipoDoc, :data, :descricao, :data_validade, :arquivo, :nome_original,
                            :ocr, :tags, :extensao, :tamanho, 1, :idLogin, 'CIP')";
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
            } else {
                $ata_arquivo_nome = null; // Nenhum arquivo enviado
            }

    } else {
        // Ocorreu um erro durante a inserção
        $retorno = "<div class='alert alert-danger'>Erro ao inserir Reunião de CIPA.</div>";
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
die();