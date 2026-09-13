<?PHP
//
// rh_brigada_aj18.php | SALVA ALTERAÇÃO de REUNIÃO de Brigada
// (C)haia, 27/06/2025
//

session_start();

$idModulo = 10; // Brigada

if (!isset($_SESSION['idLogin'])) {
    header('Location: logout.php');
    exit();
}else{
    include_once "conexao_gerar.php";
    include_once "f_logs.php";
    include_once "f_linha_do_tempo.php";
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

//include_once "debug.php";
/*
debug( json_encode($dados, JSON_PRETTY_PRINT));
echo json_encode(["status" => true, "msg" => "<div class='alert alert-primary'><strong>Success!</strong>Teste de Sistema</div>"]);
$conn = null;
die;
 rh_brigada_aj18.php | 2025-06-27 11:46:00 
{
    "idReuniaoAlt": "6",
    "data_reuniao": "2025-06-27",
    "assunto": "TESTE",
    "observacoes": "<p>TESTE<\/p>",
    "brigadista": "0",
    "idBrigadista": [
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
            S.dsSubSede,

            -- Lista de nomes dos participantes
            (
                SELECT GROUP_CONCAT(P.nome ORDER BY P.nome SEPARATOR ', ')
                FROM rh_brigada_reuniao_membros M
                INNER JOIN rh_brigadistas B ON B.id = M.idBrigadista
                INNER JOIN rh_pessoas P ON P.idPessoa = B.idPessoa
                WHERE M.idReuniao = R.id AND M.presente = 1
            ) AS nomesParticipantes,

            -- Lista de IDs dos brigadistas participantes
            (
                SELECT GROUP_CONCAT(M.idBrigadista ORDER BY M.idBrigadista SEPARATOR ',')
                FROM rh_brigada_reuniao_membros M
                WHERE M.idReuniao = R.id AND M.presente = 1
            ) AS idParticipantes

        FROM rh_brigada_reunioes R
        INNER JOIN rh_subsedes S ON S.idSubSede = R.idSubSede
        WHERE R.id = :id";

$stmt = $conn->prepare($sql);
$stmt->execute(['id' => $idReuniaoAlt]); // Substitua $idCargo pelo valor desejado
$dadosOld = $stmt->fetch(PDO::FETCH_ASSOC); // Obtém os dados como um array associativo
$arquivo_anterior = $dadosOld['ata_arquivo'];
$strDadosOld = json_encode($dadosOld, JSON_UNESCAPED_UNICODE);

$ids = $_POST['idBrigadista'] ?? []; // Garante que seja array

/*
//- Cria LISTA dos Participantes
//
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
*/

try {
    //
    //- ALTERA os dados da REUNIÃO
    //

    $sql = "UPDATE rh_brigada_reunioes 
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

        f_log("ALT", "Alterou Reunião de Brigada: Antes: ($strDadosOld) | Depois: ($strDados)", "rh_brigada_reunioes", $idModulo, $idReuniaoAlt);
        $retorno = "<div class='alert alert-success'>Alteração bem-sucedida!</div>";
        $status = true;
        
        //
        //- INSERIR PARTICIPANTES DA REUNIÃO
        //
            //
            //- EXCLUI OS MEMBROS DA REUNIÃO
            //
                $sql = "DELETE FROM rh_brigada_reuniao_membros WHERE idReuniao = $idReuniaoAlt";
                $stmt = $conn->prepare($sql);
                $stmt->execute();
            //        
            $presente = 1; // Presente por padrão
            foreach ($ids as $idBrigadista) {
                // Verifica se o idBrigadista é válido
                if (is_numeric($idBrigadista) && $idBrigadista > 0) {
                    // Prepara a inserção para cada brigadista
                    $sql = "INSERT INTO rh_brigada_reuniao_membros (idReuniao, idBrigadista, presente) 
                            VALUES (:idReuniao, :idBrigadista, :presente)";
                    $stmt = $conn->prepare($sql);
                    $stmt->bindParam(':idReuniao', $idReuniaoAlt, PDO::PARAM_INT);
                    $stmt->bindParam(':idBrigadista', $idBrigadista, PDO::PARAM_INT);
                    $stmt->bindParam(':presente', $presente, PDO::PARAM_INT);
                    
                    if (!$stmt->execute()) {
                        // Se falhar, registra o erro e continua com os outros
                        f_log("ERR", "Erro ao inserir membro na reunião: " . implode(", ", $stmt->errorInfo()), "rh_brigada_reuniao_membros", $idModulo, $idReuniao);
                    }
                }
            }   
        
        //
        //- INSERe REGISTRO NA LINHA DO TEMPO DE CADA BRIGADISTA
        //
            //
            //- EXCLUI LINHA DE TEMPO das PESSOAS
            //
                $sql = "DELETE FROM rh_pessoas_ldt WHERE idAcaoTipo = 33 and idOrigem = $idReuniaoAlt";
                $stmt = $conn->prepare($sql);
                $stmt->execute();
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
                    $descricao = "Participou em $data_reuniao da Reunião de Brigada, assunto: $assunto";
                    f_ldt( $tipo, $idPessoa, $descricao, $idReuniaoAlt, $data_reuniao);
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
            if (isset($_FILES['ata_arquivo']) && $_FILES['ata_arquivo']['error'] === UPLOAD_ERR_OK) {
                //
                //- ELIMINA ARQUIVO ANTERIOR
                //
                    if( !empty( $arquivo_anterior ))
                    {
                        $caminhoArquivo = "../docs/brigada/" . $arquivo_anterior;
                        if (file_exists($caminhoArquivo)) {
                            unlink($caminhoArquivo); // Exclui o arquivo do servidor
                        }
                    }
                //
                //- SALVA NOVO ARQUIVO
                //
                $arquivoTmp = $_FILES['ata_arquivo']['tmp_name'];
                $nomeOriginal = basename($_FILES['ata_arquivo']['name']);

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
                    $sqlUpdate = "UPDATE rh_brigada_reunioes SET ata_arquivo = :ata_arquivo WHERE id = :idReuniao";
                    $stmtUpdate = $conn->prepare($sqlUpdate);
                    $stmtUpdate->bindParam(':ata_arquivo', $nomeSeguro, PDO::PARAM_STR);
                    $stmtUpdate->bindParam(':idReuniao', $idReuniaoAlt, PDO::PARAM_INT);
                    $stmtUpdate->execute();
            } else {
                $ata_arquivo_nome = null; // Nenhum arquivo enviado
            }

    } else {
        // Ocorreu um erro durante a inserção
        $retorno = "<div class='alert alert-danger'>Erro ao inserir Reunião de Brigada.</div>";
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
