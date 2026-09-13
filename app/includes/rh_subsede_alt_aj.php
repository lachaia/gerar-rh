<?php
//
//- subsede_alt_aj.php | SALVA ALTERAÇÃO REGISTRO DE SUBSEDE
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
// reafirma identidade da sessão depois do extract() — POST não deve conseguir sobrescrever
$idLogin = $_SESSION['idLogin'];
$criado_por = $_SESSION['nmLogin'];

/*
include_once "../includes/debug.php";
debug( json_encode($dados, JSON_PRETTY_PRINT));
echo json_encode(["status" => true, "msg" => "Teste de inclusão de SubSede"]);
$conn = null;
die;
/*
 subsede_alt_aj.php | 2026-07-14 16:40:10 
{
    "id": "14",
    "identificador": "S\u00c3O PAULO",
    "subsede_id": "250",
    "responsavel": "PAULO VIRTUOSO",
    "cep": "01001-000",
    "endereco": "Pra\u00e7a da S\u00e9",
    "numero": "225",
    "complemento": "ED PALMEIRAS",
    "bairro": "S\u00e9",
    "cidade_id": "",
    "uf": "SP",
    "pais": "Brasil",
    "email": "sao_paulo@gerar.org.br",
    "telefone": "11 3214-5652",
    "obs": "<p>teste de sistema<\/p>"
}
*/

// Se não veio no $_POST['ativo'], considera 0; se veio, pega o valor enviado (1)
$ativo = isset($_POST['ativo']) ? (int)$_POST['ativo'] : 0;

//- VERIFICA SE NÃO HÁ DUPLICIDADE

    $sql = "SELECT subsede_id FROM rh_subsedes WHERE subsede_id = :subsede_id AND id != :id";
    $stmt = $conn->prepare($sql);
    $stmt->bindParam(':subsede_id', $subsede_id, PDO::PARAM_STR);
    $stmt->bindParam(':id', $id, PDO::PARAM_STR);
    $stmt->execute();

    if ($stmt->rowCount() > 0) {
        echo json_encode(["status" => false, "msg" => "ERRO: SubSede j&aacute; cadastrada!"]);
        $conn = null;
        die;
    }

//- ATUALIDA O REGISTRO
//
    $sql = "UPDATE rh_subsedes 
                SET identificador = :identificador, subsede_id = :subsede_id, endereco = :endereco, numero = :numero, complemento = :complemento, 
                    bairro = :bairro, cep = :cep, cidade_id = :cidade_id, uf = :uf, telefone = :telefone, email = :email, 
                    responsavel = :responsavel, obs = :obs, ativo = :ativo
                WHERE id = :id";
    $stmt = $conn->prepare($sql);
    $stmt->bindParam(':identificador', $identificador, PDO::PARAM_STR);
    $stmt->bindParam(':subsede_id', $subsede_id, PDO::PARAM_INT);
    $stmt->bindParam(':responsavel', $responsavel, PDO::PARAM_STR);
    $stmt->bindParam(':cep', $cep, PDO::PARAM_STR);
    $stmt->bindParam(':uf', $uf, PDO::PARAM_STR);
    $stmt->bindParam(':telefone', $telefone, PDO::PARAM_STR);
    $stmt->bindParam(':email', $email, PDO::PARAM_STR);
    $stmt->bindParam(':cidade_id', $cidade_id, PDO::PARAM_INT);
    $stmt->bindParam(':obs', $obs, PDO::PARAM_STR);
    $stmt->bindParam(':endereco', $endereco, PDO::PARAM_STR);
    $stmt->bindParam(':numero', $numero, PDO::PARAM_STR);
    $stmt->bindParam(':complemento', $complemento, PDO::PARAM_STR);
    $stmt->bindParam(':bairro', $bairro, PDO::PARAM_STR);
    $stmt->bindParam(':id', $id, PDO::PARAM_INT);
    $stmt->bindParam(':ativo', $ativo, PDO::PARAM_INT);
    
    if ($stmt->execute()) {
        $retorno = [
            "status" => true,
            "msg" => "SubSede Atualizada com sucesso!"
        ];
    } else{
        $retorno = [
            "status" => false,
            "msg" => "SubSede n&atilde;o atualizada!"
        ];
    }

die( json_encode($retorno) );