<?php
//
//- index_aj16.php | Salva alteração de Termo de Responsabilidade
//- (C)haia, 06/04/2026
//

session_start();
header('Content-Type: application/json; charset=utf-8');

if (!isset($_SESSION['idLogin'])) {
    echo json_encode([
        'status' => false,
        'msg' => 'Sessão expirada. Faça login novamente.'
    ]);
    exit;
}

include_once "../includes/conexao_gerar.php";

$id = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);
$idPessoa = filter_input(INPUT_POST, 'alt_termo_idPessoa', FILTER_VALIDATE_INT);
$idModelo = filter_input(INPUT_POST, 'alt_termo_idModelo', FILTER_VALIDATE_INT);

$nome = trim($_POST['alt_termo_nome'] ?? '');
$cpf = trim($_POST['alt_termo_cpf'] ?? '');
$email = trim($_POST['alt_termo_email'] ?? '');
$cep = trim($_POST['alt_termo_cep'] ?? '');
$endereco = trim($_POST['alt_termo_endereco'] ?? '');
$endNumero = trim($_POST['alt_termo_end_nro'] ?? '');
$bairro = trim($_POST['alt_termo_bairro'] ?? '');
$status = trim($_POST['alt_termo_status'] ?? '');

$idTipos = $_POST['alt_idTipoEquip'] ?? [];
$modelos = $_POST['alt_modelo'] ?? [];
$patrimonios = $_POST['alt_patrimonio'] ?? [];

if (!$id || !$idPessoa || !$idModelo) {
    echo json_encode([
        'status' => false,
        'msg' => 'Dados obrigatórios ausentes para alterar o termo.'
    ]);
    exit;
}

if (empty($email) || empty($endereco) || empty($endNumero) || empty($bairro) || empty($cep) || empty($status)) {
    echo json_encode([
        'status' => false,
        'msg' => 'Preencha todos os campos obrigatórios da alteração.'
    ]);
    exit;
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    echo json_encode([
        'status' => false,
        'msg' => 'E-mail inválido.'
    ]);
    exit;
}

if (!is_array($idTipos) || !is_array($modelos) || !is_array($patrimonios)) {
    echo json_encode([
        'status' => false,
        'msg' => 'Itens do termo inválidos.'
    ]);
    exit;
}

if (count($idTipos) === 0 || count($idTipos) !== count($modelos) || count($idTipos) !== count($patrimonios)) {
    echo json_encode([
        'status' => false,
        'msg' => 'Informe ao menos um item válido no termo.'
    ]);
    exit;
}

$permitidos = ['Pendente', 'Assinado', 'Baixado'];
if (!in_array($status, $permitidos, true)) {
    echo json_encode([
        'status' => false,
        'msg' => 'Status inválido.'
    ]);
    exit;
}

try {
    $conn->beginTransaction();

    $sqlPessoa = "UPDATE rh_pessoas
                 SET cpf = :cpf,
                     email = :email,
                     nome = :nome
                 WHERE idPessoa = :idPessoa";
    $stmtPessoa = $conn->prepare($sqlPessoa);
    $stmtPessoa->bindValue(':cpf', $cpf ?: null);
    $stmtPessoa->bindValue(':email', $email);
    $stmtPessoa->bindValue(':nome', $nome ?: null);
    $stmtPessoa->bindValue(':idPessoa', $idPessoa, PDO::PARAM_INT);
    $stmtPessoa->execute();

    $sqlTermo = "UPDATE rh_equip_termos
                 SET idModelo = :idModelo,
                     status = :status,
                     termo_nome = :termo_nome,
                     termo_endereco = :termo_endereco,
                     termo_end_numero = :termo_end_numero,
                     termo_bairro = :termo_bairro,
                     termo_cep = :termo_cep,
                     termo_email = :termo_email
                 WHERE id = :id";

    $stmtTermo = $conn->prepare($sqlTermo);
    $stmtTermo->bindValue(':idModelo', $idModelo, PDO::PARAM_INT);
    $stmtTermo->bindValue(':status', $status);
    $stmtTermo->bindValue(':termo_nome', $nome ?: null);
    $stmtTermo->bindValue(':termo_endereco', $endereco);
    $stmtTermo->bindValue(':termo_end_numero', $endNumero);
    $stmtTermo->bindValue(':termo_bairro', $bairro);
    $stmtTermo->bindValue(':termo_cep', $cep);
    $stmtTermo->bindValue(':termo_email', $email);
    $stmtTermo->bindValue(':id', $id, PDO::PARAM_INT);
    $stmtTermo->execute();

    $sqlDeleteItens = "DELETE FROM rh_equip_termos_ld WHERE idEquipTermo = :id";
    $stmtDeleteItens = $conn->prepare($sqlDeleteItens);
    $stmtDeleteItens->bindValue(':id', $id, PDO::PARAM_INT);
    $stmtDeleteItens->execute();

    $sqlInsertItem = "INSERT INTO rh_equip_termos_ld (idEquipTermo, idTipo, modelo, patrimonio)
                      VALUES (:idEquipTermo, :idTipo, :modelo, :patrimonio)";
    $stmtItem = $conn->prepare($sqlInsertItem);

    for ($i = 0; $i < count($idTipos); $i++) {
        $idTipo = (int)($idTipos[$i] ?? 0);
        $modelo = trim((string)($modelos[$i] ?? ''));
        $patrimonio = trim((string)($patrimonios[$i] ?? ''));

        if ($idTipo <= 0 || $modelo === '' || $patrimonio === '') {
            throw new Exception('Há item do termo incompleto.');
        }

        $stmtItem->bindValue(':idEquipTermo', $id, PDO::PARAM_INT);
        $stmtItem->bindValue(':idTipo', $idTipo, PDO::PARAM_INT);
        $stmtItem->bindValue(':modelo', $modelo);
        $stmtItem->bindValue(':patrimonio', $patrimonio);
        $stmtItem->execute();
    }

    if (isset($_FILES['alt_termo_arquivo']) && $_FILES['alt_termo_arquivo']['error'] === UPLOAD_ERR_OK) {
        $sqlPessoaTermo = "SELECT idPessoa FROM rh_equip_termos WHERE id = :id LIMIT 1";
        $stmtPessoaTermo = $conn->prepare($sqlPessoaTermo);
        $stmtPessoaTermo->bindValue(':id', $id, PDO::PARAM_INT);
        $stmtPessoaTermo->execute();
        $rowPessoaTermo = $stmtPessoaTermo->fetch(PDO::FETCH_ASSOC);

        if (!$rowPessoaTermo || empty($rowPessoaTermo['idPessoa'])) {
            throw new Exception('Não foi possível localizar a pasta do termo para upload.');
        }

        $idPessoaArquivo = (int)$rowPessoaTermo['idPessoa'];
        $uploadDir = "../docs/pessoa_{$idPessoaArquivo}/";

        if (!is_dir($uploadDir) && !mkdir($uploadDir, 0777, true)) {
            throw new Exception('Não foi possível criar a pasta do arquivo.');
        }

        $ext = pathinfo($_FILES['alt_termo_arquivo']['name'], PATHINFO_EXTENSION);
        $ext = preg_replace('/[^a-zA-Z0-9]/', '', (string)$ext);
        $ext = $ext !== '' ? strtolower($ext) : 'pdf';

        $nomeArquivo = "responsa_{$id}.{$ext}";
        $destino = $uploadDir . $nomeArquivo;

        if (!move_uploaded_file($_FILES['alt_termo_arquivo']['tmp_name'], $destino)) {
            throw new Exception('Falha ao salvar arquivo no servidor.');
        }

        $sqlArquivo = "UPDATE rh_equip_termos
                       SET arquivo = :arquivo,
                           origem = 'd'
                       WHERE id = :id";
        $stmtArquivo = $conn->prepare($sqlArquivo);
        $stmtArquivo->bindValue(':arquivo', $nomeArquivo);
        $stmtArquivo->bindValue(':id', $id, PDO::PARAM_INT);
        $stmtArquivo->execute();
    }

    $conn->commit();

    echo json_encode([
        'status' => true,
        'msg' => 'Alterações do termo salvas com sucesso.'
    ]);
} catch (Throwable $e) {
    if ($conn->inTransaction()) {
        $conn->rollBack();
    }

    echo json_encode([
        'status' => false,
        'msg' => 'Erro ao salvar alteração do termo: ' . $e->getMessage()
    ]);
}
