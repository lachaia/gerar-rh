<?php
//
//- dados_aj5.php | Salva Alteração de ENDEREÇO
//- (C)haia, 15/05/2025 | 15/10/2025
//

session_start();

$idModulo = 13; // Colaborador

$parametros = filter_input_array(INPUT_POST, FILTER_DEFAULT);

// Converte as strings para arrays associativos

if (! empty($parametros['dados'])) {
    parse_str($parametros['dados'], $dados);
    extract($dados); 
}else{
    $resposta = [ 'msg' => '<div class="alert alert-danger"><strong>NOT OK!</strong> eiiiiiiiiiiiita!</div>' ];
    die( json_encode($resposta, JSON_PRETTY_PRINT ));
}

//
if (isset($_SESSION['idLogin'])) {
    $idLogin = $_SESSION['idLogin'];
    $idEmpresa = $_SESSION['idEmpresa'];
    //
    include_once "../../includes/conexao_gerar.php";
    include_once "../../includes/f_logs.php";
} else {
    header("location: ../../logout.php");
    exit();
}
 /*
    include_once "../includes/debug.php";
    debug( json_encode($dados, JSON_PRETTY_PRINT) );
    $resposta = [ 'msg' => '<div class="alert alert-primary"><strong>OK!</strong> TESTADO!</div>' ];
    die( json_encode($resposta, JSON_PRETTY_PRINT ));
    /*
    index_aj4.php | 2025-05-16 09:19:07
    {
        "idColab": "1",
        "idEndereco": "2",
        "idTipoEndereco": "1",
        "cep": "85012075",
        "logradouro": "AVENIDA RUBEM SIQUEIRA RIBAS",
        "numero": "374",
        "complemento": "AP 306",
        "bairro": "TRIANON",
        "cidade": "GUARAPUAVA",
        "uf": "PR",
        "idPessoa": "1"
    }
*/
//$cpf = preg_replace("/\D/", "", $cpf); // Remove tudo que não for número
$cep = preg_replace("/\D/", "", $cep); // Remove tudo que não for número

// idPessoa é sempre o do usuário logado — nunca o que o cliente mandar.
$idPessoa = (int) ($_SESSION['idPessoa'] ?? 0);
if( empty( $idPessoa )){
    $resposta = [ 'msg' => '<div class="alert alert-danger"><strong>NOT OK!</strong> eiiiiiiiiiiiita!</div>' ];
    die( json_encode($resposta, JSON_PRETTY_PRINT ));
}

// Só pode alterar um endereço que já seja da própria pessoa.
$sqlCheck = "SELECT idPessoa FROM rh_enderecos WHERE idEndereco = :idEndereco";
$stmtCheck = $conn->prepare($sqlCheck);
$stmtCheck->bindParam(':idEndereco', $idEndereco, PDO::PARAM_INT);
$stmtCheck->execute();
$enderecoAtual = $stmtCheck->fetch(PDO::FETCH_ASSOC);
if (!$enderecoAtual || (int) $enderecoAtual['idPessoa'] !== $idPessoa) {
    http_response_code(403);
    die(json_encode(["status" => false, "msg" => "Acesso negado."]));
}

//-- Insere Endereço

    $sql = "UPDATE rh_enderecos SET
                idEmpresa       = :idEmpresa,
                idPessoa        = :idPessoa,
                idTipoEndereco  = :idTipoEndereco,
                cep             = :cep,
                logradouro      = :logradouro,
                numero          = :numero,
                complemento     = :complemento,
                bairro          = :bairro,
                cidade          = :cidade,
                uf              = :uf,
                idLogin         = :idLogin
            WHERE idEndereco = :idEndereco";

    $stmt = $conn->prepare($sql);
    //
    $stmt->bindParam(':idEndereco', $idEndereco, PDO::PARAM_INT);
    $stmt->bindParam(':idEmpresa', $idEmpresa, PDO::PARAM_INT);
    $stmt->bindParam(':idPessoa', $idPessoa, PDO::PARAM_INT);
    $stmt->bindParam(':idLogin', $idLogin, PDO::PARAM_INT);
    $stmt->bindParam(':idTipoEndereco', $idTipoEndereco, PDO::PARAM_INT);
    $stmt->bindParam(':cep', $cep, PDO::PARAM_STR);
    $stmt->bindParam(':logradouro', $logradouro, PDO::PARAM_STR);
    $stmt->bindParam(':numero', $numero, PDO::PARAM_STR);
    $stmt->bindParam(':complemento', $complemento, PDO::PARAM_STR);
    $stmt->bindParam(':bairro', $bairro, PDO::PARAM_STR);
    $stmt->bindParam(':cidade', $cidade, PDO::PARAM_STR);
    $stmt->bindParam(':uf', $uf, PDO::PARAM_STR);
    //
    if (! $stmt->execute()) {
        $resposta = [
            'msg' => '<div class="alert alert-danger">
                            <strong>Erro!</strong> Falha ao Salvar Endereço!
                            </div>',
            'status' => false
        ];
        //
        f_log("ALT", "ALTERAÇÃO de Endereço: Dados( $dados )", "rh_enderecos", $idModulo, $idEndereco);
        //
        $tipo = 31; // Inserido Endereço
        $descricao = "Endereço alterado no Sistema";
        f_ldt( $tipo, $idPessoa, $descricao);
        //
        $conn = null;
        die(json_encode($resposta));
    }

//-- retorna vetor Endereço
//

$retorno = [
    "status" => true,
    "msg" => '<div class="alert alert-success">
                <strong>Successo!</strong> Endereço alterado com sucesso!
                </div>',
    'dados' => $dados,
    'idEndereco' => $idEndereco,
    'idPessoa'=> $idPessoa
];

$conn = null;
die(json_encode($retorno, JSON_PRETTY_PRINT));
