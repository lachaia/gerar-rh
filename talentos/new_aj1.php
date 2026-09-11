<?php
//
//- new_aj1.php | Verifica se já há esse CPF na base de Currículo
//- (C)haia, 2026-04-07
//

session_start();

include "../app/includes/conexao_gerar.php";

$cpf = $_POST['cpf'];

/*- PLANO
 1. Pessoa = Não | ==> Senha Enviada Por e-mail 
 2. Pessoa = Sim | Colaborador = Não | Usuário = Não | ==> Atualiza "Pessoa" + "CV" e Senha é Enviada Por e-mail
 3. Pessoa = Sim | Colaborador = Não | Usuário = Sim | ==> Fazer Logon de Candidato
 4. Pessoa = Sim | Colaborador = Sim | ==> Usar Painel de Colaborador
*/
//- Traz também os demais campos do bloco 1 e de diversidade já salvos (genero,
//- linkedin), senão quem já preencheu e voltou depois (ex.: clicou Cancelar no
//- meio do assistente) só vê o nome pré-preenchido e tem que redigitar tudo de novo.
$sql  = "SELECT P.cpf, U.idColab, U.idUsuarioGrupo, P.idPessoa, C.idCV, P.nome,
                P.nomeSocial, P.telefone, P.email, P.sexo, P.nacionalidade, P.idGrauEscola,
                C.genero, C.linkedin
            FROM rh_pessoas P
            LEFT OUTER JOIN rh_usuarios U on U.idPessoa = P.idPessoa
            LEFT OUTER JOIN rh_cv C on C.idPessoa = P.idPessoa
            WHERE P.cpf like :cpf LIMIT 1";
$stmt = $conn->prepare($sql);
$stmt->bindParam( ':cpf' , $cpf, PDO::PARAM_STR );
$stmt->execute();  
$rows = $stmt->fetchAll();

if( count($rows) == 0 ) {
    //
    //- 1. NÃO EXISTE PESSOA
    //
    echo json_encode(["status" => false, "msg" => "Nao encontrado", "idColab" => 0, "idUsuarioGrupo" => 0]);
    //
} else {
    //
    //- 2. EXISTE PESSOA
    //
    if( empty( $rows[0]['idCV']) ){
        //
        //- 3. NÃO EXISTE CV
        //
        $sql = "INSERT INTO rh_cv ( idPessoa ) values ( :pessoa_id );";
        $stmt = $conn->prepare($sql);
        $stmt->bindParam( ':pessoa_id' ,  $rows[0]['idPessoa'], PDO::PARAM_INT );
        $stmt->execute();
        $rows[0]['idCV'] = $conn->lastInsertId();
    }

    //- A partir daqui o idPessoa vem da sessão (abaixo), não mais do valor que o
    //- cliente reenvia em cada passo do assistente - senão dá pra editar o
    //- currículo de outra pessoa só sabendo o CPF/idPessoa dela.
    $_SESSION['candidato_idPessoa'] = (int) $rows[0]['idPessoa'];

    $retorno = [
        "status" => true,
        "msg" => "Encontrado",
        "idPessoa" => $rows[0]['idPessoa'],
        "idColab" => $rows[0]['idColab'],
        "idCV" => $rows[0]['idCV'],
        "nome" => $rows[0]['nome'],
        "nomeSocial" => $rows[0]['nomeSocial'],
        "telefone" => $rows[0]['telefone'],
        "email" => $rows[0]['email'],
        "sexo" => $rows[0]['sexo'],
        "nacionalidade" => $rows[0]['nacionalidade'],
        "idGrauEscola" => $rows[0]['idGrauEscola'],
        "genero" => $rows[0]['genero'],
        "linkedin" => $rows[0]['linkedin'],
        "idUsuarioGrupo" => $rows[0]['idUsuarioGrupo']
    ];

    echo json_encode( $retorno );
}