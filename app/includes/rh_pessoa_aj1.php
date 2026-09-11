<?php
//
//- rh_pessoa_aj1.php | Insere Novo PESSOA na tabela
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
include_once "../includes/debug.php";
debug(json_encode($parametros, JSON_PRETTY_PRINT));
$resposta = [
    'msg' => '<div class="alert alert-primary">
                <strong>OK!</strong> TESTADO COM SUCESSSO!
            </div>',
    'status' => false
];
die(json_encode($resposta));
/*
rh_pessoa_aj1.php | 2025-10-08 13:59:24 
{
    "idPessoa": "0",
    "acao_tipo": "",
    "nome": "Godofredo Winsburn",
    "nomeSocial": "God\u00f4",
    "dataNascimento": "1967-02-17",
    "sexo": "M",
    "idEstadoCivil": "2",
    "nacionalidade": "Brasileira",
    "cpf": "145.226.280-21",
    "rg": "44.318.836-1",
    "pis": "996.18889.51-4",
    "ctps": "123456 SR 0009",
    "tituloEleitor": "666282050663",
    "telefone": "41 9 9999-8888",
    "email": "godofredo@gmail.com",
    "idGrauInstrucao": "14",
    "cnh": "50040287601",
    "cnh_categoria": "B",
    "cnh_vencimento": "2028-02-17",
    "tamanhoCamiseta": "G2",
    "idEtnia": "1",
    "nome_mae": "Martinha Winsburn",
    "ativo": "0"
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
    include_once "../includes/conexao_gerar.php";
    include_once "../includes/f_logs.php";
    include_once "../includes/f_linha_do_tempo.php";
} else {
    header("location: logout.php");
}
//-- tratamento dos dados
//
$cpf = preg_replace("/\D/", "", $cpf); // Remove tudo que não for número

if( empty($cnh_vencimento)) $cnh_vencimento = null;

if (empty($idPessoa)) {
    //- verifica se já não existe e é INSERTING
    //
    $sql = "SELECT idPessoa FROM rh_pessoas WHERE nome LIKE ? OR cpf LIKE ?";
    $stmt = $conn->prepare($sql);
    $stmt->execute([$nome, $cpf]);

    if ($stmt->rowCount() > 0) {
        $resposta = [
            'msg' => '<div class="alert alert-danger">
                        <strong>Erro!</strong> Já existia no Sistema!
                        </div>',
            'status' => false
        ];
        die(json_encode($resposta));
    }

    //

    $acao = "INSERT";
    $sql = "INSERT INTO rh_pessoas (
            idEmpresa,
            nome,
            nomeSocial,
            dtNascimento,
            sexo,
            idEstadoCivil,
            nacionalidade,
            cpf,
            rg,
            pis,
            ctps,
            titulo_eleitor,
            telefone,
            email,
            idGrauEscola,
            cnh,
            cnh_categoria,
            cnh_vencimento,
            camiseta,
            idEtnia,
            nome_mae,
            idLogin
        ) VALUES (
            :idEmpresa,
            :nome,
            :nomeSocial,
            :dataNascimento,
            :sexo,
            :idEstadoCivil,
            :nacionalidade,
            :cpf,
            :rg,
            :pis,
            :ctps,
            :tituloEleitor,
            :telefone,
            :email,
            :idGrauInstrucao,
            :cnh,
            :cnh_categoria,
            :cnh_vencimento,
            :tamanhoCamiseta,
            :idEtnia,
            :nome_mae,
            :idLogin
        )";

    // Vincular os parâmetros com os valores recebidos
    //
    $stmt = $conn->prepare($sql);

    $stmt = $conn->prepare($sql);
    $stmt->bindParam(':idEmpresa', $idEmpresa);
    $stmt->bindParam(':idLogin', $idLogin);
    $stmt->bindParam(':nome', $nome);
    $stmt->bindParam(':nomeSocial', $nomeSocial);
    $stmt->bindParam(':dataNascimento', $dataNascimento);
    $stmt->bindParam(':sexo', $sexo);
    $stmt->bindParam(':idEstadoCivil', $idEstadoCivil);
    $stmt->bindParam(':nacionalidade', $nacionalidade);
    $stmt->bindParam(':cpf', $cpf);
    $stmt->bindParam(':rg', $rg);
    $stmt->bindParam(':pis', $pis);
    $stmt->bindParam(':ctps', $ctps);
    $stmt->bindParam(':tituloEleitor', $tituloEleitor);
    $stmt->bindParam(':telefone', $telefone);
    $stmt->bindParam(':email', $email);
    $stmt->bindParam(':idGrauInstrucao', $idGrauInstrucao);
    $stmt->bindParam(':cnh', $cnh);
    $stmt->bindParam(':cnh_categoria', $cnh_categoria);
    $stmt->bindParam(':cnh_vencimento', $cnh_vencimento);
    $stmt->bindParam(':tamanhoCamiseta', $tamanhoCamiseta);
    $stmt->bindParam(':idEtnia', $idEtnia);
    $stmt->bindParam(':nome_mae', $nome_mae);

    $tipo = 1; // Cadastro Criado
} else {
    // já Existe no BD - Fazer update

    $sql = "SELECT idPessoa FROM rh_pessoas WHERE idPessoa = $idPessoa";
    $stmt = $conn->prepare($sql);
    $stmt->execute();
    $dados_old = $stmt->fetch(PDO::FETCH_ASSOC);
    $dados_old = implode(", ", $dados_old);

    // Preparar a consulta SQL para atualizar

    // Preparar a consulta SQL para atualizar
    $acao = "UPDATE";
    $tipo = 3; // Dados Pessoais Atualizados

    $sql = "UPDATE rh_pessoas
        SET
            nome = :nome,
            nomeSocial = :nomeSocial,
            dtNascimento = :dataNascimento,
            sexo = :sexo,
            idEstadoCivil = :idEstadoCivil,
            nacionalidade = :nacionalidade,
            cpf = :cpf,
            rg = :rg,
            pis = :pis,
            ctps = :ctps,
            titulo_eleitor = :tituloEleitor,
            telefone = :telefone,
            email = :email,
            idGrauEscola = :idGrauInstrucao,
            cnh = :cnh,
            cnh_categoria = :cnh_categoria,
            cnh_vencimento = :cnh_vencimento,
            camiseta = :tamanhoCamiseta,
            idEtnia = :idEtnia,
            nome_mae = :nome_mae,
            ativo = :ativo
        WHERE idPessoa = :idPessoa";

    $stmt = $conn->prepare($sql);

    // Vincular os parâmetros
    $stmt->bindParam(':idPessoa', $idPessoa, PDO::PARAM_INT);
    $stmt->bindParam(':nome', $nome);
    $stmt->bindParam(':nomeSocial', $nomeSocial);
    $stmt->bindParam(':dataNascimento', $dataNascimento);
    $stmt->bindParam(':sexo', $sexo);
    $stmt->bindParam(':idEstadoCivil', $idEstadoCivil);
    $stmt->bindParam(':nacionalidade', $nacionalidade);
    $stmt->bindParam(':cpf', $cpf);
    $stmt->bindParam(':rg', $rg);
    $stmt->bindParam(':pis', $pis);
    $stmt->bindParam(':ctps', $ctps);
    $stmt->bindParam(':tituloEleitor', $tituloEleitor);
    $stmt->bindParam(':telefone', $telefone);
    $stmt->bindParam(':email', $email);
    $stmt->bindParam(':idGrauInstrucao', $idGrauInstrucao);
    $stmt->bindParam(':cnh', $cnh);
    $stmt->bindParam(':cnh_categoria', $cnh_categoria);
    $stmt->bindParam(':cnh_vencimento', $cnh_vencimento);
    $stmt->bindParam(':tamanhoCamiseta', $tamanhoCamiseta);
    $stmt->bindParam(':idEtnia', $idEtnia);
    $stmt->bindParam(':nome_mae', $nome_mae);
    $stmt->bindParam(':ativo', $ativo);
    //
}

if ($stmt->execute()) {
    //
    if ($tipo == 1) {
        $descricao = "$nome incluída no Sistema";
        $stipo = 'INC';
        $f_descricao = "INCLUSÃO de Pessoa no Sistema: Dados( $dados )";
    } else {
        $descricao = 'Dados Pessoais atualizados';
        $stipo = 'ALT';
        $f_descricao = "ALTERACAO de Pessoa no Sistema: Daddos Anteriores [$dados_old] | Dados( $dados )";
    }
    //
    $dados = implode(", ", $parametros);
    if ($acao == 'INSERT') $_idPessoa = $conn->lastInsertId();
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
