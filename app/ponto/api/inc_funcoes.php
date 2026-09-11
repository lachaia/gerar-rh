<?php 

    function gera_ticket( $conn ){
        $data = date('Ymd');
        $stmt = $conn->query("SELECT COUNT(*)+1 AS seq FROM rh_ponto_registros WHERE DATE(criado_em) = CURDATE()");
        $seq = $stmt->fetchColumn();
        $ticket = sprintf("TCK-%s-%05d", $data, $seq);
        return $ticket;
    }

    function f_inclui_ponto($conn, $solicitacao_id, $data_hora, $idColab){
        $ticket = gera_ticket( $conn );
        $aql = "INSERT INTO rh_ponto_registros 
            ( colaborador_id, data_hora, solicitacao_id, origem, ticket ) VALUES 
            ( :colaborador_id, :data_hora, :solicitacao_id, :origem, :ticket )";
        $stmt = $conn->prepare($aql);
        $res = $stmt->execute([
            ':colaborador_id' => $idColab,
            ':data_hora' => $data_hora,
            ':solicitacao_id' => $solicitacao_id,
            ':origem' => 'manual',
            ':ticket' => $ticket
        ]);
        return $res;
    }

    function f_altera_ponto($conn, $batida_id, $data_hora, $idColab){
        //
        $sql = "UPDATE rh_ponto_registros 
                    SET data_hora = :data_hora 
                    WHERE id = :batida_id";
        $stmt = $conn->prepare($sql);
        $res = $stmt->execute([
            ':data_hora' => $data_hora,
            ':batida_id' => $batida_id
        ]);
        return $res;
    }

    function f_exclui_ponto($conn, $batida_id, $data_hora, $idColab){
        $sql = "DELETE FROM rh_ponto_registros 
                    WHERE id = :batida_id";
        $stmt = $conn->prepare($sql);
        $res = $stmt->execute([
            ':batida_id' => $batida_id
        ]);
        return $res;
    }

    function f_abonar_ponto($conn, $solicitacao_id, $data_hora, $idColab, $vetor_data, $horario_ini, $horario_fim){
        //
        $dataRef = substr($data_hora, 0, 10); //date('Y-m-d', $data_hora);
        $tipo = $vetor_data['tipo'];
        //
        if( $tipo == 'FACULTATIVO' ) return false;
        if( $tipo == 'FERIADO' ) return false;
        if( $tipo == 'DSR' ) return false;
        //
        if( $tipo == 'REDUZIDO'){
            $horario_ini = $vetor_data['horario_ini'];
            $horario_fim = $vetor_data['horario_fim'];
            $horarios = array($horario_ini, $horario_fim);
        } else{
            $horarios = array($horario_ini, "12:00:00", "13:00:00", $horario_fim);
        }
        //
        $aql = "INSERT INTO rh_ponto_registros 
            ( colaborador_id, data_hora, solicitacao_id, origem, ticket ) VALUES 
            ( :colaborador_id, :data_hora, :solicitacao_id, :origem, :ticket )";
        $stmt = $conn->prepare($aql);
        //        
        foreach( $horarios as $horario ){
            $ticket = gera_ticket( $conn );
            $data_hora = $dataRef . ' ' . $horario;
            $stmt->execute([
                ':colaborador_id' => $idColab,
                ':data_hora' => $data_hora,
                ':solicitacao_id' => $solicitacao_id,
                ':origem' => 'ajuste',
                ':ticket' => $ticket
            ]);
        }
        return true;
    }
