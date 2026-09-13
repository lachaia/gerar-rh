<?php
//
//- testar_usuario.php | Verifica status do Usuário
// by (C)haia, 13/08/2026
//

session_start();

$login = trim($_POST['usuario'] ?? '');

if ($login === '') {
    echo json_encode(["status" => false, "msg" => "Usuário Nao Encontrado"]);
    exit();
}

include_once "../../app/includes/conexao_gerar.php";

// Só retorna nome/e-mail/organograma de quem já tem um login válido para
// este portal — não devolve mais dado do que o necessário para o "existe
// ou não existe" que a tela já mostra visualmente (ver recrutamento/js/login.js).
// A enumeração de usuário em si (responder existe/não existe) é uma
// limitação aceita e documentada em ANALISE_SEGURANCA.md — é o próprio
// campo de login dando feedback em tempo real, corrigir isso de verdade
// mudaria a experiência da tela (fora do escopo desta correção pontual).
$sql = "SELECT C.idOrgao, O.nivel
            FROM rh_usuarios U
            INNER JOIN rh_pessoas P on P.idPessoa = U.idPessoa
            INNER JOIN rh_colaboradores C on C.idPessoa = P.idPessoa
            INNER JOIN rh_organograma O on O.idOrgao = C.idOrgao
            WHERE O.nivel < 7 AND (U.login = :login OR P.email_corporativo = :login2) AND U.ativo = 1
            LIMIT 1";
$stmt = $conn->prepare($sql);
$stmt->bindParam(':login', $login);
$stmt->bindParam(':login2', $login);
$stmt->execute();

if(  $stmt->rowCount() > 0 ){
    echo json_encode(["status" => true, "msg" => "Usuário Encontrado"]);
}else{
    echo json_encode(["status" => false, "msg" => "Usuário Nao Encontrado"]);
}
exit();