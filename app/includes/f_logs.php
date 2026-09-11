<?php

function f_log( $oper, $historico, $tabela, $idModulo=0, $idOperacao=0, $programa="" )
{
	global $conn;
	$agora = date('Y-m-d H:i:s');
	//
	if( isset($_SESSION['idLogin']  ) ) $idLogin   = $_SESSION['idLogin'  ]; else $idLogin=0;
	if( isset($_SESSION['idEmpresa']) ) $idEmpresa = $_SESSION['idEmpresa']; else $idEmpresa=1;
	//
	$sql = "INSERT INTO rh_logs (idLogin, idEmpresa, dtOper, oper, historico, tabela, idModulo, idOperacao, programa) 
			VALUES ($idLogin, $idEmpresa, '$agora', '$oper', '$historico', '$tabela', $idModulo, $idOperacao, '$programa')";
	try {
		// Inicia a transação
		$stmt = $conn->prepare($sql);
		$stmt->execute();
	} catch (PDOException $e) {
		//
		$texto = "Erro de f_log | Erro na transação: " . $e->getMessage() . "\n" . $sql;
        //
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
		//
	}
	return true;
}

?>