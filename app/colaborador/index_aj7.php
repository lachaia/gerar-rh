<?PHP
//
// index_aj7.php | Marca Notificação como Lida
// (C)haia, 12/11/2025
//

session_start();

$idModulo = 13; // Módulo do Colaborador

$parametros = filter_input_array(INPUT_POST, FILTER_DEFAULT);

if (isset($parametros)) {
    extract($parametros);
    $dados_novos = implode(", ", array_map('strval', $parametros)); // Garante que todos sejam strings
} else {
    $retorno = [
        "status" => false,
        "msg" => '<div class="alert alert-danger">
            <strong>Erro!</strong> Faltou parâmetros!
            </div>'
    ];
    die(json_encode($retorno));
}

$erros = [];
if (empty($id)) $erros[] = "ID do contrato";

if (!empty($erros)) {
    $retorno = [
        "status" => false,
        "msg" => '<div class="alert alert-danger">
            <strong>Erro!</strong> Faltou informar: ' . implode(", ", $erros) . '.
            </div>'
    ];
    die(json_encode($retorno));
}

//
//- Busca informações da Sessão
//
    if (isset($_SESSION['idLogin'])) {
        $idLogin = $_SESSION['idLogin'];
        include "../includes/conexao_gerar.php";
        //include "../includes/f_notificacoes.php";
    } else {
        header("Location: ../logout.php");
    }

    $agora = date('Y-m-d H:i:s');
    $sql = "UPDATE rh_notificacoes SET lido_em = :agora  WHERE id = :id";
    $stmt = $conn->prepare($sql);
    $stmt->bindParam(':agora', $agora);
    $stmt->bindParam(':id', $id);
    $res = $stmt->execute();

    if ($res) {
        $retorno = [
            "status" => true,
            "msg" => '<div class="alert alert-success">
                <strong>Sucesso!</strong> Notificação Lida!
                </div>'
        ];
    }

$conn = null; // Fecha a conexão
echo json_encode($retorno);
