<?PHP
//
// login_auth.php - Atentica o usuário e abre sessão
// (C)haia, 20/08/2025
//

session_start();

$_SESSION['SISTEMA'] = "TERMOS";
$idEmpresa = 1; // Empresa Gerar

$parametros = filter_input_array(INPUT_POST, FILTER_DEFAULT);
if( isset( $parametros )) extract( $parametros );

$agora  = date('Y-m-d H:i:s');

include "../includes/conexao_gerar.php";
include "../includes/f_login.php";

    $ip       = getUserIpAddr();
    $sisoper  = f_os();
    $hardware = f_tipoMaquina();
    $browser  = f_navegador();

    $login_cpf = preg_replace('/\D/', '', $login);
   
# TESTA LOGIN/CPF/E-MAIL e Recupera o idUsuario
#
    $sql = "SELECT  U.idColab, U.idEmpresa, U.idUsuario, P.idPessoa, P.nome, U.login, U.senha, U.idUsuarioGrupo, 
                    U.foto, P.email, U.chaveApp, U.idSubSede, S.identificador as dsSubSede, U.dcCIPA, U.dcBrigada
                FROM rh_usuarios U
                INNER JOIN rh_pessoas P ON P.idPessoa = U.idPessoa
                LEFT OUTER JOIN rh_subsedes as S on S.subsede_id = U.idSubSede
            WHERE U.ativo =1 AND ((U.login = :login ) OR (P.cpf = :login_cpf) 
                OR ( P.email = :login ) OR (P.email_corporativo = :login))
            AND U.idEmpresa = :idEmpresa AND U.idUsuarioGrupo in (4,7,9)";
    $stmt = $conn->prepare( $sql );
    $stmt->bindParam( ':login' , $login, PDO::PARAM_STR );
    $stmt->bindParam( ':login_cpf' , $login_cpf, PDO::PARAM_STR );
    $stmt->bindParam( ':idEmpresa' , $idEmpresa, PDO::PARAM_INT );
    //$stmt->bindParam( ':idPerfil' , $idPerfil, PDO::PARAM_INT );
    $stmt->execute();  

    $rows = $stmt->fetchAll();
    if (count($rows) > 0) {
        //- verifica se a senha confere
        //
        if( ! password_verify( $senha, $rows[0]['senha'] ) ){
            $conn = null; 
            die('{"status":"0", "mensagem":"Usuário ou Senha errada!"}');            
        }
        $idUsuario = $rows[0]["idUsuario"];
        $_SESSION['idUsuario'    ] = $idUsuario;
        $_SESSION['idColab'      ] = $rows[0]["idColab"];
        $_SESSION['idPessoa'     ] = $rows[0]["idPessoa"];
        $_SESSION['nmLogin'      ] = $rows[0]["login"];
        $_SESSION['nmUsuario'    ] = $rows[0]["nome"];
        $_SESSION['idGrupo'      ] = 2; //- Normal --- //$rows[0]["idUsuarioGrupo"];
        $_SESSION['email'        ] = $rows[0]["email"];      //-- necessária para o SMTP
        $_SESSION['chaveApp'     ] = $rows[0]["chaveApp"];   //-- necessária para o SMTP
        $_SESSION['idSubSede'    ] = $rows[0]["idSubSede"];   
        $_SESSION['dsSubSede'    ] = $rows[0]["dsSubSede"];  
        $_SESSION['idEmpresa'    ] = $rows[0]["idEmpresa"];  
        $_SESSION['dcCIPA'       ] = $rows[0]["dcCIPA"   ];  
        $_SESSION['dcBrigada'    ] = $rows[0]["dcBrigada"];  
        //
        $idUsuarioGrupo = $rows[0]["idUsuarioGrupo"];
        //
        if( ! empty( $rows[0]["foto"] ) ){
            $_SESSION['perfil'] = $rows[0]["foto"];
        } else{
            $_SESSION['perfil'] = "perfil.png";
        }
        #
        # VERIFICA se esse usuário não tem Sessões abertas
        #
        $stmt = $conn->prepare( "SELECT idUsuario, idLogin FROM rh_logins WHERE idUsuario = :idUsuario AND dtLogout is null" );
        $stmt->bindParam( 'idUsuario', $idUsuario, PDO::PARAM_INT);
        $stmt->execute();        
        $rows = $stmt->fetchAll();

        if (count($rows) > 0) {
            // Tem uma ou mais sessões abertas
            // Procuro nos ti_logs a última interação do usuário
            //            
                try {
                    $sql = "SELECT max(dtOper) as dtOper 
                                FROM rh_logs 
                                INNER JOIN rh_logins ON rh_logins.idLogin = rh_logs.idLogin
                                WHERE rh_logins.idUsuario = :idUsuario";
                    $stmt = $conn->prepare( $sql );
                    $stmt->bindParam( ':idUsuario', $idUsuario);
                    $stmt->execute();    
                    $rows = $stmt->fetchAll();
                
                    // Resto do código aqui, se necessário
                
                } catch (PDOException $e) {
                    die( "Erro: " . $e->getMessage() );
                }

            
            if (count($rows) > 0) {
                $dtLogout = $rows[0]['dtOper'];
                $stmt = $conn->prepare( "UPDATE rh_logins SET dtLogout = :dtLogout where dtLogout is null and idUsuario = :idUsuario" );
                $stmt->bindParam( ':idUsuario', $idUsuario);
                $stmt->bindParam( ':dtLogout', $dtLogout);
                $stmt->execute();
            }
            else{
                $stmt = $conn->prepare( "UPDATE rh_logins SET dtLogout = dtLogin where dtLogout is null and idUsuario = :idUsuario" );
                $stmt->bindParam( ':idUsuario', $idUsuario);
                $stmt->execute();
            }
        }

        #
        # INSERE OS DADOS NO BANCO
        #
            $sql = "INSERT INTO rh_logins ( idUsuario, dtLogin, ip, sisoper, browser, hardware )
                        VALUES ( :idUsuario, :agora, :ip, :sisoper, :browser, :hardware )";
            $stmt = $conn->prepare( $sql );
            $stmt->bindParam( ':idUsuario', $idUsuario);
            $stmt->bindParam( ':agora', $agora);
            $stmt->bindParam( ':ip', $ip);
            $stmt->bindParam( ':sisoper', $sisoper);
            $stmt->bindParam( ':browser', $browser);
            $stmt->bindParam( ':hardware', $hardware);
            $stmt->execute();
            $_SESSION['idLogin'] = $conn->lastInsertId();
            //
            $retorno = [
                "status" => "1", 
                "mensagem" => "ok", 
                "idPerfil" => $idUsuarioGrupo 
            ];
    }else{
        $retorno = [
            "status" => "0", 
            "mensagem" => "Usuário ou Senha errada!" 
        ];
    }
    $conn = null;
    die( json_encode($retorno, JSON_PRETTY_PRINT) );
    