<?php
#
# FUNÇÕES AUXILIARES
#

    function f_os() // retorna o Tipo de Sistema Operacional em uso pelo Cliente: Windows, iOS, OSX, Linux, etc...
    {
        $texto = $_SERVER['HTTP_USER_AGENT'];
        if (strpos($texto, 'Windows'))  return "Windows";
        if (strpos($texto, 'OS X'))     return "OS X";
        if (strpos($texto, 'Android'))  return "Androide";
        if (strpos($texto, 'Linux') and ! strpos($texto, 'Android') ) return "Linux";
        return 'indefinido';
    }

    function f_tipoMaquina() 
    {
        $mobile = false;
        $modelo = "";
        $texto  = $_SERVER['HTTP_USER_AGENT'];
        if (strpos($texto, 'Mobile')) $mobile = true;
        $user_agents = array("iPhone","iPad","Android","webOS","BlackBerry","iPod","Symbian","IsGeneric");
        foreach($user_agents as $user_agent)
        {
            if (strpos($texto, $user_agent) !== FALSE)
            {
                $mobile = TRUE;
                $modelo = $user_agent;
                break;
            }
        }
        if ($mobile) return( "MOBILE | " . strtolower("$modelo") );
        else return "PC";
    }

    function f_navegador() // retorna o Tipo de Navegador em Uso pelo Cliente: IE, Chrome, Opera, etc...
    {
        $texto = $_SERVER['HTTP_USER_AGENT'];
        if (strpos($texto, 'Trident'))   return "Internet Explorer";
        if (strpos($texto, 'Firefox'))   return "Firefox";
        if (strpos($texto, 'Flock'))     return "Flock";
        if (strpos($texto, 'Opera'))     return "Opera";
        if (strpos($texto, 'Edge'))      return "Edge";
        if (strpos($texto, 'Crosswalk')) return "Crosswalk"; // navegador do androide
        if (strpos($texto, 'Safari') and strpos($texto, 'Chrome')) return "Chrome";
        if (strpos($texto, 'Safari'))    return "Safari";
        return 'indefinido';
    }

    function getUserIpAddr()
    {
        if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
            $ip = $_SERVER['HTTP_CLIENT_IP'];
        } elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
            $ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
        } else {
            $ip = $_SERVER['REMOTE_ADDR'];
        }
        return $ip;
    }


?>