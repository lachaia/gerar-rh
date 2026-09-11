<?php
//
//- dados_aj4.php | Salva Alteração de Dados de Pessoa
//- (C)haia, 26/02/2025
//

session_start();

$idModulo = 2; // Pessoas

$parametros = filter_input_array(INPUT_POST, FILTER_DEFAULT);

// Trata arrays e valores nulos
foreach ($parametros as $chave => $valor) {
    if (is_array($valor)) {
        $parametros[$chave] = implode(";", $valor); // Converte array para string separada por ";"
    } elseif (is_null($valor)) {
        $parametros[$chave] = 'NULL'; // Exibe "NULL" ao invés de remover o valor
    }
}

// Transforma tudo em uma única string separada por ", "
$dados = implode(", ", $parametros);
/*
include_once "../../includes/debug.php";
debug(json_encode($parametros, JSON_PRETTY_PRINT));
$resposta = [
    'msg' => '<div class="alert alert-primary">
                <strong>OK!</strong> TESTADO COM SUCESSSO!
            </div>',
    'status' => false
];
die(json_encode($resposta));
/*
dados_aj4.php | 2025-10-08 10:36:11 
{
    "idPessoa": "1",
    "nome": "LUIZ AUGUSTO CHAIA",
    "nomeSocial": "LUIZ AUGUSTO CHAIA",
    "idColab": "1",
    "dataNascimento": "1967-02-17",
    "sexo": "M",
    "idEstadoCivil": "3",
    "nacionalidade": "Brasileira",
    "cpf": "57160600991",
    "rg": "3.742.568-0 SSP PR",
    "tituloEleitor": "10121212",
    "telefone": "+5542988668668",
    "email": "luiz.chaia@gmail.com",
    "tamanhoCamiseta": "G2",
    "idEtnia": "1",
    "cnh": "01159472989",
    "cnh_categoria": "AD",
    "cnh_vencimento": "2027-05-02",
    "celular_corporativo": "41 9 9700 4083",
    "email_corporativo": "luiz.chaia@gerar.org.br",
    "nome_mae": "Nadir Maciel Chaia",
    "pis": "170.12981.55-3",
    "ctps": " 05276, sr: 00009-PR",
    "idGrau": "14"
}
*/

if (isset($parametros)) {
    extract($parametros);
    if (! isset($ativo)) $ativo = 1;
    if (empty($dataNascimento)) $dataNascimento = null;
    //
} else {
    $resposta = '<div class="alert alert-danger">
                <strong>Erro!</strong> Faltou parâmetros!
                </div>';
    die($resposta);
}

if (empty($nome)) {
    $resposta = [
        'msg' => '<div class="alert alert-danger">
                    <strong>Erro!</strong> Faltou parâmetros!
                </div>',
        'status' => false
    ];
    die($resposta);
}

if (isset($_SESSION['idLogin'])) {
    $idLogin = $_SESSION['idLogin'];
    $idEmpresa = $_SESSION['idEmpresa'];
    //
    include_once "../../includes/conexao_gerar.php";
    include_once "../../includes/f_logs.php";
    include_once "../../includes/f_linha_do_tempo.php";
} else {
    header("location: ../../logout.php");
}
//-- tratamento dos dados
//
$cpf = preg_replace("/\D/", "", $cpf); // Remove tudo que não for número

//
//- ATUALIZAR A TABELA
//

$sql = "SELECT idPessoa FROM rh_pessoas WHERE idPessoa = $idPessoa";
$stmt = $conn->prepare($sql);
$stmt->execute();
$dados_old = $stmt->fetch(PDO::FETCH_ASSOC);
$dados_old = implode(", ", $dados_old);

// Preparar a consulta SQL para atualizar
$acao = "UPDATE";
$tipo = 3; // Dados Pessoais Atualizados
$sql = "UPDATE rh_pessoas
        SET
            nome = :nome, nomeSocial = :nomeSocial, cpf = :cpf, telefone = :telefone, email = :email, sexo = :sexo,
            dtNascimento = :dataNascimento, idEstadoCivil = :idEstadoCivil, nacionalidade = :nacionalidade, rg = :rg, 
            titulo_eleitor = :tituloEleitor, camiseta = :tamanhoCamiseta, idEtnia = :idEtnia,
            cnh = :cnh, cnh_categoria = :cnh_categoria, cnh_vencimento = :cnh_vencimento,
            email_corporativo = :email_corporativo, celular_corporativo = :celular_corporativo,
            nome_mae = :nome_mae, pis = :pis, ctps = :ctps, idGrauEscola = :idGrau
            WHERE idPessoa = $idPessoa"; // Condição para o registro a ser atualizado 
// Vincular os parâmetros com os valores recebidos
$stmt = $conn->prepare($sql);
$stmt->bindParam(':nome', $nome);
$stmt->bindParam(':nomeSocial', $nomeSocial);
$stmt->bindParam(':cpf', $cpf);
$stmt->bindParam(':rg', $rg);
$stmt->bindParam(':tituloEleitor', $tituloEleitor);
$stmt->bindParam(':telefone', $telefone);
$stmt->bindParam(':email', $email);
$stmt->bindParam(':sexo', $sexo);
$stmt->bindParam(':idEstadoCivil', $idEstadoCivil);
$stmt->bindParam(':nacionalidade', $nacionalidade);
$stmt->bindParam(':tamanhoCamiseta', $tamanhoCamiseta);
$stmt->bindParam(':idEtnia', $idEtnia);
$stmt->bindParam(':dataNascimento', $dataNascimento);
$stmt->bindParam(':cnh', $cnh);
$stmt->bindParam(':cnh_categoria', $cnh_categoria);
$stmt->bindParam(':cnh_vencimento', $cnh_vencimento);
$stmt->bindParam(':email_corporativo', $email_corporativo);
$stmt->bindParam(':celular_corporativo', $celular_corporativo);
$stmt->bindParam(':nome_mae', $nome_mae);
$stmt->bindParam(':pis', $pis);
$stmt->bindParam(':ctps', $ctps);
$stmt->bindParam(':idGrau', $idGrau);

if ($stmt->execute()) {
    //
        $descricao = 'Dados Pessoais atualizados';
        $stipo = 'ALT';
        $f_descricao = "ALTERACAO de Pessoa no Sistema: Daddos Anteriores [$dados_old] | Dados( $dados )";
    //
    $dados = implode(", ", $parametros);
    //
    f_log($stipo, $f_descricao, "rh_pessoas", $idModulo, $idPessoa);
    f_ldt($tipo, $idPessoa, $descricao);
    //
    $resposta = [
        'msg' => "<div class='alert alert-success'>
                <strong>Successo!</strong> Registrado com sucesso!
                </div>",
        'status' => true,
        '_idPessoa' => $idPessoa
    ];
    //
} else {
    $resposta = [
        'msg' => "<div class='alert alert-danger'>
                    <strong>Erro!</strong> Falha ao Registrar Pessoa!
                    </div>",
        'status' => false
    ];
}

$conn = null;
die(json_encode($resposta));
