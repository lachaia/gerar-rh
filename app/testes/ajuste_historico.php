<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
   <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/5.3.0/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css">
    <script src="https://use.fontawesome.com/releases/v6.3.0/js/all.js" crossorigin="anonymous"></script>
    <link href="css/styles.css" rel="stylesheet" />
    <link href="css/rh_pessoas.css" rel="stylesheet" />

    <!-- Summernote CSS -->
    <link href="https://cdn.jsdelivr.net/npm/summernote@0.8.18/dist/summernote.min.css" rel="stylesheet">

    <!-- Inclua os arquivos do DataTables -->
    <script src="https://code.jquery.com/jquery-3.7.0.js"></script>

    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

    <!-- Summernote JS -->
    <script src="https://cdn.jsdelivr.net/npm/summernote@0.8.18/dist/summernote.min.js"></script>    
</head>
<body>
    
</body>
</html>
<?PHP

include_once __DIR__ . "/includes/conexao_gerar.php";

$sql = "truncate table rh_historico_sal;
        truncate table rh_historico_funcao;
        truncate table rh_historico_orgao;
        truncate table rh_historico_cargo;"; 
$res = $conn->prepare($sql);
$res->execute();

$sql = "SELECT * 
        FROM rh_colaboradores C
        INNER JOIN rh_pessoas P ON C.idPessoa = P.idPessoa";
$res = $conn->prepare($sql);
$res->execute();
$i = 1;
echo "<div class='container mt-4'>
<table class='table table-dark table-striped table-hover table-bordered table-sm w-100 nowrap'>
<thead>
    <tr class='bg-secondary'>
        <th><sup>1</sup>ID Colab</th>
        <th><sup>2</sup>Matricula</th>
        <th><sup>3</sup>Nome</th>
        <th><sup>4</sup>Status</th>";
while ($row = $res->fetch(PDO::FETCH_ASSOC)) {
    extract($row);
    //
    $dsMotivo = "Início de Contrato";
    $sql = "INSERT INTO rh_historico_sal ( idColab, data, valor, motivo, idLogin ) 
            VALUES ( :idColab, :data, :salario, :motivo, :idLogin )";
    $stmt = $conn->prepare($sql);
    $stmt->bindValue(':idColab', $idColab, PDO::PARAM_INT);
    $stmt->bindValue(':data', $data_admissao, PDO::PARAM_STR);
    $stmt->bindValue(':salario', $salario_base, PDO::PARAM_STR);
    $stmt->bindValue(':motivo', $dsMotivo, PDO::PARAM_STR);
    $stmt->bindValue(':idLogin', $idLogin, PDO::PARAM_INT);
    $stmt->execute();
    //
    $sql = "INSERT INTO rh_historico_funcao ( idColab, data, idFuncao, motivo, idLogin ) 
            VALUES ( :idColab, :data, :idFuncao, :motivo, :idLogin )";
    $stmt = $conn->prepare($sql);
    $stmt->bindValue(':idColab', $idColab, PDO::PARAM_INT);
    $stmt->bindValue(':data', $data_admissao, PDO::PARAM_STR);
    $stmt->bindValue(':idFuncao', $idFuncao, PDO::PARAM_INT);
    $stmt->bindValue(':motivo', $dsMotivo, PDO::PARAM_STR);
    $stmt->bindValue(':idLogin', $idLogin, PDO::PARAM_INT);
    $stmt->execute();
    //
    $sql = "INSERT INTO rh_historico_cargo ( idColab, data, idCargo, motivo, idLogin ) 
            VALUES ( :idColab, :data, :idCargo, :motivo, :idLogin )";
    $stmt = $conn->prepare($sql);
    $stmt->bindValue(':idColab', $idColab, PDO::PARAM_INT);
    $stmt->bindValue(':data', $data_admissao, PDO::PARAM_STR);
    $stmt->bindValue(':idCargo', $idCargo, PDO::PARAM_INT);
    $stmt->bindValue(':motivo', $dsMotivo, PDO::PARAM_STR);
    $stmt->bindValue(':idLogin', $idLogin, PDO::PARAM_INT);
    $stmt->execute();
    //
    $sql = "INSERT INTO rh_historico_orgao ( idColab, data, idOrgao, motivo, idLogin ) 
            VALUES ( :idColab, :data, :idOrgao, :motivo, :idLogin )";
    $stmt = $conn->prepare($sql);
    $stmt->bindValue(':idColab', $idColab, PDO::PARAM_INT);
    $stmt->bindValue(':data', $data_admissao, PDO::PARAM_STR);
    $stmt->bindValue(':idOrgao', $idOrgao, PDO::PARAM_INT);
    $stmt->bindValue(':motivo', $dsMotivo, PDO::PARAM_STR);
    $stmt->bindValue(':idLogin', $idLogin, PDO::PARAM_INT);
    $stmt->execute();    
    //
    $status = "Inserido";
    echo "<tr>";
    echo "<td>$idColab</td><td>$matricula</td><td>$nome</td><td>$status</td>";
    $i++;
    echo "</tr>";
}
echo "</table>";
echo "</div>";
?>