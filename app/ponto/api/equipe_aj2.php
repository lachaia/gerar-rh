<?php 
//
//- equipe_aj.php | Retorna dados do Colaborador
//- (C)haia, 27/11/2025
//

session_start();

include dirname(__DIR__) . '/../includes/conexao_gerar.php';

$colab_id = $_POST['idColab'] ?? null;

if (empty($colab_id)) {
    header('Location: ../logout.php');
    exit();
}

//
//- LÊ DADOS DO COLABORADOR
//
    $sql = "SELECT P.nome, U.foto, CG.nome as nmCargo, F.nome as nmFuncao, O.descricao as nmOrgao,
                    P.email_corporativo, P.celular_corporativo,
                    (
                        select concat(logradouro,', ',numero, ', ', complemento, ', ', bairro, ', ', trim(cidade), '-', uf) 
                        from rh_enderecos E 
                        where E.idPessoa = P.idPessoa and E.idTipoEndereco = 1 limit 1
                    ) as endereco 
            FROM rh_colaboradores C
            INNER JOIN rh_pessoas P on P.idPessoa = C.idPessoa
            LEFT OUTER JOIN rh_cargos CG on CG.idCargo = C.idCargo
            LEFT OUTER JOIN rh_usuarios U on U.idColab = C.idColab
            LEFT OUTER JOIN rh_funcoes F on F.idFuncao = C.idFuncao
            LEFT OUTER JOIN rh_organograma O on O.idOrgao = C.idOrgao
            WHERE C.idColab = :idColab";
    $stmt = $conn->prepare($sql);
    $stmt->bindValue(':idColab', $colab_id, PDO::PARAM_INT);
    $stmt->execute();
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    extract($row);

//
//- PREPARA FOTO DE PERFIL
//
    if( empty($foto) || $foto == 'perfil.png') {
        $url_foto = "https://ui-avatars.com/api/?name=$nome";
    }else{
        $url_foto = "../../fotos/$foto";
    }


//
//- LÊ BATIDAS DO DIA
//
    $sql = "SELECT tipo, data_hora
            FROM rh_ponto_registros
            WHERE colaborador_id = :idColab AND DATE(data_hora) = CURDATE()";
    $stmt = $conn->prepare($sql);
    $stmt->bindValue(':idColab', $colab_id, PDO::PARAM_INT);
    $stmt->execute();
    $registros = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<div class="flex flex-col items-center text-center p-4">

    <!-- FOTO -->
    <div class="w-32 h-32 mb-4">
        <img src="<?= $url_foto ?>" 
             class="w-full h-full object-cover rounded-full shadow-md border" />
    </div>

    <!-- NOME -->
    <h2 class="text-xl font-bold text-gray-800 mb-2">
        <?= $nome ?>
    </h2>

    <!-- ENDEREÇO -->
    <div class="w-full bg-white shadow rounded-lg p-4 mb-3 text-left">
        <p class="text-gray-700 break-all text-center"><?= $endereco ?? 'Endereço não informado' ?></p>
    </div>

    <!-- E-MAIL | TELEFONE -->
    <div class="w-full bg-white shadow rounded-lg p-4 mb-3 text-left">
        <p class="text-gray-700 break-all text-center"><i class="fa-regular fa-envelope text-orange-500"></i> <?= $email_corporativo ?? 'e-Mail não informado' ?></p>
        <p class="text-gray-700 text-center"><i class="fa-solid fa-phone text-orange-500"></i> <?= $celular_corporativo ?? 'Celular não informado' ?></p>
    </div>

    <!-- CARGO -->
    <div class="w-full bg-white shadow rounded-lg p-4 mb-3 text-left">
        <table class="w-full">
            <tr><td class='p-2 text-gray-700'>Cargo</td> <td class='p-2'><?= $nmCargo  ?? 'Cargo não informado'  ?></td></tr>
            <tr><td class='p-2 text-gray-700'>Função</td><td class='p-2'><?= $nmFuncao ?? 'Função não informada' ?></td></tr>
            <tr><td class='p-2 text-gray-700'>Setor</td> <td class='p-2'><?= $nmOrgao  ?? 'Órgão não informado'  ?></td></tr>
        </table>
    </div>

    <!-- BATIDAS DO DIA -->
    <div class="w-full bg-white shadow rounded-lg p-4 mb-3">
        <p class="text-lg text-gray-700 text-center m-2">Batidas do dia</p>
        <?php
            if( empty($registros) ) {
                echo "<p class='text-center text-xl mb-2 bg-gray-700 p-2 text-white'>Nenhuma batida hoje</p>";
                exit;
            }else{?>
                <table class="w-full text-center border-2 border-gray-700">
                    <thead><tr><th>Batida</th><th>Tipo</th></tr></thead>
                    <tbody><?php 
                        foreach ($registros as $registro) {
                            $tipo = $registro['tipo'];
                            $batida = substr($registro['data_hora'],11,5);
                            echo "<tr><td>$batida</td><td>$tipo</td>";
                        }?>                        
                    </tbody>
                </table><?php
            }
        ?>
    </div>
</div>
