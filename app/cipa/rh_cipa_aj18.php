<?PHP
//
// rh_cipa_aj18.php | SALVA ALTERAÇÃO de REUNIÃO de CIPA
// (C)haia, 27/06/2025
//

session_start();

$idModulo = 11; // CIPA

if (!isset($_SESSION['idLogin']) || (empty($_SESSION['dcCIPA']) && (int) ($_SESSION['idGrupo'] ?? 0) !== 9)) {
    header('location: ../logout.php');
    exit();
}else{
    include_once "../includes/conexao_gerar.php";
    include_once "../includes/f_logs.php";
    include_once "../includes/f_linha_do_tempo.php";
    include_once "../includes/f_ocr.php";
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

//include_once "debug.php";
/*
debug( json_encode($dados, JSON_PRETTY_PRINT));
echo json_encode(["status" => true, "msg" => "<div class='alert alert-primary'><strong>Success!</strong>Teste de Sistema</div>"]);
$conn = null;
die;
 rh_cipa_aj18.php | 2025-06-27 11:46:00 
{
    "idReuniaoAlt": "6",
    "data_reuniao": "2025-06-27",
    "assunto": "TESTE",
    "observacoes": "<p>TESTE<\/p>",
    "cipeiro": "0",
    "idCipeiro": [
        "9",
        "10",
        "12"
    ]
}
*/

//
//- RECUPERA dados antes da ALTERAÇÃO
//

$sql = "SELECT 
            R.*,
            S.identificador as dsSubSede,

            -- Lista de nomes dos participantes
            (
                SELECT GROUP_CONCAT(P.nome ORDER BY P.nome SEPARATOR ', ')
                FROM rh_cipa_reuniao_membros M
                INNER JOIN rh_cipeiros B ON B.id = M.idCipeiro
                INNER JOIN rh_pessoas P ON P.idPessoa = B.idPessoa
                WHERE M.idReuniao = R.id AND M.presente = 1
            ) AS nomesParticipantes,

            -- Lista de IDs dos cipeiros participantes
            (
                SELECT GROUP_CONCAT(M.idCipeiro ORDER BY M.idCipeiro SEPARATOR ',')
                FROM rh_cipa_reuniao_membros M
                WHERE M.idReuniao = R.id AND M.presente = 1
            ) AS idParticipantes

        FROM rh_cipa_reunioes R
        INNER JOIN rh_subsedes S ON S.subsede_id = R.idSubSede
        WHERE R.id = :id";

$stmt = $conn->prepare($sql);
$stmt->execute(['id' => $idReuniaoAlt]); // Substitua $idCargo pelo valor desejado
$dadosOld = $stmt->fetch(PDO::FETCH_ASSOC); // Obtém os dados como um array associativo
$arquivo_anterior = $dadosOld['ata_arquivo'];
$strDadosOld = json_encode($dadosOld, JSON_UNESCAPED_UNICODE);

$ids = $_POST['idCipeiro'] ?? []; // Garante que seja array

try {
    //
    //- ALTERA os dados da REUNIÃO
    //

    $sql = "UPDATE rh_cipa_reunioes 
            SET  
                data_reuniao = :data_reuniao, 
                assunto = :assunto, 
                observacoes = :observacoes 
            WHERE id = :idReuniaoAlt";  

    $stmt = $conn->prepare($sql);
    $stmt->bindParam(':data_reuniao', $data_reuniao, PDO::PARAM_STR );
    $stmt->bindParam(':assunto', $assunto, PDO::PARAM_STR );
    $stmt->bindParam(':observacoes', $observacoes, PDO::PARAM_STR );
    $stmt->bindParam(':idReuniaoAlt', $idReuniaoAlt, PDO::PARAM_INT );

    $result = $stmt->execute();

    if ($result) {
        // Inserção bem-sucedida

        f_log("ALT", "Alterou Reunião de CIPA: Antes: ($strDadosOld) | Depois: ($strDados)", "rh_cipa_reunioes", $idModulo, $idReuniaoAlt);
        $retorno = "<div class='alert alert-success'>Alteração bem-sucedida!</div>";
        $status = true;
        
        //
        //- INSERIR PARTICIPANTES DA REUNIÃO
        //
            //
            //- EXCLUI OS MEMBROS DA REUNIÃO
            //
                $sql = "DELETE FROM rh_cipa_reuniao_membros WHERE idReuniao = :idReuniaoAlt";
                $stmt = $conn->prepare($sql);
                $stmt->execute(['idReuniaoAlt' => $idReuniaoAlt]);
            //        
            $presente = 1; // Presente por padrão
            foreach ($ids as $idCipeiro) {
                // Verifica se o idCipeiro é válido
                if (is_numeric($idCipeiro) && $idCipeiro > 0) {
                    // Prepara a inserção para cada cipeiro
                    $sql = "INSERT INTO rh_cipa_reuniao_membros (idReuniao, idCipeiro, presente) 
                            VALUES (:idReuniao, :idCipeiro, :presente)";
                    $stmt = $conn->prepare($sql);
                    $stmt->bindParam(':idReuniao', $idReuniaoAlt, PDO::PARAM_INT);
                    $stmt->bindParam(':idCipeiro', $idCipeiro, PDO::PARAM_INT);
                    $stmt->bindParam(':presente', $presente, PDO::PARAM_INT);
                    
                    if (!$stmt->execute()) {
                        // Se falhar, registra o erro e continua com os outros
                        f_log("ERR", "Erro ao inserir membro na reunião: " . implode(", ", $stmt->errorInfo()), "rh_cipa_reuniao_membros", $idModulo, $idReuniao);
                    }
                }
            }   
        
        //
        //- INSERe REGISTRO NA LINHA DO TEMPO DE CADA cipeiro
        //
            //
            //- EXCLUI LINHA DE TEMPO das PESSOAS
            //
                $sql = "DELETE FROM rh_pessoas_ldt WHERE idAcaoTipo = 33 and idOrigem = :idReuniaoAlt";
                $stmt = $conn->prepare($sql);
                $stmt->execute(['idReuniaoAlt' => $idReuniaoAlt]);
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
                    $descricao = "Participou em $data_reuniao da Reunião de CIPA, assunto: $assunto";
                    f_ldt( $tipo, $idPessoa, $descricao, $idReuniaoAlt, $data_reuniao);
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
                //
                //- ELIMINA ARQUIVO ANTERIOR
                //
                    if( !empty( $arquivo_anterior ))
                    {
                        $caminhoArquivo = "../docs/cipa/" . $arquivo_anterior;
                        if (file_exists($caminhoArquivo)) {
                            unlink($caminhoArquivo); // Exclui o arquivo do servidor
                        }
                    }
                //
                //- SALVA NOVO ARQUIVO
                //
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
                    //
                    // ATUALIZA o nome do arquivo na tabela de reuniões
                    //
                        $sqlUpdate = "UPDATE rh_cipa_reunioes SET ata_arquivo = :ata_arquivo WHERE id = :idReuniao";
                        $stmtUpdate = $conn->prepare($sqlUpdate);
                        $stmtUpdate->bindParam(':ata_arquivo', $nomeSeguro, PDO::PARAM_STR);
                        $stmtUpdate->bindParam(':idReuniao', $idReuniaoAlt, PDO::PARAM_INT);
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
