<?php
//
//- rh_equipamentos_aj1.php | Salva Registro de Solicitação de Equipamentos
//- (C)haia, 19/08/2025
//

session_start();

$idModulo = 19; // Equipamentos

$parametros = filter_input_array(INPUT_POST, FILTER_DEFAULT);
if ($parametros) extract($parametros);

/*
include "debug.php";
debug( json_encode($parametros, JSON_PRETTY_PRINT) );
$response = ["status" => true, "msg" => "<div class='alert alert-primary'>TESTE REALIZADO COM SUCESSO</div>"];
die(json_encode($response));
/*
 rh_equipamentos_aj1.php | 2025-08-19 17:03:13 
{
    "chaveApp": "lhun cqny adzx vlsr",
    "idPessoa": "59",
    "inc_responsavel": "Marcos Costa",
    "inc_usuario": "Margarida Frida",
    "equipamentos": [
        "Notebook",
        "Smartphone",
        "Chip",
        "Desktop",
        "Monitor",
        "Impressora"
    ],
    "inc_observacoes": "<p>Teste de Sistema<\/p>"
}

*/

if (isset($_SESSION['idLogin']) && !empty($_SESSION['idLogin'])) {
    $criado_por = $_SESSION['nmLogin'];
    //
    require_once "conexao_gerar.php"; // Inclua sua conexão com o banco de dados
    require_once "debug.php"; // Inclua sua conexão com o banco de dados
    include_once "f_logs.php";
    //
} else {
    header("Location: ../logout.php");
}

$lista = implode(", ", $equipamentos);

$sql = "INSERT INTO rh_equip_solic (idPessoa, usuario_final, equipamentos, observacao, criado_por) 
            values (:idPessoa, :usuario_final, :equipamentos, :observacao, :criado_por) ";
$stmt = $conn->prepare($sql);
$stmt->bindParam(':idPessoa', $idPessoa, PDO::PARAM_INT);
$stmt->bindParam(':usuario_final', $inc_usuario, PDO::PARAM_STR);
$stmt->bindParam(':criado_por', $criado_por, PDO::PARAM_STR);
$stmt->bindParam(':equipamentos', $lista, PDO::PARAM_STR);
$stmt->bindParam(':observacao', $inc_observacoes, PDO::PARAM_STR);

// Executa a inserção
if ($stmt->execute()) {
    $idSolic = $conn->lastInsertId();
    $msg = "<div class='alert alert-success'><strong>Successo!</strong> ao registrar a solicitação. ID: $idSolic</div>";
    $response = ["status" => true, "msg" => $msg];

    //
    //- INTEGRAÇÃO COM O GLPI
    // Auxílio: Danilo Marques, 20/08/2025
    //
        $app_token  = "R802Oirqd2q5PM48Jw7X265QezaKR96KeP6xSBcN";
        $user_token = "hdHEblW5Tb1Gx2WHPQPOBbvHhP7TA0kaaN3amwLP";
        $api_url    = "https://tech.gerar.org.br/apirest.php";
    
    
    // =====================
    // 1. Obter Session Token
    // =====================

        $init_session_url = $api_url . "/initSession";

        $headers = [
            "App-Token: $app_token",
            "Authorization: user_token $user_token"
        ];

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $init_session_url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

        $response = curl_exec($ch);
        $httpcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($httpcode != 200) {
            die("Erro ao iniciar sessão: HTTP $httpcode\nResposta: $response");
        }

        $data = json_decode($response, true);
        $session_token = $data["session_token"] ?? null;

        if (!$session_token) {
            die("Não foi possível obter o session_token.");
        }

        // echo "Session token: $session_token\n";

    // =====================
    // 2. Criar Ticket no GLPI
    // =====================

        $content =  "<html><head><title><h3>Solicitação de Equipamentos</h3></title></head><body><br>";
        $content .= "<p><b>Responsável: </b>$inc_responsavel</p>";
        $content .= "<p><b>Usuário final: </b>$inc_usuario</p>";
        $content .= "<p><b>Equipamentos: </b>$lista</p>";
        $content .= "<p><b>Observações: </b>$inc_observacoes</p>";
        $content .= "<p><b>Solicitante: </b>$criado_por</p></body></html>";

        $create_item_url = $api_url . "/Ticket";

        $headers = [
            "App-Token: $app_token",
            "Session-Token: $session_token",
            "Content-Type: application/json"
        ];

        $body = [
            "input" => [
                "name"    => "Reserva de Equipamentos",  // Título
                "content" => $content // Descrição
            ]
        ];

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $create_item_url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($body));

        $response = curl_exec($ch);
        $httpcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        //echo "Resposta HTTP: $httpcode\n";
        //echo "Resposta: $response\n";
        //debug(  "Resposta HTTP: $httpcode\nResposta: $response\n");

        // Decodifica a resposta JSON
        $data = json_decode($response, true);
        // Verifica se veio o ID
        if (isset($data['id'])) {
            $ticket_id = $data['id'];
            //echo "ID do ticket criado: " . $ticket_id . "\n";

            // aqui você pode salvar no banco, por exemplo
            $sql = "UPDATE rh_equip_solic SET glpi_id = :id WHERE id = :idSolic";
            $stmt = $conn->prepare($sql);
            $stmt->execute([':id' => $ticket_id, ':idSolic' => $idSolic]);
        }

} else {
    $msg = "<div class='alert alert-danger'><strong>Erro </strong> ao registrar a solicitação!</div>";
    $response = ["status" => false, "msg" => $msg];
}
$dados = json_encode($parametros, JSON_PRETTY_PRINT);
f_log("INC", "Inclusão de Reserva de Equipamentos para colaborador", "rh_equip_solic", $idModulo, $idSolic);

$conn = null;
die(json_encode($response));
