<?php
//
//- rh_colab_aj9.php | Cria o Seletor Endereços do Colaborador
//- (C)haia, 09/04/2025
//

session_start();

$idModulo = 4; // colaboradores

$parametros = filter_input_array(INPUT_POST, FILTER_DEFAULT);
if (isset($parametros)) {
    extract($parametros);
    $dados_novos = implode(", ", array_map('strval', $parametros)); // Garante que todos sejam strings
}

if ( empty($idPessoa) ) {
    $retorno = [
        "status" => false,
        "msg" => '<div class="alert alert-danger">
            <strong>Erro!</strong> Faltou parâmetros!
            </div>'
    ];
    die(json_encode($retorno));
}

if (isset($_SESSION['idLogin'])) {
    $idLogin = $_SESSION['idLogin'];
    $idEmpresa = $_SESSION['idEmpresa'];
    $agora = date("Y-m-d H:i:s");
    //
    include_once "../includes/conexao_gerar.php";
} else {
    header("location: logout.php");
}

$sql = "SELECT E.idEndereco, T.dsTipoEndereco, logradouro, numero, complemento, bairro, cidade, uf 
            FROM RH.rh_enderecos E
            INNER JOIN rh_enderecos_tipo T on T.idTipoEndereco = E.idTipoEndereco
            WHERE idPessoa = $idPessoa";
$stmt = $conn->prepare($sql);
$stmt->execute();
$select = "<select class='form-select fs-13' id='idEndereco' name='idEndereco' onchange='change_endereco(this)'>";
$select .= "<option value='0' selected>Selecione</option>";

while ($linha = $stmt->fetch(PDO::FETCH_ASSOC)) {
    extract($linha);
    $select .= "<option value='$idEndereco'>$dsTipoEndereco: $logradouro, $numero, $complemento, $bairro, $cidade, $uf</option>";
}
$select .= "</select>";
$select .= "<span class='input-group-text'><a class='envia_arquivo' href='#!' onclick='add_endereco()'><i class='fa-solid fa-plus'></i></a></span>";
echo $select;
