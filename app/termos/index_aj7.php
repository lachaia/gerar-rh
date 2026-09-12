<?php
//
//- index_aj7.php | Recupera dados do id da RESERVA
//- (C)haia, 25/08/2025
//

session_start();

$idModulo = 19; // Equipamentos

$parametros = filter_input_array(INPUT_POST, FILTER_DEFAULT);
if (isset($parametros)) extract($parametros);

if (empty($id)) {
    $retorno = [
        "status" => false,
        "msg" => '<div class="alert alert-danger">
            <strong>Erro!</strong> Faltou parâmetros!
            </div>'
    ];
    die(json_encode($retorno));
}

if (isset($_SESSION['idLogin']) && in_array((int) ($_SESSION['idGrupo'] ?? 0), [4, 7, 9], true)) {
    $idLogin = $_SESSION['idLogin'];
    $nmLogin = $_SESSION['nmLogin'];
    //
    include_once "../includes/conexao_gerar.php";
    include_once "../includes/f_logs.php";
} else {
    header("location: logout.php");
    exit();
}

$sql = "SELECT  S.*, 
                P.nome, 
                P.cpf, 
                P.email, 
                P.telefone,
                e.logradouro, e.numero, e.complemento, e.bairro, e.cidade, e.uf, e.cep
            FROM RH.rh_equip_solic S
            INNER JOIN rh_pessoas P ON P.idPessoa = S.idPessoa
            LEFT JOIN rh_enderecos e 
                ON e.idPessoa = S.idPessoa 
                AND e.idTipoEndereco = 1
            WHERE S.id = :id LIMIT 1";
$stmt = $conn->prepare($sql);
$stmt->bindParam(':id', $id, PDO::PARAM_INT);
$stmt->execute();

$dados = $stmt->fetch(PDO::FETCH_ASSOC);


if (!$dados) {
    die(json_encode([
        "status" => false,
        "msg" => "Reserva não encontrada.",
        "dados" => null
    ]));
}
die( json_encode([
    "status" => true,
    "msg" => '<div class="alert alert-success">
        <strong>OK!</strong> Modelo encontrado!
        </div>',
    "dados" => $dados
]) );


