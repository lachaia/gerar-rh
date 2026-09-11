<?php
//
//- new_aj2.php | Salva Bloco 1 do CV
//- (C)haia, 2026-04-08
//

session_start();

header('Content-Type: application/json');

include "../app/includes/conexao_gerar.php";

$dados = filter_input_array(INPUT_POST, FILTER_DEFAULT);
if( $dados ) extract($dados);
/*
include "../app/includes/debug.php";
debug( json_encode($dados, JSON_PRETTY_PRINT) );
die(json_encode(["status" => true, "msg" => "Teste de inclusão de Currículo"]));
/*
 new_aj2.php | 2026-04-08 14:16:00 
{
    "cpf": "391.197.718-27",
    "nome": "Fl\u00e1via Miranda de Queiroz",
    "nomeSocial": "Flavinha",
    "sexo": "F",
    "telefone": "(41) 99759-0703",
    "email": "flavia.miranda@gerar.org.br",
    "nacionalidade": "Brasileira",
    "idGrauInstrucao": "10",
    "genero": "F",
    "deficiente": "1",
    "fisica": "1",
    "visual": "1",
    "auditiva": "1",
    "mental": "1",
    "intelectual": "1",
    "autista": "1",
    "cid": "1234",
    "linkedin": "https:\/\/www.linkedin.com\/in\/fl%C3%A1via-miranda-de-queiroz-281429151\/"
}
*/

//
//- VALIDA CAMPOS OBRIGATÓRIOS
//

if( empty($cpf) || empty($nome) || empty($telefone) || empty($email) || empty($sexo) || empty($nacionalidade) || empty($idGrauInstrucao) ){
    echo json_encode(['status' => false, 'msg' => 'Preencha todos os campos obrigatórios!']);
    exit;
}

$cpf_limpo = preg_replace('/\D+/', '', $cpf); // Resultado: 12345678909
$nomeSocial = $nomeSocial ?? '';

if (!empty($_SESSION['candidato_idPessoa'])) {
    //- Pessoa já existia (achada por CPF em new_aj1.php) - idPessoa vem só da
    //- sessão, nunca de campo do cliente, senão dá pra sobrescrever o cadastro
    //- de qualquer pessoa só sabendo o CPF dela. Confere que o CPF informado
    //- agora é mesmo o da pessoa da sessão.
    $idPessoa = (int) $_SESSION['candidato_idPessoa'];

    $sql = "SELECT idPessoa FROM rh_pessoas WHERE idPessoa = :idPessoa AND cpf = :cpf";
    $stmt = $conn->prepare($sql);
    $stmt->bindParam(':idPessoa', $idPessoa, PDO::PARAM_INT);
    $stmt->bindParam(':cpf', $cpf_limpo);
    $stmt->execute();

    if (!$stmt->fetchColumn()) {
        echo json_encode(['status' => false, 'msg' => 'Pessoa não encontrada!']);
        exit;
    }
} else {
    //- CPF novo - new_aj1.php não encontrou ninguém, então é a primeira vez
    //- dessa pessoa no sistema. Cria o cadastro e o CV agora, com os dados que
    //- acabaram de ser preenchidos no bloco 1 (já validados como obrigatórios
    //- ali em cima).
    $sql = "SELECT idPessoa FROM rh_pessoas WHERE cpf = :cpf";
    $stmt = $conn->prepare($sql);
    $stmt->bindParam(':cpf', $cpf_limpo);
    $stmt->execute();
    $idPessoa = (int) $stmt->fetchColumn();

    if (!$idPessoa) {
        $sql = "INSERT INTO rh_pessoas (cpf, nome, nomeSocial, telefone, email, sexo, nacionalidade, idGrauEscola)
                VALUES (:cpf, :nome, :nomeSocial, :telefone, :email, :sexo, :nacionalidade, :idGrauInstrucao)";
        $stmt = $conn->prepare($sql);
        $stmt->bindParam(':cpf', $cpf_limpo);
        $stmt->bindParam(':nome', $nome);
        $stmt->bindParam(':nomeSocial', $nomeSocial);
        $stmt->bindParam(':telefone', $telefone);
        $stmt->bindParam(':email', $email);
        $stmt->bindParam(':sexo', $sexo);
        $stmt->bindParam(':nacionalidade', $nacionalidade);
        $stmt->bindParam(':idGrauInstrucao', $idGrauInstrucao);

        if (!$stmt->execute()) {
            echo json_encode(['status' => false, 'msg' => 'Erro ao criar cadastro!']);
            exit;
        }
        $idPessoa = (int) $conn->lastInsertId();

        $sql = "INSERT INTO rh_cv (idPessoa) VALUES (:idPessoa)";
        $stmt = $conn->prepare($sql);
        $stmt->bindParam(':idPessoa', $idPessoa, PDO::PARAM_INT);
        $stmt->execute();
    }

    $_SESSION['candidato_idPessoa'] = $idPessoa;
}

//
//- ATUALIZA PESSOA
//

$sql = "UPDATE rh_pessoas SET 
            nome = :nome,
            nomeSocial = :nomeSocial,
            telefone = :telefone,
            email = :email,
            sexo = :sexo,
            nacionalidade = :nacionalidade,
            idGrauEscola = :idGrauInstrucao
        WHERE idPessoa = :idPessoa;";

$stmt = $conn->prepare($sql);
//
$stmt->bindParam(':idPessoa', $idPessoa);
$stmt->bindParam(':nome', $nome);
$stmt->bindParam(':nomeSocial', $nomeSocial);
$stmt->bindParam(':telefone', $telefone);
$stmt->bindParam(':email', $email);
$stmt->bindParam(':sexo', $sexo);
$stmt->bindParam(':nacionalidade', $nacionalidade);
$stmt->bindParam(':idGrauInstrucao', $idGrauInstrucao);
//
if (!$stmt->execute()) {
    echo json_encode(['status' => false, 'msg' => 'Erro ao atualizar dados!']);
    exit;
} 

//
//- PREPARAÇÃO DOS DADOS DO CV (Garante que checkboxes vazios virem 0)
//
$deficiente  = isset($deficiente) ? $deficiente : 0;
$genero      = isset($genero)     ? $genero : '0';
$fisica      = isset($fisica)     ? $fisica : 0;
$visual      = isset($visual)     ? $visual : 0;
$auditiva    = isset($auditiva)   ? $auditiva : 0;
$mental      = isset($mental)     ? $mental : 0;
$intelectual = isset($intelectual)? $intelectual : 0;
$autista     = isset($autista)    ? $autista : 0;
$cid         = !empty($cid)       ? $cid : '0'; // CID costuma ser string, mas '0' evita erro se for int

if ($deficiente == 0) {
    $fisica = $visual = $auditiva = $mental = $intelectual = $autista = 0;
    $cid = '0';
}

//
//- ATUALIZA CV
//
$sql = "UPDATE rh_cv 
        SET 
            linkedin = :linkedin, 
            deficiente = :deficiente, 
            genero = :genero,           -- VÍRGULA ADICIONADA AQUI
            def_fisica = :fisica,
            def_visual = :visual,
            def_auditiva = :auditiva,
            def_mental = :mental,
            def_intelectual = :intelectual,
            def_autista = :autista,
            cid = :cid
        WHERE idPessoa = :idPessoa";

try {
    $stmt = $conn->prepare($sql);
    $stmt->bindParam(':idPessoa',        $idPessoa);   
    $stmt->bindParam(':deficiente',      $deficiente);
    $stmt->bindParam(':genero',          $genero);
    $stmt->bindParam(':fisica',          $fisica);
    $stmt->bindParam(':visual',          $visual);
    $stmt->bindParam(':auditiva',        $auditiva);
    $stmt->bindParam(':mental',          $mental);
    $stmt->bindParam(':intelectual',     $intelectual);
    $stmt->bindParam(':autista',         $autista);
    $stmt->bindParam(':cid',             $cid);
    $stmt->bindParam(':linkedin',        $linkedin);

    if ($stmt->execute()) {
        echo json_encode(['status' => true, 'msg' => 'Salvo com sucesso!']);
    } else {
        // Se cair aqui, o execute retornou false. 
        // Vamos pegar o erro real do banco para te ajudar:
        $erro = $stmt->errorInfo();
        echo json_encode(['status' => false, 'msg' => 'Erro SQL: ' . $erro[2]]);
    }
} catch (PDOException $e) {
    echo json_encode(['status' => false, 'msg' => 'Erro de conexão: ' . $e->getMessage()]);
}