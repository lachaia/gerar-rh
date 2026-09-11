<?PHP 
//
//- g_config_aj1.php | Salva campo assinatura de e-mail 
//- (C)haia, 22/11/2024 | (U)pdate: 08/02/2025 21:55

session_start();
$idUsuario = $_SESSION['idUsuario'];

include "conexao_gerar.php";

$txt_email = addslashes($_POST['texto']);

$sql = "UPDATE rh_usuarios SET assinatura = '$txt_email' WHERE idUsuario = $idUsuario";
$stmt = $conn->prepare($sql);
$stmt->execute();

$conn = null;

if( $stmt ){
    die( '<div class="alert alert-success">
  <strong>Sucesso!</strong> Alteração Salva.
</div>' );
}else{
    die( '<div class="alert alert-warning">
  <strong>ERRO!</strong> Não foi possível salvar.
</div>' );
}