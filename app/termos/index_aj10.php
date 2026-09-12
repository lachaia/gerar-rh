<?php
//
//- rh_index_aj10.php | Salva Novo Termo de Responsabilidade
//- (C)haia, 26/08/2025
//

session_start();
header('Content-Type: application/json; charset=utf-8');
ob_start();

function json_saida($status, $msg)
{
    if (ob_get_length()) {
        ob_clean();
    }
    echo json_encode([
        "status" => (bool)$status,
        "msg" => $msg
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

set_exception_handler(function ($e) {
    json_saida(false, '<div class="alert alert-danger"><strong>ERRO!</strong> Falha na inclusão do termo: ' . htmlspecialchars($e->getMessage(), ENT_QUOTES, 'UTF-8') . '</div>');
});

register_shutdown_function(function () {
    $err = error_get_last();
    if ($err && in_array($err['type'], [E_ERROR, E_PARSE, E_CORE_ERROR, E_COMPILE_ERROR], true)) {
        json_saida(false, '<div class="alert alert-danger"><strong>ERRO!</strong> Erro fatal na inclusão do termo.</div>');
    }
});

$autoload = __DIR__ . '/../vendor/autoload.php';
if (!file_exists($autoload)) {
    json_saida(false, '<div class="alert alert-danger"><strong>ERRO!</strong> Dependências ausentes no servidor (vendor/autoload.php).</div>');
}
include $autoload;
use Dompdf\Dompdf;
use Dompdf\Options;

$idModulo = 19; // Equipamentos

$parametros = filter_input_array(INPUT_POST, FILTER_DEFAULT);
if (isset($parametros)) extract($parametros);
/*
include_once "../includes/debug.php";
debug(json_encode($parametros, JSON_PRETTY_PRINT));

$retorno = [
    "status" => true,
    "msg" => '<div class="alert alert-primary">
        <strong>OK!</strong> Sucesso no Teste de Sistema!
        </div>'
];
die( json_encode( $retorno ) );
/*

 index_aj10.php | 2025-09-18 09:08:35 
{
    "inc_termo_reserva": "1",
    "termo_inc_nome": "Luiz Augusto Chaia",
    "termo_idPessoa": "1",
    "termo_idModelo": "1",
    "termo_cpf": "57160600991",
    "termo_cep": "81315420",
    "termo_endereco": "Rua Hamilton Taborda Ribas",
    "termo_end_nro": "80",
    "termo_end_cpl": "casa 2",
    "termo_bairro": "Cidade Industrial",
    "termo_cidade": "Curitiba",
    "termo_uf": "PR",
    "termo_email": "luiz.chaia@gmail.com",
    "termo_telefone": "+5542988668668",
    "termo_observacoes": "",
    "termo_data_dev": "2025-09-18T09:02",
    "termo_user_dev": "luiz.chaia",
    "termo_observacoes_dev": "",
    "idTipoEquip": [
        "1",
        "2",
        "3"
    ],
    "modelo": [
        "Lenovo i5",
        "Motorola G34",
        "41 99700-4083"
    ],
    "patrimonio": [
        "5343",
        "TIM0020",
        "e-sim"
    ],
    "termo_dt_entrega": "2025-01-01T10:10",
    "termo_status": ""
}
*/
// Lista de campos obrigat�rios simples
$camposObrigatorios = [
    'termo_inc_nome',
    'termo_idModelo',
    'termo_endereco',
    'termo_end_nro',
    'termo_bairro',
    'termo_cidade',
    'termo_uf',
    'termo_cep',
    'termo_email',
    'termo_telefone'
];

// Verifica campos simples
foreach ($camposObrigatorios as $campo) {
    if (empty($_POST[$campo])) {
        $retorno = [
            "status" => false,
            "msg" => '<div class="alert alert-danger">
                        <strong>Erro!</strong> Campo obrigatório ausente: ' . $campo . '.
                      </div>'
        ];
        die(json_encode($retorno));
    }
}

// Verifica arrays obrigat�rios
$arraysObrigatorios = ['idTipoEquip', 'modelo', 'patrimonio'];

foreach ($arraysObrigatorios as $array) {
    if (empty($_POST[$array]) || !is_array($_POST[$array]) || count($_POST[$array]) == 0) {
        $retorno = [
            "status" => false,
            "msg" => '<div class="alert alert-danger">
                        <strong>Erro!</strong> Lista obrigatória ausente ou vazia: ' . $array . '.
                      </div>'
        ];
        die(json_encode($retorno));
    }
}

if (isset($_SESSION['idLogin']) && in_array((int) ($_SESSION['idGrupo'] ?? 0), [4, 7, 9], true)) {
    $idLogin = $_SESSION['idLogin'];
    $nmLogin = $_SESSION['nmLogin'];
    $idEmpresa = $_SESSION['idEmpresa'];
    //
    include_once "../includes/conexao_gerar.php";
    include_once "../includes/f_logs.php";
    include_once "../includes/f_linha_do_tempo.php";
    include_once "../includes/f_upload_seguro.php";
} else {
    header("location: logout.php");
    exit();
}

//
//- SE PESSOA não EXISTE, CRIA
//

if( empty($termo_idPessoa) ){

    $sql = "INSERT INTO rh_pessoas (nome, nomeSocial, cpf, telefone, email, idLogin) 
                VALUES (:nome, :nome, :cpf, :telefone, :email, :idLogin)";
    $stmt = $conn->prepare($sql);
    $stmt->bindParam(':nome', $termo_inc_nome);
    $stmt->bindParam(':cpf', $termo_cpf);
    $stmt->bindParam(':telefone', $termo_telefone);
    $stmt->bindParam(':email', $termo_email);
    $stmt->bindParam(':idLogin', $idLogin);
    $stmt->execute();
    $termo_idPessoa = $conn->lastInsertId();
    //
    $idTipoEndereco = 1;
    if( empty($termo_end_cpl) ) $termo_end_cpl = NULL;

    $sql = "INSERT INTO rh_enderecos (idEmpresa, idPessoa, idTipoEndereco, logradouro, numero, complemento, cep, bairro, cidade, uf, idLogin) 
                VALUES (:idEmpresa, :idPessoa, :idTipoEndereco, :logradouro, :numero, :complemento, :cep, :bairro, :cidade, :uf, :idLogin)";
    $stmt = $conn->prepare($sql);
    $stmt->bindParam(':idEmpresa', $idEmpresa);
    $stmt->bindParam(':idPessoa', $termo_idPessoa);
    $stmt->bindParam(':idTipoEndereco', $idTipoEndereco, PDO::PARAM_INT);
    $stmt->bindParam(':logradouro', $termo_endereco);
    $stmt->bindParam(':numero', $termo_end_nro);
    $stmt->bindParam(':complemento', $termo_end_cpl);
    $stmt->bindParam(':cep', $termo_cep);
    $stmt->bindParam(':bairro', $termo_bairro);
    $stmt->bindParam(':cidade', $termo_cidade);
    $stmt->bindParam(':uf', $termo_uf);
    $stmt->bindParam(':idLogin', $idLogin);
    $stmt->execute();
}
//
if (empty($termo_status)){
    $termo_status = "Pendente";
    $termo_data_dev = null;
    $termo_user_dev = null;
    $termo_observacoes_dev = null;
}

$sql = "INSERT INTO rh_equip_termos (
                idPessoa, status, criado_por, idLogin, idModelo, vistoria_entrega,
                termo_nome, termo_endereco, termo_end_numero, termo_end_cpl,
                termo_bairro, termo_cidade, termo_uf, termo_cep, termo_email, termo_celular,
                data_devolucao, user_devolucao, vistoria_devolucao, origem, data_entrega
            ) VALUES (
                :idPessoa, :status, :criado_por, :idLogin, :idModelo, :vistoria_entrega,
                :termo_nome, :termo_endereco, :termo_end_numero, :termo_end_cpl,
                :termo_bairro, :termo_cidade, :termo_uf, :termo_cep, :termo_email, :termo_celular,
                :data_devolucao, :user_devolucao, :vistoria_devolucao, 'e', :data_entrega
            )";

$stmt = $conn->prepare($sql);

$termo_cep = limpa_cep( $termo_cep );

//$stmt->bindValue(':idSolic', $inc_termo_reserva);
$stmt->bindValue(':idPessoa', $termo_idPessoa);
$stmt->bindValue(':status', $termo_status);
$stmt->bindValue(':criado_por', $nmLogin);
$stmt->bindValue(':idLogin', $idLogin);
$stmt->bindValue(':idModelo', $termo_idModelo);
$stmt->bindValue(':vistoria_entrega', $termo_observacoes);

$stmt->bindValue(':termo_nome', $termo_inc_nome);
$stmt->bindValue(':termo_endereco', $termo_endereco);
$stmt->bindValue(':termo_end_numero', $termo_end_nro);
$stmt->bindValue(':termo_end_cpl', $termo_end_cpl ?: null); // manda NULL se vazio
$stmt->bindValue(':termo_bairro', $termo_bairro ?: null);
$stmt->bindValue(':termo_cidade', $termo_cidade);
$stmt->bindValue(':termo_uf', $termo_uf);
$stmt->bindValue(':termo_cep', $termo_cep);
$stmt->bindValue(':termo_email', $termo_email ?: null);
$stmt->bindValue(':termo_celular', $termo_telefone ?: null);

$stmt->bindValue(':data_devolucao', $termo_data_dev ?: null);
$stmt->bindValue(':data_entrega', $termo_dt_entrega ?: null);
$stmt->bindValue(':user_devolucao', $termo_user_dev ?: null);
$stmt->bindValue(':vistoria_devolucao', $termo_observacoes_dev ?: null);

if ($stmt->execute()) {
    //
    //- Inserir Itens do Termo
    //

    $idEquipTermo = $conn->lastInsertId();

    $sql = "INSERT INTO rh_equip_termos_ld (idEquipTermo, idTipo, modelo, patrimonio) 
            VALUES (:idEquipTermo, :idTipo, :modelo, :patrimonio)";
    $stmt = $conn->prepare($sql);

    // Percorre os arrays e insere os dados
    for ($i = 0; $i < count($idTipoEquip); $i++) {
        $stmt->execute([
            ':idEquipTermo' => $idEquipTermo,
            ':idTipo'       => $idTipoEquip[$i],
            ':modelo'       => $modelo[$i],
            ':patrimonio'   => $patrimonio[$i]
        ]);
    }

    //- Linha do Tempo
    $tipo = 37; // Cria��o de Termo de Responsabilidade
    $quando = date("Y-m-d H:i:s");
    $descricao = "Termo de Responsabilidade #$idEquipTermo criado para " . $termo_inc_nome;
    f_ldt($tipo, $termo_idPessoa, $descricao, $idEquipTermo, $quando, "EQP");

    $retorno = [
        "status" => true,
        "msg" => '<div class="alert alert-success">
        <strong>OK!</strong> Sucesso ao Inserir Termo!
        </div>'
    ];
 
    //
    //- UPLOAD DO DOCUMENTO PRONTO
    //
 
    if (!empty($_FILES['termo_arquivo']['name'])) {
        $validacao = upload_seguro_validar($_FILES['termo_arquivo'], ['pdf', 'jpg', 'jpeg', 'png', 'gif', 'webp', 'bmp']);
        if ($validacao !== true) {
            $conn = null;
            die(json_encode(["status" => false, "msg" => $validacao]));
        }

        // Pasta destino
        $destinoDir = "../docs/pessoa_" . intval($termo_idPessoa) . "/";

        // Cria a pasta caso não exista
        if (!is_dir($destinoDir)) {
            mkdir($destinoDir, 0777, true);
        }

        // Define nome do arquivo destino
        $ext = pathinfo($_FILES['termo_arquivo']['name'], PATHINFO_EXTENSION);
        $nomeDestino = "responsa_" . intval($idEquipTermo) . "." . $ext;

        $destino = $destinoDir . $nomeDestino;

        if (move_uploaded_file($_FILES['termo_arquivo']['tmp_name'], $destino)) {
            $resposta["status"] = true;
            $resposta["msg"] = "<div class='alert alert-success'>Arquivo enviado com sucesso!</div>";
        } else {
            $resposta["msg"] = "<div class='alert alert-danger'>Falha ao mover o arquivo!</div>";
        }
        //
        // Atualiza o nome do arquivo no banco de dados
        //
        
        $sql = "UPDATE rh_equip_termos 
                    SET arquivo = :arquivo, origem='d' WHERE id = :id";

        $stmt = $conn->prepare($sql);
        $stmt->bindParam(':arquivo', $nomeDestino, PDO::PARAM_STR);
        $stmt->bindParam(':id', $idEquipTermo, PDO::PARAM_INT);
        $stmt->execute();
        die(json_encode($retorno));
    } 

    //
    // - GERAR PDF do Termo
    //

    $sql = "SELECT texto FROM rh_equip_modelos WHERE id = :idModelo";
    $stmt = $conn->prepare($sql);
    $stmt->bindParam(':idModelo', $termo_idModelo);
    $stmt->execute();

    if ($stmt->rowCount() > 0) {
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        $htmlModelo = $row['texto'];
    } else {
        $htmlModelo = "<p>Modelo de Termo não encontrado.</p>";
    }

    // Dados do colaborador
    $dados = [
        '{{NOME}}'        => htmlspecialchars($termo_inc_nome),
        '{{ENDERECO}}'    => htmlspecialchars($termo_endereco),
        '{{NUMERO}}'      => htmlspecialchars($termo_end_nro),
        '{{COMPLEMENTO}}' => htmlspecialchars($termo_end_cpl),
        '{{BAIRRO}}'      => htmlspecialchars($termo_bairro),
        '{{CIDADE}}'      => htmlspecialchars($termo_cidade),
        '{{UF}}'          => htmlspecialchars($termo_uf),
        '{{CEP}}'         => htmlspecialchars($termo_cep),
        '{{EMAIL}}'       => htmlspecialchars($termo_email),
        '{{TELEFONE}}'    => htmlspecialchars($termo_telefone)
    ];

    // Substitui os placeholders
    $termo = strtr($htmlModelo, $dados);

    // Gerar a lista de equipamentos em HTML
    $listaEquipamentosHTML = "<table border='1' cellpadding='5' cellspacing='0' style='width: 100%; border-collapse: collapse;'>
        <thead>
            <tr>
                <th style='background-color: #f2f2f2;'>Tipo</th>
                <th style='background-color: #f2f2f2;'>Modelo</th>
                <th style='background-color: #f2f2f2;'>Patrim�nio</th>
            </tr>
        </thead>
        <tbody>";

    for ($i = 0; $i < count($idTipoEquip); $i++) {
        // Obter o nome do tipo de equipamento
        $sql = "SELECT descricao FROM rh_equip_tipos WHERE id = :idTipo";
        $stmt = $conn->prepare($sql);
        $stmt->bindParam(':idTipo', $idTipoEquip[$i]);
        $stmt->execute();
        $tipoEquip = ($stmt->rowCount() > 0) ? $stmt->fetch(PDO::FETCH_ASSOC)['descricao'] : 'Desconhecido';

        $listaEquipamentosHTML .= "<tr>
            <td>" . htmlspecialchars($tipoEquip) . "</td>
            <td>" . htmlspecialchars($modelo[$i]) . "</td>
            <td>" . htmlspecialchars($patrimonio[$i]) . "</td>
        </tr>";
    }
    $listaEquipamentosHTML .= "</tbody></table>";

    // Insere a lista de equipamentos no termo
    $termo = str_replace('{{LISTA_EQUIPAMENTOS}}', $listaEquipamentosHTML, $termo);

    // Monta o HTML final com a logomarca

    $hoje = date("d/m/Y H:i:s");

    $html = '
        <html>
        <head>
            <meta charset="utf-8">
            <style>
                body { font-family: Arial, sans-serif; font-size: 14px; margin: 0; padding: 0; }
                .logo {
                    position: absolute;
                    top: 10px;
                    right: 10px;
                    width: 80px; /* tamanho da logo */
                }
                .titulo { 
                    text-align: center; 
                    font-size: 18px; 
                    font-weight: bold; 
                    margin-top: 120px; /* empurra conte�do para baixo da logo */
                    margin-bottom: 20px; 
                }
                .conteudo { text-align: justify; margin: 20px; }
                table { border-collapse: collapse; width: 100%; margin-top: 15px; }
                th, td { border: 1px solid #000; padding: 5px; }
                th { background-color: #f2f2f2; }
                h5 { text-align: center}
                .data_assinatura { text-align: center; margin-top: 40px;}
            </style>
        </head>
        <body>
            <img src="https://rh.gerar.org.br/imagens/logo.png" class="logo" alt="Logo">
            <div class="conteudo">
                ' . $termo . '
            </div>
            <div class="conteudo">
            <h5>Vistoria de Entrega/Observações</h5>
            <p>' . $termo_observacoes . ' | Curitiba, ' . $termo_dt_entrega . '</p>
            </div>';

    //- o fechamento vem com a assinatura

    // Gera o PDF com DOMPDF

    $options = new Options();
    $options->set('isRemoteEnabled', true); // permite carregar imagens locais ou remotas

    $dompdf = new Dompdf($options);

    $dompdf->loadHtml($html);
    $dompdf->setPaper('A4', 'portrait');
    $dompdf->render();

    // Exibe no navegador ou força download
    // $dompdf->stream("termo_responsabilidade.pdf", ["Attachment" => false]);

    $arquivo = "responsa_$idEquipTermo.pdf";
    $destinoDir = "../docs/pessoa_$termo_idPessoa/";
    $url = $destinoDir . $arquivo;

    if (!is_dir($destinoDir) && !mkdir($destinoDir, 0777, true)) {
        $retorno = [
            "status" => false,
            "msg" => '<div class="alert alert-danger"><strong>ERRO!</strong> não foi possível criar pasta do documento.</div>'
        ];
        die(json_encode($retorno));
    }

    //debug( $url );

    $pdf = $dompdf->output();
    if (file_put_contents($url, $pdf) === false) {
        $retorno = [
            "status" => false,
            "msg" => '<div class="alert alert-danger"><strong>ERRO!</strong> não foi possível gravar o PDF do termo.</div>'
        ];
        die(json_encode($retorno));
    }

    //
    //- ENVIA E-MAIL COM O LINK PARA ASSINATURA
    //
    // Gera link único
    $token = bin2hex(random_bytes(16));
    $expira = date("Y-m-d H:i:s", strtotime("+3 days"));

    $sql = "UPDATE rh_equip_termos 
                SET termo_html = :html, arquivo = :arquivo, token = :token, expira = :expira
                WHERE id = :id";

    $stmt = $conn->prepare($sql);
    $stmt->bindParam(':html', $html, PDO::PARAM_STR);
    $stmt->bindParam(':arquivo', $arquivo, PDO::PARAM_STR);
    $stmt->bindParam(':token', $token, PDO::PARAM_STR);
    $stmt->bindParam(':expira', $expira, PDO::PARAM_STR);
    $stmt->bindParam(':id', $idEquipTermo, PDO::PARAM_INT);
    $stmt->execute();

    $link = "https://rh.gerar.org.br/app/termos/assinar.php?token=$token";

    include_once "../includes/inc_email.php";

    $emailFrom = "ti@gerar.org.br";
    $nmFrom    = "GERAR SISTEMAS";
    $titulo    = "Termo de Responsabilidade - Assinatura";
    //
    ob_start();

    $mail->isHTML(true);
    $mail->Subject = $titulo;
    $mail->setFrom($emailFrom, $nmFrom);

    $mail->addAddress($termo_email, $termo_inc_nome);   // Add a recipient 
    //$mail->addAddress( "ti@gerar.org.br", 'TI');   // Add a recipient 

    $html_email = "Olá, clique no link abaixo para assinar o termo:<br><br><a href='$link'>$link</a>";

    $mail->Body = $html_email;
    $mail->send();
    // Limpa qualquer saída que possa ter acontecido
    ob_end_clean();
} else {
    $retorno = [
        "status" => false,
        "msg" => '<div class="alert alert-danger">
        <strong>ERRO!</strong> Falha ao Inserir Termo!
        </div>'
    ];
    die(json_encode($retorno));
}

die(json_encode($retorno));


function limpa_cep( $termo_cep ){
    return preg_replace( '/[^0-9]/', '', $termo_cep );
}