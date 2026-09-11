<?PHP
//
//- importa.php | 2025-09-22 10:20:45
//

include_once "includes/conexao_gerar.php";

$sql = "SELECT * FROM importacao";
$stmt = $conn->prepare($sql);
$stmt->execute();
$i = 1;
while ($linha = $stmt->fetch(PDO::FETCH_ASSOC)) {
    extract($linha);
    echo "<p>$i |$nome | $cpf | $data_nascimento | $sexo | $estado_civil | $email | $descricao_cargo | $raca_cor | $admissao</p>";
    $i++;
    //
    //- VERIFICA SE JÁ EXISTE
    $sql2 = "SELECT idPessoa FROM rh_pessoas WHERE cpf = :cpf";
    $stmt2 = $conn->prepare($sql2);
    $stmt2->bindValue(':cpf', $cpf, PDO::PARAM_STR);
    $stmt2->execute();
    $idPessoa = $stmt2->fetchColumn();
    if ($idPessoa == false) {
        //
        //- INSERT PESSOA
        $idSexo = null;
        if ($sexo == "Masculino") $idSexo = 'M';
        if ($sexo == "Feminino") $idSexo = 'F';
        $nacionalidade = "Brasileira";
        $sql3 = "INSERT INTO rh_pessoas ( nome, nomeSocial, cpf, dtNascimento, sexo, idEstadoCivil, 
                    nacionalidade, email_corporativo, idEtnia ) 
                    VALUES( :nome, :nome_social, :cpf, :data_nascimento, :sexo, :idEstadoCivil, 
                    :nacionalidade, :email_corporativo, :idEtnia )";
        $stmt3 = $conn->prepare($sql3);
        $stmt3->bindValue(':nome', $nome, PDO::PARAM_STR);
        $stmt3->bindValue(':nome_social', $nome_social, PDO::PARAM_STR);
        $stmt3->bindValue(':cpf', $cpf, PDO::PARAM_STR);
        $stmt3->bindValue(':data_nascimento', $data_nascimento, PDO::PARAM_STR);
        $stmt3->bindValue(':sexo', $idSexo, PDO::PARAM_STR);
        $stmt3->bindValue(':idEstadoCivil', $idEstadoCivil, PDO::PARAM_INT);
        $stmt3->bindValue(':nacionalidade', $nacionalidade, PDO::PARAM_STR);
        $stmt3->bindValue(':email_corporativo', $email, PDO::PARAM_STR);
        $stmt3->bindValue(':idEtnia', $idEtnia, PDO::PARAM_STR);
        $stmt3->execute();
        $idPessoa = $conn->lastInsertId();
        //
    } else {
        echo "<p>ATUALIZANDO:: $idPessoa</p>";

        // Atualiza os dados da pessoa existente
        $idSexo = null;
        if ($sexo == "Masculino") $idSexo = 'M';
        if ($sexo == "Feminino") $idSexo = 'F';
        $nacionalidade = "Brasileira";

        $sqlUpdate = "UPDATE rh_pessoas SET 
        nome = :nome,
        nomeSocial = :nome_social,
        dtNascimento = :data_nascimento,
        sexo = :sexo,
        idEstadoCivil = :idEstadoCivil,
        nacionalidade = :nacionalidade,
        email_corporativo = :email_corporativo,
        idEtnia = :idEtnia
        WHERE cpf = :cpf";

        $stmtUpdate = $conn->prepare($sqlUpdate);
        $stmtUpdate->bindValue(':nome', $nome, PDO::PARAM_STR);
        $stmtUpdate->bindValue(':nome_social', $nome_social, PDO::PARAM_STR);
        $stmtUpdate->bindValue(':data_nascimento', $data_nascimento, PDO::PARAM_STR);
        $stmtUpdate->bindValue(':sexo', $idSexo, PDO::PARAM_STR);
        $stmtUpdate->bindValue(':idEstadoCivil', $idEstadoCivil, PDO::PARAM_INT);
        $stmtUpdate->bindValue(':nacionalidade', $nacionalidade, PDO::PARAM_STR);
        $stmtUpdate->bindValue(':email_corporativo', $email, PDO::PARAM_STR);
        $stmtUpdate->bindValue(':idEtnia', $idEtnia, PDO::PARAM_STR);
        $stmtUpdate->bindValue(':cpf', $cpf, PDO::PARAM_STR);
        $stmtUpdate->execute();
    }
    //
}
