<?php
//
//- rh_ficha_denuncia_aj.php | exibe Ficha Deuncia RH
//- (C)haia, 24/07/2025
//

session_start();

$idModulo = 15; // Acolhimento do RH

if (!isset($_SESSION['idLogin']) || !in_array((int) ($_SESSION['idGrupo'] ?? 0), [3, 9], true)) {
    http_response_code(403);
    die(json_encode(['error' => 'Acesso negado.']));
}

$parametros = filter_input_array(INPUT_POST, FILTER_DEFAULT);

if ($parametros) extract($parametros);
if (empty($id)) {
    die("FALTOU PARÂMETROS ");
}

include_once "includes/conexao_gerar.php";
include_once "includes/f_ouvidoria_cripto.php";

$sql = "SELECT * FROM rh_denuncias WHERE id = :id";
$stmt = $conn->prepare($sql);  
$stmt->bindParam(':id', $id, PDO::PARAM_INT);
$stmt->execute();

$dados = $stmt->fetch(PDO::FETCH_ASSOC);

if ($dados) {
    //- Decriptografar os campos que foram criptografados

    $dados['nome'               ] = descriptografar( $dados['nome'] );
    $dados['email'              ] = descriptografar( $dados['email'] );
    $dados['telefone'           ] = descriptografar( $dados['telefone'] );
    $dados['relato'             ] = descriptografar( $dados['relato'] );
    $dados['envolvidos'         ] = descriptografar( $dados['envolvidos'] );
    $dados['nomes_testemunhas'  ] = descriptografar( $dados['nomes_testemunhas'] );
    $dados['resposta_comunicado'] = descriptografar( $dados['resposta_comunicado'] );
    $dados['nome_contato'       ] = descriptografar( $dados['nome_contato'] );
    $dados['email_contato'      ] = descriptografar( $dados['email_contato'] );
    $dados['telefone_contato'   ] = descriptografar( $dados['telefone_contato'] );

    //- Retornar os dados como JSON
    //header('Content-Type: application/json');
    echo json_encode($dados);
} else {
    echo json_encode(['error' => 'Registro não encontrado.']);
}