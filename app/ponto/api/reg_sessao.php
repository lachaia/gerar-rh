<?php 
//
//- reg_sessao.php | Registra Vars em Sessão
//- (C)haia, 17/11/2025
//

session_start();

$parametros = filter_input_array(INPUT_POST, FILTER_DEFAULT);
extract( $parametros );

if( isset($lat ))       $_SESSION['LAT'] = $lat;
if( isset($lon ))       $_SESSION['LON'] = $lon;
if( isset($hora ))      $_SESSION['HORA'] = $hora;
if( isset($endereco))   $_SESSION['DSENDERECO'] = $endereco;
if( isset($idEndereco)) $_SESSION['IDENDERECO'] = $idEndereco;