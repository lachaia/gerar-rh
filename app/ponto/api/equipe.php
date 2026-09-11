<?php
//
//- equipe.php | Minha Equipe
//- (C)haia, 27/11/2025

header('Content-Type: text/html; charset=utf-8');
date_default_timezone_set('America/Sao_Paulo');
setlocale(LC_TIME, 'pt_BR.utf8', 'pt_BR', 'portuguese');

session_start();

if (!isset($_SESSION['idLogin'])) {
    header('Location: ../logout.php');
    exit();
}

include dirname(__DIR__) . '/../includes/conexao_gerar.php';

// Recebe o colaborador logado
$colab_id = $_SESSION['idColab'] ?? null;
$orgao_id = $_SESSION['idOrgao'] ?? null;

if (empty($colab_id)) {
    header('Location: ../logout.php');
    exit();
}

//
//- LISTA DOS COLABORADORES QUE TRABALHAM COMIGO
//
    $sql = "SELECT C.idColab, O.descricao, P.nome, U.foto
                FROM rh_colaboradores C
                INNER JOIN rh_organograma O on O.idOrgao = C.idOrgao
                INNER JOIN rh_pessoas P on P.idPessoa = C.idPessoa
                LEFT OUTER JOIN rh_usuarios U on U.idColab = C.idColab
                WHERE O.idOrgao = :orgao_id AND C.data_rescisao IS NULL
                ORDER BY P.nome";
    $stmt = $conn->prepare($sql);
    $stmt->bindParam(':orgao_id', $orgao_id, PDO::PARAM_INT);
    $stmt->execute();
    $trabalham_comigo = $stmt->fetchAll(PDO::FETCH_ASSOC);

//
//- LISTA DOS SUBORDINADOS
//

    $listaColabs = getSubordinados($orgao_id, $conn);

    $sql = "SELECT C.idColab, O.descricao, P.nome, U.foto
                FROM rh_colaboradores C
                INNER JOIN rh_organograma O on O.idOrgao = C.idOrgao
                INNER JOIN rh_pessoas P on P.idPessoa = C.idPessoa
                LEFT OUTER JOIN rh_usuarios U on U.idColab = C.idColab
                WHERE C.data_rescisao IS NULL and C.idColab in (" . implode(",", $listaColabs) . ")
                ORDER BY P.nome";
    $stmt = $conn->prepare($sql);
    $stmt->execute();
    $subordinados = $stmt->fetchAll(PDO::FETCH_ASSOC);

//
//- Busca dados do Supervisor na API
//
    $url = "https://rh.gerar.org.br/api/api_supervisor.php?id=" . $colab_id;
    $resposta = file_get_contents($url);
    $dados = json_decode($resposta, true); // converte JSON em array associativo

    if ($dados) {
        extract($dados);
        //
        if( empty($gestor_foto) || $gestor_foto == 'perfil.png') {
            $url_foto_gestor = "https://ui-avatars.com/api/?name=$gestor_nome";
        }else{
            $url_foto_gestor = "../../fotos/$gestor_foto";
        }        
    } else {
        die("FALTA O CADASTRO DO SUPERVISOR DO COLABORADOR - INFORME O RH...");
    }

    ?>

<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ponto Eletrônico</title>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://use.fontawesome.com/releases/v6.3.0/js/all.js" crossorigin="anonymous"></script>
    <style>
        body {
            font-family: 'Inter', sans-serif;
            background-color: #f7f9fb;
        }

        .card {
            box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1),
                0 4px 6px -2px rgba(0, 0, 0, 0.05);
        }

        #horaAtual {
            color: #0a47ca;
            font-weight: bold;
        }
    </style>
</head>

<body class="bg-gray-100 p-6">
    <input type="hidden" id="idColab" value="<?= $idColab ?>">

    <!-- BOTÕES SUPERIORES -->
    <div class="relative flex items-center justify-center text-gray-600 mt-2 mb-8 text-xl font-bold">
        <a href="../index.php"
            id="botaoInicio"
            class="fixed top-4 left-4 p-2 bg-white/80 backdrop-blur shadow-md rounded-full hover:bg-blue-100 transition"
            title="Início">
            <i class="fa-solid fa-house text-gray-500"></i>
        </a>

        <!-- Botão Voltar -->
        <a href="equipe.php"
            id="botaoVoltar"
            class="hidden fixed top-4 left-16 p-2 bg-white/80 backdrop-blur shadow-md rounded-full hover:bg-blue-100 transition"
            title="Voltar">
            <i class="fa-solid fa-chevron-left text-gray-500"></i>
        </a>

        <a href="../logout.php"
            class="fixed top-4 right-4 p-2 bg-white/80 backdrop-blur shadow-md rounded-full hover:bg-red-100 transition"
            title="Sair">
            <i class="fa-solid fa-right-from-bracket text-gray-500"></i>
        </a>
        Minha Equipe
    </div>
    <div id='divEquipe'>
        <?php 
        //- Meu SUPERVISOR
        echo "<p class='text-center text-xl mb-2 bg-gray-700 p-2 text-white'>Meu Gestor</p>";
        echo "<div class='max-w-md mx-auto bg-white rounded-lg shadow-md p-2 mb-2' onclick='ver($gestor_id)'>
                <div class='flex items-center'>
                    <div class='flex-shrink-0'>
                        <img src='$url_foto_gestor' alt='$gestor_nome' class='w-10 h-10 rounded-full text-blue-500'>
                    </div>
                    <div class='ml-4'>
                        <p class='text-base font-semibold text-gray-700'>$gestor_nome</p>
                        <p class='text-sm text-gray-600'>$gestor_funcao</p>                        
                    </div>
                </div>
            </div>";
        //- LISTA DOS QUE TRABALHAM COMIGO
        if(!empty($trabalham_comigo)) { 
            echo "<p class='text-center text-xl mb-2 bg-gray-700 p-2 text-white'>No mesmo Setor</p>";
            foreach ($trabalham_comigo as $colab) {
                $nome = $colab['nome'];
                $descricao = $colab['descricao'];
                $idColab = $colab['idColab'];
                $foto = $colab['foto'];
                if( empty($foto) || $foto == 'perfil.png') {
                    $url_foto = "https://ui-avatars.com/api/?name=$nome";
                }else{
                    $url_foto = "../../fotos/$foto";
                }
                $status = f_status( $conn, $idColab );
                //
                if( $status == 'Ausente') $status = "<i class='text-red-600'>Ausente</i>";
                //
                echo "<div class='max-w-md mx-auto bg-white rounded-lg shadow-md p-2 mb-2' onclick='ver($idColab)'>
                        <div class='flex items-center'>
                            <div class='flex-shrink-0'>
                                <img src='$url_foto' alt='$nome' class='w-10 h-10 rounded-full text-blue-500'>
                            </div>
                            <div class='ml-4'>
                                <p class='text-base font-semibold text-gray-700'>$nome</p>
                                <p class='text-sm text-gray-600'>$descricao</p>
                                
                            </div>
                        </div>
                        <p class='text-sm text-green-600 text-right mt-2'>$status</p>
                    </div>";    
            }
            ?>
            <?php 
        }
        //- LISTA DOS SUBORDINADOS
        if(!empty($subordinados)) { 
            echo "<p class='text-center text-xl mb-2 bg-gray-700 p-2 text-white'>Que sou gestor</p>";
            foreach ($subordinados as $colab) {
                $nome = $colab['nome'];
                $descricao = $colab['descricao'];
                $idColab = $colab['idColab'];
                $foto = $colab['foto'];
                if( empty($foto) || $foto == 'perfil.png') {
                    $url_foto = "https://ui-avatars.com/api/?name=$nome";
                }else{
                    $url_foto = "../../fotos/$foto";
                }
                $status = f_status( $conn, $idColab );
                //
                if( $status == 'Ausente') $status = "<i class='text-red-600'>Ausente</i>";
                //
                echo "<div class='max-w-md mx-auto bg-white rounded-lg shadow-md p-2 mb-2' onclick='ver_sub($idColab)'>
                        <div class='flex items-center'>
                            <div class='flex-shrink-0'>
                                <img src='$url_foto' alt='$nome' class='w-10 h-10 rounded-full text-blue-500'>
                            </div>
                            <div class='ml-4'>
                                <p class='text-base font-semibold text-gray-700'>$nome</p>
                                <p class='text-sm text-gray-600'>$descricao</p>
                                
                            </div>
                        </div>
                        <p class='text-sm text-green-600 text-right mt-2'>$status</p>
                    </div>";    
            }
            ?>
            <?php 
        }
        ?>
    </div>
    <div id='divColaborador' class="hidden">
        <div id='divBody'></div>
    </div>
</div>
<script>

    function botao_voltar() {
        const botaoVoltar = document.getElementById('botaoVoltar');
        const divEquipe   = document.getElementById('divEquipe');
        const divColab    = document.getElementById('divColaborador');
        //
        divEquipe.classList.remove('hidden');
        divColab.classList.add('hidden');
        botaoVoltar.classList.add('hidden');
    }

    function ver( $idColab ) {
        const botaoVoltar = document.getElementById('botaoVoltar');
        const divEquipe = document.getElementById('divEquipe');
        const divColab  = document.getElementById('divColaborador');
        const divBody   = document.getElementById('divBody');
        //
        divEquipe.classList.add('hidden');
        botaoVoltar.classList.remove('hidden');
        divColab.classList.remove('hidden');
        //
        $.post(
            "equipe_aj.php",
            {
                idColab: $idColab
            },
            function ( zhtml) {
                divBody.innerHTML = zhtml;
            }
        );
    }

    function ver_sub( $idColab ) {
        const botaoVoltar = document.getElementById('botaoVoltar');
        const divEquipe = document.getElementById('divEquipe');
        const divColab  = document.getElementById('divColaborador');
        const divBody   = document.getElementById('divBody');
        //
        divEquipe.classList.add('hidden');
        divColab.classList.remove('hidden');
        botaoVoltar.classList.remove('hidden');
        //
        $.post(
            "equipe_aj2.php",
            {
                idColab: $idColab
            },
            function ( zhtml) {
                divBody.innerHTML = zhtml;
            }
        );
    }
    
</script>
</body>
</html>
<?php

function f_status( $conn, $idColab ) {
    $sql = "SELECT tipo
            FROM rh_ponto_registros 
            WHERE colaborador_id = :idColab AND DATE(data_hora) = CURDATE()
            ORDER BY data_hora DESC 
            LIMIT 1";
    $stmt = $conn->prepare( $sql );
    $stmt->bindParam( ':idColab', $idColab, PDO::PARAM_INT );
    $stmt->execute();
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    //
    if( ! $row ) return "Ausente"; //- sem registros
    $tipo = strtolower(trim($row['tipo'])); //- normaliza para evitar erros
    if( $tipo =='entrada') return 'Trabalhando';
    return "Ausente";
}

function getSubordinados($idOrgao, $pdo) {
    // 1. Buscar a linha do organograma do gestor
    $sql = "SELECT * FROM rh_organograma WHERE idOrgao = :id";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([':id' => $idOrgao]);
    $gestor = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$gestor) {
        return [];
    }

    // 2. Descobrir em qual nível o gestor está (último nível > 0)
    $nivelGestor = 0;
    for ($i = 1; $i <= 7; $i++) {
        if (!empty($gestor["nivel_$i"]) && $gestor["nivel_$i"] > 0) {
            $nivelGestor = $i;
        }
    }

    // 3. Montar condição dinâmica para os níveis anteriores
    $conds = [];
    $params = [];

    for ($i = 1; $i <= $nivelGestor; $i++) {
        $conds[] = "O.nivel_$i = :n$i";
        $params[":n$i"] = $gestor["nivel_$i"];
    }

    // 4. O próximo nível precisa ser > 0
    $proximoNivel = $nivelGestor + 1;
    if ($proximoNivel <= 7) {
        $conds[] = "O.nivel_$proximoNivel > 0";
    }

    // 5. Montar SQL final
    $where = implode(" AND ", $conds);

    $sql = "
        SELECT C.idColab
        FROM rh_colaboradores C
        INNER JOIN rh_organograma O ON O.idOrgao = C.idOrgao
        WHERE $where
    ";

    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);

    // 6. Extrair apenas os IDs em um vetor simples
    $ids = [];
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $ids[] = $row["idColab"];
    }

    return $ids;
}