<?PHP 
//
// autocadastro_aj.php | Recebe o formulário do autocadastro do novo colaborador
// (C)haia, 23/10/2025
//

session_start();

$idModulo = 21; //-Autocadastro

include_once "includes/conexao_gerar.php";

$dados = filter_input_array(INPUT_POST, FILTER_DEFAULT);
if( $dados ) extract( $dados);

/*
include "includes/debug.php";
debug( json_encode($parametros, JSON_PRETTY_PRINT)  );
*/
//
//-- VERIFICA SE A PESSOA JÁ EXISTE PELO CPF
//
    // Limpa CPF (mantém apenas números)
    $cpf = preg_replace('/\D/', '', $dados['cpf'] ?? '');

    // Verifica se o CPF já existe
    $sql = "SELECT id FROM rh_autocadastro WHERE cpf = :cpf";
    $stmt = $conn->prepare($sql);
    $stmt->bindValue(':cpf', $cpf, PDO::PARAM_STR);
    $stmt->execute();
    $idAutocadastro = $stmt->fetchColumn();

// Campos comuns (tanto para insert quanto update)
$campos = [
    'nome'              => $dados['nome_completo'] ?? '',
    'nomeSocial'        => $dados['nome_social'] ?? '',
    'nome_mae'          => $dados['mae'] ?? '',
    'sexo'              => $dados['sexo'] ?? '',
    'dtNascimento'      => $dados['data_nascimento'] ?? '',
    'idEstadoCivil'     => $dados['idEstadoCivil'] ?? null,
    'nacionalidade'     => $dados['nacionalidade'] ?? '',
    'cpf'               => $cpf,
    'rg'                => $dados['rg'] ?? '',
    'titulo_eleitor'    => $dados['titulo'] ?? '',
    'pis'               => $dados['pis'] ?? '',
    'ctps'              => $dados['ctps'] ?? '',
    'camiseta'          => $dados['camiseta'] ?? '',
    'idEtnia'           => $dados['idEtnia'] ?? null,
    'idGrauEscola'      => $dados['idGrauEscola'] ?? null,
    'cnh'               => $dados['cnh'] ?? '',
    'cnh_categoria'     => $dados['categoria_cnh'] ?? '',
    'cnh_vencimento'    => $dados['vencimento_cnh'] ?? '',
    'token'             => $dados['token'] ?? '',
    'email'             => $dados['email'] ?? '',
    'telefone'          => $dados['telefone'] ?? '',
    //
    'cep'               => $dados['cep'] ?? '',
    'logradouro'        => $dados['logradouro'] ?? '',
    'numero'            => $dados['numero'] ?? '',
    'bairro'            => $dados['bairro'] ?? '',
    'cidade'            => $dados['cidade'] ?? '',
    'uf'                => $dados['uf'] ?? '',
    'complemento'       => $dados['complemento'] ?? ''
];

// UPDATE se já existe
if ($idExistente) {
    $sql = "UPDATE rh_autocadastro SET 
                nome = :nome,
                nomeSocial = :nomeSocial,
                nome_mae = :nome_mae,
                sexo = :sexo,
                dtNascimento = :dtNascimento,
                idEstadoCivil = :idEstadoCivil,
                nacionalidade = :nacionalidade,
                rg = :rg,
                titulo_eleitor = :titulo_eleitor,
                pis = :pis,
                ctps = :ctps,
                camiseta = :camiseta,
                idEtnia = :idEtnia,
                idGrauEscola = :idGrauEscola,
                cnh = :cnh,
                cnh_categoria = :cnh_categoria,
                cnh_vencimento = :cnh_vencimento,                
                atualizado_em = NOW(),
                token = :token,
                email = :email,
                telefone = :telefone,
                cep = :cep,
                logradouro = :logradouro,
                numero = :numero,
                bairro = :bairro,
                cidade = :cidade,
                uf = :uf,
                complemento = :complemento
            WHERE cpf = :cpf";
} else {
    $sql = "INSERT INTO rh_autocadastro (
                nome, nomeSocial, nome_mae, sexo, dtNascimento, idEstadoCivil, nacionalidade, 
                cpf, rg, titulo_eleitor, pis, ctps, camiseta, idEtnia, idGrauEscola, 
                cnh, cnh_categoria, cnh_vencimento, atualizado_em, token, email, telefone,
                cep, logradouro, numero, bairro, cidade, uf, complemento
            ) VALUES (
                :nome, :nomeSocial, :nome_mae, :sexo, :dtNascimento, :idEstadoCivil, :nacionalidade,
                :cpf, :rg, :titulo_eleitor, :pis, :ctps, :camiseta, :idEtnia, :idGrauEscola,
                :cnh, :cnh_categoria, :cnh_vencimento, NOW(), :token, :email, :telefone,
                :cep, :logradouro, :numero, :bairro, :cidade, :uf, :complemento
            )";
}

$stmt = $conn->prepare($sql);

// Faz o bind de todos os parâmetros
foreach ($campos as $campo => $valor) {
    $stmt->bindValue(":$campo", $valor);
}

$stmt->execute();

// Retorna o resultado
if ($idAutocadastro) {
    $resposta = [
        'msg' => "<div class='alert alert-success'><strong>Sucesso!</strong> ✅ Registro atualizado com sucesso (ID $idAutocadastro)!</div>",
        'status' => true
    ];    
} else {
    $idAutocadastro = $conn->lastInsertId();
    $resposta = [
        'msg' => "<div class='alert alert-success'><strong>Sucesso!</strong> ✅ Registro Inserido com sucesso (ID $idAutocadastro )!</div>",
        'status' => true
    ];     
}

//
//- TRATAMENTO DE UPLOAD DE ARQUIVOS PARA ÁREA TEMPORÁRIA (/buffer)
//
$dirBuffer = __DIR__ . '/buffer'; // diretório absoluto (pasta "buffer" no mesmo nível do script)

// Cria o diretório se não existir
if (!is_dir($dirBuffer)) {
    mkdir($dirBuffer, 0775, true);
}

$arquivo_foto = null;
$arquivo_endereco = null;

// FOTO
if (!empty($_FILES['foto']['name'])) {
    $extensao = pathinfo($_FILES['foto']['name'], PATHINFO_EXTENSION);
    $nomeFinal = 'foto_' . uniqid() . '_' . time() . '.' . strtolower($extensao);
    $caminhoDestino = $dirBuffer . '/' . $nomeFinal;

    if (move_uploaded_file($_FILES['foto']['tmp_name'], $caminhoDestino)) {
        $arquivo_foto = $nomeFinal;
    }
}

// COMPROVANTE DE ENDEREÇO
if (!empty($_FILES['comprovante_endereco']['name'])) {
    $extensao = pathinfo($_FILES['comprovante_endereco']['name'], PATHINFO_EXTENSION);
    $nomeFinal = 'comprovante_' . uniqid() . '_' . time() . '.' . strtolower($extensao);
    $caminhoDestino = $dirBuffer . '/' . $nomeFinal;

    if (move_uploaded_file($_FILES['comprovante_endereco']['tmp_name'], $caminhoDestino)) {
        $arquivo_endereco = $nomeFinal;
    }
}

$sql = "UPDATE rh_autocadastro 
        SET arquivo_foto = :foto, arquivo_endereco = :comprovante_endereco 
        WHERE token = :token";
$stmt = $conn->prepare($sql);
$stmt->bindValue(':foto', $arquivo_foto);
$stmt->bindValue(':comprovante_endereco', $arquivo_endereco);
$stmt->bindValue(':token', $token);
$stmt->execute();

$status = "RH";
$sql = "UPDATE rh_autocadastro_ctr 
        SET data_resposta = :agora, status = :status
        WHERE token = :token";
$stmt = $conn->prepare($sql);
$stmt->bindValue(':agora', date('Y-m-d H:i:s'));
$stmt->bindValue(':status', $status);
$stmt->bindValue(':token', $token);
$stmt->execute();

die( json_encode($resposta) );