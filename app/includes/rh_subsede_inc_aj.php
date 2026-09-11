<?php
//
//- rh_subsede_inc_aj.php | SALVA NOVO REGISTRO DE SUBSEDE
//- (C)haia, 14/07/2026
//

session_start();

if (!isset($_SESSION['idLogin'])) {
    header('Location: ../logout.php');
    exit();
} else {
    $idLogin = $_SESSION['idLogin'];
    $criado_por = $_SESSION['nmLogin'];
}
include_once "../includes/conexao_gerar.php";
include_once "../includes/f_logs.php";
//

$dados = filter_input_array(INPUT_POST, FILTER_DEFAULT);
if( $dados ) extract($dados);

/*
include_once "../inc/debug.php";
debug( json_encode($dados, JSON_PRETTY_PRINT));
echo json_encode(["status" => true, "msg" => "Teste de inclusão de SubSede"]);
$conn = null;
die;
/*
 subsede_inc_aj.php | 2026-07-14 15:23:44 
{
    "identificador": "S\u00c3O PAULO",
    "subsede_id": "250",
    "responsavel": "PAULO O VIRTUOSO",
    "cep": "01001-000",
    "endereco": "Pra\u00e7a da S\u00e9",
    "numero": "250",
    "complemento": "ED PALMEIRAS",
    "bairro": "Centro",
    "cidade_id": "5341",
    "uf": "SP",
    "pais": "Brasil",
    "email": "sao_paulo@gerar.org.br",
    "telefone": "11 3214-6545",
    "obs": "<p>teste de sistema<\/p>"
}
*/

//- VERIFICA SE NÃO HÁ DUPLICIDADE

    $sql = "SELECT subsede_id FROM rh_subsedes WHERE subsede_id = :subsede_id";
    $stmt = $conn->prepare($sql);
    $stmt->bindParam(':subsede_id', $subsede_id, PDO::PARAM_STR);
    $stmt->execute();

    if ($stmt->rowCount() > 0) {
        echo json_encode(["status" => false, "msg" => "SubSede j&aacute; cadastrada!"]);
        $conn = null;
        die;
    }

//- INSERE NOVO REGISTRO
//
    $sql = "INSERT INTO rh_subsedes (identificador, subsede_id, endereco, numero, complemento, bairro, cep, cidade_id, uf, telefone, email, responsavel, login_id, obs) VALUES
            (:identificador, :subsede_id, :endereco, :numero, :complemento, :bairro, :cep, :cidade_id, :uf, :telefone, :email, :responsavel, :login_id, :obs)";
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
    
    if ($stmt->execute()) {
        $retorno = [
            "status" => true,
            "msg" => "SubSede cadastrada com sucesso!"
        ];
    } else{
        $retorno = [
            "status" => false,
            "msg" => "SubSede n&atilde;o cadastrada!"
        ];
    }

die( json_encode($retorno) );