<?PHP
//
// autocadastro_aj6.php | Salva Alterações de Autocadastro pelo RH
// (C)haia, 27/10/2025
//

session_start();

$idModulo = 21; //-Autocadastro

include_once "conexao_gerar.php";

$dados = filter_input_array(INPUT_POST, FILTER_DEFAULT);
if ($dados) extract($dados);

/*
include "debug.php";
debug( json_encode($dados, JSON_PRETTY_PRINT)  );
/*
 rh_autocadastro_aj6.php | 2025-10-27 14:45:23 
{
    "id": "1",
    "nome_completo": "LUIZ AUGUSTO CHAIA",
    "nome_social": "LUIZ AUGUSTO CHAIA",
    "data_nascimento": "1967-02-17",
    "sexo": "M",
    "idEstadoCivil": "3",
    "nacionalidade": "Brasileira",
    "idEtnia": "1",
    "camiseta": "G2",
    "mae": "NADIR MACIEL CHAIA",
    "telefone": "+55 42 9 8866 8668",
    "email_pessoal": "lachaia@gmail.com",
    "cpf": "57160600991",
    "rg": "3742568-0 SSP PR",
    "pis": "170.12981.55-3",
    "ctps": "05276 00009-PR",
    "titulo": "0143.8589.0698",
    "idGrauEscola": "14",
    "cnh": "01159472989",
    "categoria_cnh": "AD",
    "vencimento_cnh": "2027-05-27",
    "cep": "81315415",
    "logradouro": "Rua Hamilton Taborda Ribas",
    "numero": "84",
    "complemento": "CASA 2",
    "bairro": "Cidade Industrial",
    "cidade": "Curitiba",
    "uf": "PR"
}
*/

if (empty($id)) {
    $retorno = [
        'status' => false,
        "msg" => "<div class='alert alert-danger'><strong>Erro!</strong> Faltou parâmetros...</div>"
    ];
    die(json_encode($retorno, JSON_PRETTY_PRINT));
}

$agora = date("Y-m-d H:i:s");

// Limpa o CPF (mantém apenas números)
$cpf_limpo = preg_replace('/\D/', '', $cpf);

$sql = "UPDATE rh_autocadastro SET
            nome              = :nome,
            nomeSocial        = :nome_social,
            nome_mae          = :mae,
            telefone          = :telefone,
            email             = :email_pessoal,
            sexo              = :sexo,
            dtNascimento      = :data_nascimento,
            idEstadoCivil     = :idEstadoCivil,
            nacionalidade     = :nacionalidade,
            cpf               = :cpf,
            rg                = :rg,
            titulo_eleitor    = :titulo,
            pis               = :pis,
            ctps              = :ctps,
            camiseta          = :camiseta,
            idEtnia           = :idEtnia,
            idGrauEscola      = :idGrauEscola,
            cnh               = :cnh,
            cnh_categoria     = :categoria_cnh,
            cnh_vencimento    = :vencimento_cnh,
            cep               = :cep,
            logradouro        = :logradouro,
            numero            = :numero,
            complemento       = :complemento,
            bairro            = :bairro,
            cidade            = :cidade,
            uf                = :uf,
            atualizado_em     = :agora
        WHERE id = :id";

$stmt = $conn->prepare($sql);

$stmt->bindValue(':nome', $nome_completo);
$stmt->bindValue(':nome_social', $nome_social);
$stmt->bindValue(':mae', $mae);
$stmt->bindValue(':telefone', $telefone);
$stmt->bindValue(':email_pessoal', $email_pessoal);
$stmt->bindValue(':sexo', $sexo);
$stmt->bindValue(':data_nascimento', $data_nascimento);
$stmt->bindValue(':idEstadoCivil', $idEstadoCivil, PDO::PARAM_INT);
$stmt->bindValue(':nacionalidade', $nacionalidade);
$stmt->bindValue(':cpf', $cpf_limpo);
$stmt->bindValue(':rg', $rg);
$stmt->bindValue(':titulo', $titulo);
$stmt->bindValue(':pis', $pis);
$stmt->bindValue(':ctps', $ctps);
$stmt->bindValue(':camiseta', $camiseta);
$stmt->bindValue(':idEtnia', $idEtnia, PDO::PARAM_INT);
$stmt->bindValue(':idGrauEscola', $idGrauEscola, PDO::PARAM_INT);
$stmt->bindValue(':cnh', $cnh);
$stmt->bindValue(':categoria_cnh', $categoria_cnh);
$stmt->bindValue(':vencimento_cnh', $vencimento_cnh);
$stmt->bindValue(':cep', $cep);
$stmt->bindValue(':logradouro', $logradouro);
$stmt->bindValue(':numero', $numero);
$stmt->bindValue(':complemento', $complemento);
$stmt->bindValue(':bairro', $bairro);
$stmt->bindValue(':cidade', $cidade);
$stmt->bindValue(':uf', $uf);
$stmt->bindValue(':agora', $agora);
$stmt->bindValue(':id', $id, PDO::PARAM_INT);

try {
    $stmt->execute();
    $retorno = [
        'status' => true,
        "msg" => "<div class='alert alert-success'><strong>Sucesso!</strong> Dados Salvos...</div>"
    ];
} catch (PDOException $e) {
    $msg = "❌ Erro ao atualizar: " . $e->getMessage();
    $retorno = [
    'status' => false,
    "msg" => "<div class='alert alert-danger'><strong>Erro!</strong>$msg</div>"
];
    
}

die(json_encode($retorno, JSON_PRETTY_PRINT));
