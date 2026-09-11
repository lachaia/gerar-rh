<?PHP 

$texto = "571.606.009-91";

$limpo = preg_replace('/[^0-9]/', '', $texto);

echo $limpo;

echo preg_match_all('/\ba\w*/i', 'aprender regex ajuda bastante', $result);