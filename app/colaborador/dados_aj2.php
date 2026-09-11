<?php
//
//- dados_aj2.php | Insere Novo ENDEREÇO (commit)
//- (C)haia, 15/05/2025 | 08/10/2025
//

session_start();

$idModulo = 13; // Colaborador

$parametros = filter_input_array(INPUT_POST, FILTER_DEFAULT);

// Converte as strings para arrays associativos

if (! empty($parametros['dados'])) {
    parse_str($parametros['dados'], $dados);
    extract($dados); // Agora você pode acessar diretamente as variáveis de dados_2
}else{
    $resposta = [ 'msg' => '<div class="alert alert-danger"><strong>NOT OK!</strong> eiiiiiiiiiiiita!</div>' ];
    die( json_encode($resposta, JSON_PRETTY_PRINT ));
}

//
if (isset($_SESSION['idLogin'])) {
    $idLogin = $_SESSION['idLogin'];
    $idEmpresa = $_SESSION['idEmpresa'];
    //
    include_once "../includes/conexao_gerar.php";
    include_once "../includes/f_logs.php";
} else {
    header("location: ../logout.php");
}
/*
    include_once "../includes/debug.php";
    debug( json_encode($dados, JSON_PRETTY_PRINT) );
    $resposta = [ 'msg' => '<div class="alert alert-primary"><strong>OK!</strong> TESTADO!</div>' ];
    die( json_encode($resposta, JSON_PRETTY_PRINT ));
    //
     index_aj2.php | 2025-05-15 15:42:42 
        {
            "idColab": "1",
            "idEndereco": "",
            "idTipoEndereco": "1",
            "cep": "85012-075",
            "logradouro": "Avenida Vereador Rubem Siqueira Ribas",
            "numero": "374",
            "complemento": "",
            "bairro": "Trianon",
            "cidade": "Guarapuava",
            "uf": "PR"
        }
*/
//$cpf = preg_replace("/\D/", "", $cpf); // Remove tudo que não for número
$cep = preg_replace("/\D/", "", $cep); // Remove tudo que não for número

if( empty( $idPessoa )){
    $resposta = [ 'msg' => '<div class="alert alert-danger"><strong>NOT OK!</strong> eiiiiiiiiiiiita!</div>' ];
    die( json_encode($resposta, JSON_PRETTY_PRINT ));
}

//-- Insere Endereço

    $sql = "INSERT INTO rh_enderecos ( idEmpresa, idPessoa, idTipoEndereco, cep, logradouro, numero, complemento, bairro, cidade, uf, idLogin
            ) VALUES (
                :idEmpresa, :idPessoa, :idTipoEndereco, :cep, :logradouro, :numero, :complemento, :bairro, :cidade, :uf, :idLogin
            )";

    $stmt = $conn->prepare($sql);
    //
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
                            <strong>Erro!</strong> Falha ao inserir Endereço!
                            </div>',
            'status' => false
        ];
        //
        $idEndereco = $conn->lastInsertId();
        f_log("INC", "INCLUSÃO de Endereço: Dados( $dados )", "rh_enderecos", $idModulo, $idEndereco);
        //
        $tipo = 10; // Inserido Endereço
        $descricao = "Endereço incluído no Sistema";
        f_ldt( $tipo, $idPessoa, $descricao);
        //
        $conn = null;
        die(json_encode($resposta));
    }

//-- retorna vetor Endereço
//

$idEndereco = $conn->lastInsertId();

$retorno = [
    "status" => true,
    "msg" => '<div class="alert alert-success">
                <strong>Successo!</strong> Endereço inserido com sucesso!
                </div>',
    'dados' => $dados,
    'idEndereco' => $idEndereco,
    'idPessoa'=> $idPessoa
];

$conn = null;
die(json_encode($retorno, JSON_PRETTY_PRINT));
