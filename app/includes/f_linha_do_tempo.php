<?php

function f_ldt( $tipo, $idPessoa, $descricao, $idOrigem = 0, $quando="", $origem="" )
{
	// include "includes/conexao_gerar.php";
	global $conn;

	try {
		if( isset($_SESSION['idLogin']  ) ) $idLogin   = $_SESSION['idLogin'  ]; else $idLogin=0;
		if( isset($_SESSION['idUsuario']) ) $idUsuario = $_SESSION['idUsuario']; else $idUsuario=0;
		if( isset($_SESSION['idEmpresa']) ) $idEmpresa = $_SESSION['idEmpresa']; else $idEmpresa=1; // padrão
		//
        if(empty($quando)) $agora = date("Y-m-d H:i:s"); else $agora=$quando;
        //
		$sql = "INSERT INTO rh_pessoas_ldt ( idAcaoTipo, idPessoa, idEmpresa, data, descricao, idLogin, idOrigem, idUsuario, origem ) 
				VALUES ( :idAcaoTipo, :idPessoa, :idEmpresa, :data, :descricao, :idLogin, :idOrigem, :idUsuario, :origem )";
		//
		// Prepara a consulta
		$stmt = $conn->prepare($sql);
        //
        $stmt->bindParam(':idAcaoTipo', $tipo, PDO::PARAM_INT);
        $stmt->bindParam(':idEmpresa',  $idEmpresa, PDO::PARAM_INT);
        $stmt->bindParam(':idPessoa',   $idPessoa, PDO::PARAM_INT);
        $stmt->bindParam(':idUsuario',  $idUsuario, PDO::PARAM_INT);
        $stmt->bindParam(':data',       $agora, PDO::PARAM_STR);
        $stmt->bindParam(':descricao',  $descricao, PDO::PARAM_STR);
        $stmt->bindParam(':idLogin',    $idLogin, PDO::PARAM_STR);
        $stmt->bindParam(':idOrigem',   $idOrigem, PDO::PARAM_INT);
        $stmt->bindParam(':origem',   $origem, PDO::PARAM_STR);
        //
		$stmt->execute();
	
	} catch (PDOException $e) {
		// Em caso de exceção, faz o rollback e exibe o erro
		$texto = "f_ldt | Erro na transação: " . $e->getMessage() . "\n" . $sql;
        f_erro( $texto );
        $vetor = '{"status":"0", "mensagem":"'.$texto.'"}'; 
        die( $vetor );
	}

}

function f_erro( $texto ){
    $agora = date("Y-m-d H:i:s");
    $nomeArquivo = 'erros.log'; // Nome do arquivo TXT
    if (!file_exists($nomeArquivo)) {
        $arquivo = fopen($nomeArquivo, 'w');
    } else {
        $arquivo = fopen($nomeArquivo, 'a');
    }
    fwrite($arquivo, "\n Em $agora informo: \n");   // Escreve o texto no final do arquivo
    fwrite($arquivo, "$texto\n");   // Escreve o texto no final do arquivo
    fclose($arquivo);  // Fecha o arquivo
}

?>