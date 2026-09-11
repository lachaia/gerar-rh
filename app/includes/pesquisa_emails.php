<?php 
//
//- pesquisa_emails.php | busca lista de emails
//- (C)haia, 12/03/2024. | (U) 2025-06-02
//

include_once "./conexao_gerar.php";

$texto = filter_input(INPUT_GET, "texto", FILTER_DEFAULT);

if( ! empty($texto) ){

    $texto = "%" . $texto . "%";
    
        $sql = "SELECT * FROM VW_emails
                WHERE nome like :texto or email like :texto
                LIMIT 10";

    $result = $conn->prepare($sql);
    $result->bindParam(':texto', $texto);
    $result->execute();
    //
    if( $result && ($result->rowCount() != 0)){
        while( $linha = $result->fetch(PDO::FETCH_ASSOC)){
            $dados[] = [
                'nome'  => $linha['nome' ],
                'tipo'  => $linha['tipo' ],
                'email' => $linha['email']
            ];
        }
        $retorna = ['status' => true, 'dados'=> $dados ];
        //
    } else{
        $retorna = ['status' => false, 'msg'=> 'Erro: nada encontrado!'];
    }

} else{
    $retorna = ['status' => false, 'msg'=> 'Erro: nada encontrado!'];
}

echo json_encode( $retorna );
