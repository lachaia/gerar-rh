<?PHP
//
//- rh_autocadastro_aj1.php | Popula Grid - Formulários enviados
// (C)haia, 23/10/2025;
//

session_start();

$idModulo = 21; //-Autocadastro

include_once "../includes/conexao_gerar.php";

$sql = "SELECT * FROM rh_autocadastro_ctr";
$stmt = $conn->prepare($sql);
$stmt->execute();
$recordsFiltered = $stmt->rowCount();

//- LER OS Registros e preencher o array

$dados = array();
while ($linha = $stmt->fetch(PDO::FETCH_ASSOC)) {
    extract( $linha );
    $dado = array();
    //
    if($status=='Enviado'){
        $acoes = "<a href='#!' class='btn btn-outline-danger btn-sm me-1' onClick='f_excluir($id)'><i class='fa-solid fa-trash-can'></i></a>";
        $status = "<span class='badge bg-warning text-dark status w-100'>$status</span>";
    }elseif($status=='RH'){
        $acoes = "<a href='#!' class='btn btn-outline-success btn-sm' onClick='f_importar($id)'><i class='fa-solid fa-check'></i></a>"; 
        $status = "<span class='badge bg-danger status w-100'>$status</span>";
    }elseif($status=='Transferido'){
        $acoes = "<a href='#!' class='btn btn-outline-secondary disabled btn-sm'><i class='fa-solid fa-check'></i></a>"; 
        $status = "<span class='badge bg-success status w-100'>$status</span>";        
    }
    //
    $dado[] = $data;                                      
    $dado[] = $nome;
    $dado[] = $status;
    $dado[] = $acoes;
    //
    $dados[] = $dado;
}

//- Criar um vetor para retornar ao Javascript
//

$output = array(
    "draw" => 1,
    "recordsTotal" => intval($recordsFiltered),
    "recordsFiltered" => intval($recordsFiltered),
    "data" => $dados
);
$conteudo = json_encode($output);
echo $conteudo;
exit;