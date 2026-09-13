<?php
//
//- rh_polo_inc_aj.php | SALVA NOVO REGISTRO DE POLO
//- (C)haia, 27/07/2026
//

session_start();

if (!isset($_SESSION['idLogin'])) {
    header('Location: ../logout.php');
    exit();
} else {
    $idLogin = $_SESSION['idLogin'];
    $criado_por = $_SESSION['nmLogin'];
}
include_once "conexao_gerar.php";
include_once "f_logs.php";
//

$dados = filter_input_array(INPUT_POST, FILTER_DEFAULT);
if( $dados ) extract($dados);
// reafirma identidade da sessão depois do extract() — POST não deve conseguir sobrescrever
$idLogin = $_SESSION['idLogin'];
$criado_por = $_SESSION['nmLogin'];

/*
include_once "../inc/debug.php";
debug( json_encode($dados, JSON_PRETTY_PRINT));
echo json_encode(["status" => true, "msg" => "Teste de inclusão de SubSede"]);
$conn = null;
die;
/*
 unidade_inc_aj.php | 2026-07-15 16:12:59 
{
    "identificador": "POLO BOQUEIR\u00c3O",
    "subsede_id": "101",
    "responsavel": "Marcio Vidall",
    "cep": "81850-000",
    "endereco": "Rua Eduardo Pinto da Rocha",
    "numero": "321",
    "complemento": "sala 2",
    "bairro": "Alto Boqueir\u00e3o",
    "cidade_ds": "Curitiba",
    "cidade_id": "3281",
    "uf": "PR",
    "pais": "Brasil",
    "email": "email_gerar@gerar.org.br",
    "telefone": "41 3211-7895",
    "obs": "<p>teste de sistema<\/p>"
}
*/

//- INSERE NOVO REGISTRO
//
    $sql = "INSERT INTO rh_polos (identificador, subsede_id, endereco, numero, complemento, bairro, cep, cidade_id, cidade_ds, uf, pais, telefone, email, responsavel, login_id, obs, polo_id) VALUES
            (:identificador, :subsede_id, :endereco, :numero, :complemento, :bairro, :cep, :cidade_id, :cidade_ds, :uf, :pais, :telefone, :email, :responsavel, :login_id, :obs, :polo_id)";
    $stmt = $conn->prepare($sql);
    $stmt->bindParam(':identificador', $identificador, PDO::PARAM_STR);
    $stmt->bindParam(':subsede_id', $subsede_id, PDO::PARAM_STR);
    $stmt->bindParam(':responsavel', $responsavel, PDO::PARAM_STR);
    $stmt->bindParam(':cep', $cep, PDO::PARAM_STR);
    $stmt->bindParam(':uf', $uf, PDO::PARAM_STR);
    $stmt->bindParam(':telefone', $telefone, PDO::PARAM_STR);
    $stmt->bindParam(':email', $email, PDO::PARAM_STR);
    $stmt->bindParam(':cidade_id', $cidade_id, PDO::PARAM_INT);
    $stmt->bindParam(':obs', $obs, PDO::PARAM_STR);
    $stmt->bindParam(':login_id', $idLogin, PDO::PARAM_INT);
    $stmt->bindParam(':endereco', $endereco, PDO::PARAM_STR);
    $stmt->bindParam(':numero', $numero, PDO::PARAM_STR);
    $stmt->bindParam(':complemento', $complemento, PDO::PARAM_STR);
    $stmt->bindParam(':bairro', $bairro, PDO::PARAM_STR);
    $stmt->bindParam(':pais', $pais, PDO::PARAM_STR);
    $stmt->bindParam(':cidade_ds', $cidade_ds, PDO::PARAM_STR);
    $stmt->bindParam(':polo_id', $polo_id, PDO::PARAM_INT);
    
    if ($stmt->execute()) {
        $retorno = [
            "status" => true,
            "msg" => "Polo cadastrado com sucesso!"
        ];
    } else{
        $retorno = [
            "status" => false,
            "msg" => "Polo n&atilde;o cadastrado!"
        ];
    }

die( json_encode($retorno) );