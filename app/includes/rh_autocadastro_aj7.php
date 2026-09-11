<?PHP
//
//- rh_autocadastro_aj7.php | Transferir dados do Buffer para tabela RH_PESSOAS
// (C)haia, 27/10/2025;
//

session_start();

$idModulo = 21; //-Autocadastro

$idLogin = $_SESSION['idLogin'];
$idEmpresa = $_SESSION['idEmpresa'];

include_once "conexao_gerar.php";
include_once "f_logs.php";
include_once "f_linha_do_tempo.php";

$dados = filter_input_array(INPUT_POST, FILTER_DEFAULT);
if ($dados) extract($dados);
/*
include "debug.php";
debug( json_encode($dados, JSON_PRETTY_PRINT)  );
*/

//
//- LEITURA DO BUFFER

    $sql = "SELECT * FROM rh_autocadastro WHERE id = :id";
    $stmt = $conn->prepare($sql);
    $stmt->bindValue(':id', $id, PDO::PARAM_INT);
    $stmt->execute();
    $dados = $stmt->fetch(PDO::FETCH_ASSOC);
    if( ! $dados ){
        $msg = "<div class='alert alert-danger'><strong>Erro: </strong> Registro não encontrado no autocadastro.</div>";
        $response = ["status" => false, "msg" => $msg ];
        echo json_encode($response);
        exit;
    }
    extract($dados);

//
//- VERIFICAR SE JÁ EXISTE LÁ
    $sql = "SELECT idPessoa FROM rh_pessoas WHERE cpf = :cpf";
    $stmt = $conn->prepare($sql);
    $stmt->bindValue(':cpf', $cpf, PDO::PARAM_STR);
    $stmt->execute();
    $idPessoa = $stmt->fetchColumn();

//
//- Se existe, faz UPDATE
//
if( $idPessoa ){

    // Atualiza dados pessoais
    $sql = "UPDATE rh_pessoas SET 
                nome = :nome,
                nomeSocial = :nomeSocial,
                nome_mae = :nome_mae,
                telefone = :telefone,
                email = :email,
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
                idLogin = :idLogin
            WHERE idPessoa = :idPessoa";
    $stmt = $conn->prepare($sql);
    $stmt->execute([
        ':nome' => $nome,
        ':nomeSocial' => $nomeSocial,
        ':nome_mae' => $nome_mae,
        ':telefone' => $telefone,
        ':email' => $email,
        ':sexo' => $sexo,
        ':dtNascimento' => $dtNascimento,
        ':idEstadoCivil' => $idEstadoCivil,
        ':nacionalidade' => $nacionalidade,
        ':rg' => $rg,
        ':titulo_eleitor' => $titulo_eleitor,
        ':pis' => $pis,
        ':ctps' => $ctps,
        ':camiseta' => $camiseta,
        ':idEtnia' => $idEtnia,
        ':idGrauEscola' => $idGrauEscola,
        ':cnh' => $cnh,
        ':cnh_categoria' => $cnh_categoria,
        ':cnh_vencimento' => $cnh_vencimento,
        ':idLogin' => $idLogin,
        ':idPessoa' => $idPessoa
    ]);

    // Atualiza endereço residencial
    $sql = "SELECT idEndereco FROM rh_enderecos WHERE idPessoa = :idPessoa AND idTipoEndereco = 1";
    $stmt = $conn->prepare($sql);
    $stmt->bindValue(':idPessoa', $idPessoa);
    $stmt->execute();
    $idEndereco = $stmt->fetchColumn();

    if( $idEndereco ){
        $sql = "UPDATE rh_enderecos SET 
                    logradouro = :logradouro,
                    numero = :numero,
                    complemento = :complemento,
                    cep = :cep,
                    bairro = :bairro,
                    cidade = :cidade,
                    uf = :uf,
                    idLogin = :idLogin
                WHERE idEndereco = :idEndereco";
        $stmt = $conn->prepare($sql);
        $stmt->execute([
            ':logradouro' => $logradouro,
            ':numero' => $numero,
            ':complemento' => $complemento,
            ':cep' => $cep,
            ':bairro' => $bairro,
            ':cidade' => $cidade,
            ':uf' => $uf,
            ':idLogin' => $idLogin,
            ':idEndereco' => $idEndereco
        ]);
    } else {
        $sql = "INSERT INTO rh_enderecos 
                    (idEmpresa, idPessoa, idTipoEndereco, logradouro, numero, complemento, cep, bairro, cidade, uf, idLogin)
                VALUES 
                    (:idEmpresa, :idPessoa, 1, :logradouro, :numero, :complemento, :cep, :bairro, :cidade, :uf, :idLogin)";
        $stmt = $conn->prepare($sql);
        $stmt->execute([
            ':idEmpresa' => $idEmpresa,
            ':idPessoa' => $idPessoa,
            ':logradouro' => $logradouro,
            ':numero' => $numero,
            ':complemento' => $complemento,
            ':cep' => $cep,
            ':bairro' => $bairro,
            ':cidade' => $cidade,
            ':uf' => $uf,
            ':idLogin' => $idLogin
        ]);
    }
    $msg = "<div class='alert alert-success'><strong>Sucesso: </strong> Registro atualizado com sucesso..</div>";
    $response = ["status" => true, "msg" => $msg ];

//
//- Se não existe, faz INSERT
//
} else {

    // Insere nova pessoa
    $sql = "INSERT INTO rh_pessoas 
                (idEmpresa, nome, nomeSocial, nome_mae, telefone, email, sexo, dtNascimento, idEstadoCivil, nacionalidade,
                 cpf, rg, titulo_eleitor, pis, ctps, camiseta, idEtnia, idGrauEscola, cnh, cnh_categoria, cnh_vencimento, ativo, idLogin)
            VALUES
                (:idEmpresa, :nome, :nomeSocial, :nome_mae, :telefone, :email, :sexo, :dtNascimento, :idEstadoCivil, :nacionalidade,
                 :cpf, :rg, :titulo_eleitor, :pis, :ctps, :camiseta, :idEtnia, :idGrauEscola, :cnh, :cnh_categoria, :cnh_vencimento, 1, :idLogin)";
    $stmt = $conn->prepare($sql);
    $stmt->execute([
        ':idEmpresa' => $idEmpresa,
        ':nome' => $nome,
        ':nomeSocial' => $nomeSocial,
        ':nome_mae' => $nome_mae,
        ':telefone' => $telefone,
        ':email' => $email,
        ':sexo' => $sexo,
        ':dtNascimento' => $dtNascimento,
        ':idEstadoCivil' => $idEstadoCivil,
        ':nacionalidade' => $nacionalidade,
        ':cpf' => $cpf,
        ':rg' => $rg,
        ':titulo_eleitor' => $titulo_eleitor,
        ':pis' => $pis,
        ':ctps' => $ctps,
        ':camiseta' => $camiseta,
        ':idEtnia' => $idEtnia,
        ':idGrauEscola' => $idGrauEscola,
        ':cnh' => $cnh,
        ':cnh_categoria' => $cnh_categoria,
        ':cnh_vencimento' => $cnh_vencimento,
        ':idLogin' => $idLogin
    ]);
    $idPessoa = $conn->lastInsertId();

    // Insere endereço residencial
    $sql = "INSERT INTO rh_enderecos 
                (idEmpresa, idPessoa, idTipoEndereco, logradouro, numero, complemento, cep, bairro, cidade, uf, idLogin)
            VALUES 
                (:idEmpresa, :idPessoa, 1, :logradouro, :numero, :complemento, :cep, :bairro, :cidade, :uf, :idLogin)";
    $stmt = $conn->prepare($sql);
    $stmt->execute([
        ':idEmpresa' => $idEmpresa,
        ':idPessoa' => $idPessoa,
        ':logradouro' => $logradouro,
        ':numero' => $numero,
        ':complemento' => $complemento,
        ':cep' => $cep,
        ':bairro' => $bairro,
        ':cidade' => $cidade,
        ':uf' => $uf,
        ':idLogin' => $idLogin
    ]);

    $msg = "<div class='alert alert-success'><strong>Sucesso: </strong> Novo registro inserido com sucesso..</div>";
    $response = ["status" => true, "msg" => $msg ];
}

//
//- Atualiza Registro de Controle (token)

$status = 'Transferido';
$sql = "UPDATE rh_autocadastro_ctr 
        SET data_resposta = :agora, status = :status
        WHERE token = :token";
$stmt = $conn->prepare($sql);
$stmt->bindValue(':agora', date('Y-m-d H:i:s'));
$stmt->bindValue(':status', $status);
$stmt->bindValue(':token', $token);
$stmt->execute();

//--------------------------------------
// MOVER ARQUIVOS DO /buffer PARA /docs/pessoa_$idPessoa/
//--------------------------------------

$origemDir  = "../buffer/";  // caminho atual dos arquivos
$destinoDir = "../docs/pessoa_$idPessoa/";

// Cria diretório se não existir
if (!file_exists($destinoDir)) {
    mkdir($destinoDir, 0777, true);
}

// Array de arquivos que serão tratados (campo => idTipoDoc)
$arquivos = [
    'arquivo_foto'     => 3, // Tipo 3 → Foto
    'arquivo_endereco' => 1  // Tipo 1 → Comprovante de Endereço
];

foreach ($arquivos as $campo => $idTipoDoc) {

    if (empty($$campo)) continue; // pula se o campo estiver vazio no BD

    $arquivo = $$campo;                 // nome salvo no BD (único)
    $origem  = $origemDir . $arquivo;
    $destino = $destinoDir . $arquivo;

    // Verifica se o arquivo existe na pasta buffer
    if (!file_exists($origem)) {
        f_log("ERR", "Arquivo não encontrado: $origem", "rh_documentos", $idModulo);
        continue;
    }

    // Move o arquivo para outro diretório
    if (rename($origem, $destino)) {

        // Extrai informações
        $fileExt = pathinfo($arquivo, PATHINFO_EXTENSION);
        $fileSize = filesize($destino);
        $fileName = $arquivo; // Nome original igual ao salvo
        $dataAtual = date("Y-m-d H:i:s");

        // Busca validade no tipo de documento
        $sql = "SELECT validade FROM rh_docs_tipo WHERE idTipoDoc = :idTipoDoc";
        $stmt = $conn->prepare($sql);
        $stmt->bindValue(':idTipoDoc', $idTipoDoc, PDO::PARAM_INT);
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        $meses = !empty($result['validade']) ? $result['validade'] : 120;

        $validade = new DateTime($dataAtual);
        if ($meses > 0) $validade->modify("+$meses months");
        $validade_str = $validade->format("Y-m-d");

        $status = 1; // válido
        $idLoginAprova = $idLogin; // quem aprovou / importou

        $descricao = ($idTipoDoc == 3)
            ? "foto da pessoa"
            : "comprovante de endereço";

        // Insere o documento no banco
        $sql = "INSERT INTO rh_documentos 
                (idEmpresa, idPessoa, idTipoDoc, data, data_validade, arquivo, extensao, 
                tamanho, status, idLoginAprova, nome_original, descricao)
                VALUES 
                (:idEmpresa, :idPessoa, :idTipoDoc, :data, :data_validade, :arquivo, 
                :extensao, :tamanho, :status, :idLoginAprova, :original, :descricao)";
        $stmt = $conn->prepare($sql);
        $stmt->execute([
            ':idEmpresa'      => $idEmpresa,
            ':idPessoa'       => $idPessoa,
            ':idTipoDoc'      => $idTipoDoc,
            ':data'           => $dataAtual,
            ':data_validade'  => $validade_str,
            ':arquivo'        => $arquivo,
            ':extensao'       => $fileExt,
            ':tamanho'        => $fileSize,
            ':status'         => $status,
            ':idLoginAprova'  => $idLoginAprova,
            ':original'       => $fileName,
            ':descricao'      => $descricao
        ]);
        $idDoc = $conn->lastInsertId();

        // Log e linha do tempo (mantendo seu padrão)
        $descricao = ($idTipoDoc == 3)
            ? "Inserido foto da pessoa ($fileName)"
            : "Inserido comprovante de endereço ($fileName)";
        f_log("INC", "INCLUSÃO de Documento ($idTipoDoc) - $fileName", "rh_documentos", $idModulo, $idDoc);
        f_ldt(14, $idPessoa, $descricao);

    } else {
        f_log("ERRO", "Falha ao mover $arquivo de /buffer para /docs/pessoa_$idPessoa/", "rh_documentos", $idModulo);
    }
}

echo json_encode($response);