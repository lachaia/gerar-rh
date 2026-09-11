<?php
//
//- logout.php | Portal do Candidato | Encerra a sessão do candidato
//- (C)haia, 2026-08-27
//

session_start();
$_SESSION = [];
session_destroy();

header('Location: index.php');
exit;
