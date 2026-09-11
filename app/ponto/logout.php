<?php
//
// - logout.php
//

session_start();

$agora = date('Y-m-d H:i:s');

if( isset( $_SESSION['idLogin'] ) ){
    // Atualiza a data de logout
    $idLogin = $_SESSION["idLogin"];
    include_once "../includes/conexao_gerar.php";
    $stmt = $conn->prepare( "UPDATE rh_logins SET dtLogout = '$agora' WHERE idLogin = $idLogin" );
    $stmt->execute(); 
    $conn = null;     
}

$_SESSION = array(); // Limpa todas as variáveis de sessão
session_destroy(); // Destruir a sessão

header('Location: login.php');
exit();

?>