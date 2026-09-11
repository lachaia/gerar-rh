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

    <!-- E-MAIL | TELEFONE -->
    <div class="w-full bg-white shadow rounded-lg p-4 mb-3 text-left">
        <p class="text-gray-700 break-all text-center"><i class="fa-regular fa-envelope text-orange-500"></i> <?= $email_corporativo ?? 'e-Mail não informado' ?></p>
        <p class="text-gray-700 text-center"><i class="fa-solid fa-phone text-orange-500"></i> <?= $celular_corporativo ?? 'Celular não informado' ?></p>
    </div>

    <!-- CARGO -->
    <div class="w-full bg-white shadow rounded-lg p-4 mb-3 text-left">
        <table>
            <!--<tr><td class='p-2 text-gray-700'>Cargo</td> <td class='p-2'><?= $nmCargo ?? 'Cargo não informado'  ?></td></tr>-->
            <tr><td class='p-2 text-gray-700'>Função</td><td class='p-2'><?= $nmFuncao ?? 'Função não informada'?></td></tr>
            <tr><td class='p-2 text-gray-700'>Setor</td> <td class='p-2'><?= $nmOrgao ?? 'Órgão não informado'  ?></td></tr>
        </table>
    </div>
</div>
