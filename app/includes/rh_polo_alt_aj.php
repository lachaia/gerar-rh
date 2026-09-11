<?php
//
//- unidade_alt_aj.php | SALVA ALTERAÇÃO REGISTRO DE UNIDADE
//- (C)haia, 15/07/2026
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

/*
include_once "../inc/debug.php";
debug( json_encode($dados, JSON_PRETTY_PRINT));
echo json_encode(["status" => true, "msg" => "Teste de inclusão de SubSede"]);
$conn = null;
die;
/*
 unidade_alt_aj.php | 2026-07-15 16:39:56 
{
    "id": "51",
    "identificador": "POLO BOQUEIR\u00c3O",
    "subsede_id": "101",
    "responsavel": "M\u00e1rcio da Villa II",
    "cep": "81850-000",
    "endereco": "Rua Eduardo Pinto da Rocha",
    "numero": "321",
    "complemento": "sala b",
    "bairro": "Alto Boqueir\u00e3o",
    "cidade_ds": "Curitiba",
    "cidade_id": "3281",
    "uf": "PR",
    "pais": "Brasil",
    "email": "polo_boqueirao@gerar.org.br",
    "telefone": "41 2222-3333",
    "obs": "Teste de Sistema"
}
    */

//- INSERE NOVO REGISTRO
//
    $sql = "UPDATE rh_polos 
                SET identificador = :identificador, subsede_id = :subsede_id, endereco = :endereco, numero = :numero, 
                    complemento = :complemento, bairro = :bairro, cep = :cep, cidade_id = :cidade_id, cidade_ds = :cidade_ds, 
                    uf = :uf, pais = :pais, telefone = :telefone, email = :email, responsavel = :responsavel, obs = :obs, polo_id = :polo_id
                WHERE id = :id";
    $stmt = $conn->prepare($sql);
    $stmt->bindParam(':identificador', $identificador, PDO::PARAM_STR);
    $stmt->bindParam(':subsede_id', $subsede_id, PDO::PARAM_INT);
    $stmt->bindParam(':responsavel', $responsavel, PDO::PARAM_STR);
    $stmt->bindParam(':cep', $cep, PDO::PARAM_STR);
    $stmt->bindParam(':endereco', $endereco, PDO::PARAM_STR);
    $stmt->bindParam(':numero', $numero, PDO::PARAM_STR);
    $stmt->bindParam(':complemento', $complemento, PDO::PARAM_STR);
    $stmt->bindParam(':bairro', $bairro, PDO::PARAM_STR);
    $stmt->bindParam(':cidade_id', $cidade_id, PDO::PARAM_INT);
    $stmt->bindParam(':cidade_ds', $cidade_ds, PDO::PARAM_STR);
    $stmt->bindParam(':uf', $uf, PDO::PARAM_STR);
    $stmt->bindParam(':pais', $pais, PDO::PARAM_STR);
    $stmt->bindParam(':telefone', $telefone, PDO::PARAM_STR);
    $stmt->bindParam(':email', $email, PDO::PARAM_STR);
    $stmt->bindParam(':obs', $obs, PDO::PARAM_STR);    
    $stmt->bindParam(':id', $id, PDO::PARAM_INT);
    $stmt->bindParam(':polo_id', $polo_id, PDO::PARAM_INT);
    
    if ($stmt->execute()) {
        $retorno = [
            "status" => true,
            "msg" => "Polo Atualizado com sucesso!"
        ];
    } else{
        $retorno = [
            "status" => false,
            "msg" => "Polo n&atilde;o atualizado!"
        ];
    }

die( json_encode($retorno) );