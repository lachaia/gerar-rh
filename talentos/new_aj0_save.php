<?php
//
//- rh_cv_aj1.php | CURRÍCULO | Salva CV (Dados Pessoais e Diversidade)
//- (C)haia, 01/04/2025 | (U) 2026-04-16
//

session_start();
$idModulo = 7; // CURRICULUM

$parametros = filter_input_array(INPUT_POST, FILTER_DEFAULT);

if (isset($parametros)) {
    extract($parametros);
    // Log formatado para leitura fácil
    $dados_novos = implode(", ", array_map(function($k, $v) { return "$k: $v"; }, array_keys($parametros), $parametros));
}

//- idPessoa vem da sessão aberta em new_aj1.php (dedupe por CPF) - nunca de um
//- campo do cliente, senão dá pra alterar o CV/criar login para qualquer pessoa
//- só sabendo o idPessoa dela.
if (empty($_SESSION['candidato_idPessoa'])) {
    die(json_encode(["status" => false, "msg" => "Sessão expirada. Verifique o CPF novamente."]));
}
$idPessoa = (int) $_SESSION['candidato_idPessoa'];

if (empty($email)) {
    die(json_encode(["status" => false, "msg" => "Erro: e-Mail não informado."]));
}

// Tratamento de Checkboxes de Deficiência (Se não marcar, o POST não envia, então forçamos 0)
$deficiente  = (isset($deficiente) && $deficiente == "1") ? 1 : 0;
$fisica      = (isset($fisica)) ? 1 : 0;
$visual      = (isset($visual)) ? 1 : 0;
$auditiva    = (isset($auditiva) || isset($def_auditiva)) ? 1 : 0; // Aceita ambos os nomes
$mental      = (isset($mental)) ? 1 : 0;
$intelectual = (isset($intelectual)) ? 1 : 0;
$autista     = (isset($autista)) ? 1 : 0;

if (isset($_SESSION['idLogin'])) $idLogin = $_SESSION['idLogin']; else $idLogin = 0;
if (isset($_SESSION['idEmpresa'])) $idEmpresa = $_SESSION['idEmpresa']; else $idEmpresa = 1;

$agora = date("Y-m-d H:i:s");

include_once "../app/includes/conexao_gerar.php";
include_once "../app/includes/f_logs.php";

/*
Estes são os campos recebidos no POST (TESTE REALIZADO COM SUCESSO)
new_aj0_save.php | 2026-04-16 10:59:50 
{
    "idCV": "8",
    "idPessoa": "21",
    "cpf": "391.197.718-27",
    "nome": "Flávia Miranda de Queiroz",
    "nomeSocial": "Flávia Miranda de Queiroz",
    "sexo": "F",
    "telefone": "(41) 99759-0703",
    "email": "flavia.miranda@gerar.org.br",
    "nacionalidade": "Brasileira",
    "idGrauInstrucao": "12",
    "genero": "F",
    "deficiente": "0",
    "auditiva": "1",
    "mental": "1",
    "cid": "1234",
    "linkedin": "https://www.linkedin.com/in/flávia-miranda-de-queiroz-281429151/",
    "cidade": "Guarapuava\/PR",
    "cidade_id": "3323",
    "cor": "Branca",
    "pronome": "ela",
    "orientacao": "Heterossexual",
    "identgenero": "Cisgênero"
}
*/

// 1. Verifica se já existe registro
$sql = "SELECT idCV, idPessoa FROM rh_cv WHERE idPessoa = :idPessoa";
$consulta = $conn->prepare($sql);
$consulta->bindParam(':idPessoa', $idPessoa);
$consulta->execute();
$registro = $consulta->fetch(PDO::FETCH_ASSOC);

if ($registro) {
    $idCV = $registro['idCV'];
    
    // UPDATE
    $status = "Atualizado";
    $sql = "UPDATE rh_cv SET 
                ultimaAtualizacao = :ultimaAtualizacao, 
                genero = :genero, 
                deficiente = :deficiente, 
                def_fisica = :def_fisica, 
                def_visual = :def_visual, 
                def_auditiva = :def_auditiva, 
                def_mental = :def_mental, 
                def_intelectual = :def_intelectual, 
                def_autista = :def_autista, 
                cid = :cid, 
                linkedin = :linkedin,
                idCidade = :idCidade,
                cor = :cor,
                pronome = :pronome,
                orientacao = :orientacao,
                idGenero = :idGenero,
                status = :status
            WHERE idPessoa = :idPessoa";

    $cidade_id = !empty($cidade_id) ? (int) $cidade_id : null;

    $stmt = $conn->prepare($sql);
    $stmt->bindParam(':idCidade', $cidade_id, $cidade_id === null ? PDO::PARAM_NULL : PDO::PARAM_INT);
    $stmt->bindParam(':cor', $cor);
    $stmt->bindParam(':pronome', $pronome);
    $stmt->bindParam(':orientacao', $orientacao);
    $stmt->bindParam(':idGenero', $identgenero);
    $stmt->bindParam(':ultimaAtualizacao', $agora);
    $stmt->bindParam(':genero', $genero);
    $stmt->bindParam(':deficiente', $deficiente);
    $stmt->bindParam(':def_fisica', $fisica);
    $stmt->bindParam(':def_visual', $visual);
    $stmt->bindParam(':def_auditiva', $auditiva);
    $stmt->bindParam(':def_mental', $mental);
    $stmt->bindParam(':def_intelectual', $intelectual);
    $stmt->bindParam(':def_autista', $autista);
    $stmt->bindParam(':cid', $cid);
    $stmt->bindParam(':linkedin', $linkedin);
    $stmt->bindParam(':idPessoa', $idPessoa);
    $stmt->bindParam(':status', $status);

    if ($stmt->execute()) {
        f_log("ALT", "ATUALIZAÇÃO CV - Pessoais/Diversidade: ($dados_novos)", "rh_cv", $idModulo, $idCV);
        $response = ["status" => true, "msg" => "<div class='alert alert-success'>Dados salvos com sucesso!</div>"];
    } else {
        $response = ["status" => false, "msg" => "Erro ao atualizar banco."];
    }
} else {
    // Não deveria acontecer - rh_cv é criado em new_aj2.php (save do bloco 1)
    $response = ["status" => false, "msg" => "Erro: currículo não encontrado. Volte e preencha o bloco de dados pessoais novamente."];
}

//
//- CRIA A CREDENCIAL DE CANDIDATO (só na primeira vez - rodar o assistente de
//- novo em cima de uma pessoa que já tem CV não deve trocar a senha dela)
//
$sql = "SELECT id FROM rh_user_candidatos WHERE pessoa_id = :idPessoa";
$stmt = $conn->prepare($sql);
$stmt->bindParam(':idPessoa', $idPessoa, PDO::PARAM_INT);
$stmt->execute();
$ja_tem_login = (bool) $stmt->fetch();

if (!$ja_tem_login) {
    $primeiroNome = strtok($nome, " ");

    $senha = gera_senha();
    $senhaHash = password_hash($senha, PASSWORD_DEFAULT);
    //
    $sql = "INSERT INTO rh_user_candidatos (pessoa_id, senha, criado_em) VALUES (:pessoa_id, :senha, NOW())";
    $stmt = $conn->prepare($sql);
    $stmt->bindParam(':pessoa_id', $idPessoa, PDO::PARAM_INT);
    $stmt->bindParam(':senha', $senhaHash, PDO::PARAM_STR);

    if (! $stmt->execute()) {
        $retorno = [
            "status" => false,
            "msg" => '<div class="alert alert-danger">'
            . 'Erro ao criar usuário: ' . $stmt->errorInfo()[2] . '</div>'
        ];
        die(json_encode($retorno));
    }

    //
    //- Envia e-mail de boas-vindas com a senha (só quando a credencial é criada agora)
    //

    include "../app/includes/inc_email.php";

    // Inicia o buffer de saída para evitar qualquer saída inesperada
    ob_start();

    $emailFrom = "ti@gerar.org.br";
    $nmFrom    = "GERAR SISTEMAS";
    $titulo    = "Bem vindo ao RH-GERAR";
    //
    $mail->isHTML(true);
    $mail->Subject = $titulo;
    $mail->setFrom($emailFrom, $nmFrom);

    $mail->addAddress($email, $primeiroNome);   // Add a recipient

    $html = "
        <p>Olá, <strong>$primeiroNome</strong>,</p>
        <p>Recebemos seu currículo em nosso sistema RH.</p>
        <p>Para manter atualizado, acesse usando seu CPF ou e-mail e a senha abaixo:</p><br>
        <h3>$senha</h3><br>
        <p>Atenciosamente,</p>
        <p><strong>Equipe GERAR SISTEMAS</strong></p>
    ";

    $mail->Body = $html;
    $mail->send();

    // Limpa e descarta qualquer saída acumulada
    ob_end_clean();
}

$conn = null;
echo json_encode($response);
exit();

function gera_senha($tamanho = 6) {
    // Definimos os caracteres permitidos (letras maiúsculas, minúsculas e números)
    $caracteres = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
    $senha = '';
    $max = strlen($caracteres) - 1;

    for ($i = 0; $i < $tamanho; $i++) {
        // Escolhe um caractere aleatório da string acima
        $senha .= $caracteres[random_int(0, $max)];
    }

    return $senha;
}

function limpaString($string) {
    return preg_replace(
        array("/(á|à|ã|â|ä)/", "/(é|è|ê|ë)/", "/(í|ì|î|ï)/", "/(ó|ò|õ|ô|ö)/", "/(ú|ù|û|ü)/", "/(ñ)/", "/(ç)/"),
        explode(" ", "a e i o u n c"),
        $string
    );
}