<?php

header('Content-Type: application/json');

require_once 'conn_db.php';

if ($_SERVER['REQUEST_METHOD'] =='GET'){

     // Verificar se os campos estão preenchidos
    if (empty($_GET['nome_user'])) {
        echo json_encode(array("status" => "error", "message" => "Usuário não podem estar vazios"));
        exit();
    }

    if (empty($_GET['id_card'])) {
        echo json_encode(array("status" => "error", "message" => "carta não podem estar vazios"));
        exit();
    }

    if (empty($_GET['tipo'])) {
        echo json_encode(array("status" => "error", "message" => "tipo não podem estar vazios"));
        exit();
    }

    $nome_user = $_GET['nome_user'];
    $id_card = $_GET['id_card']; 
    $tipo = $_GET['tipo'];
    $saldo_rodada = 0;
    $saldo_moedas = 0;

    //------------------------------------------\\
    // Verifica o saldo do usuário
    $stmt = $conn->prepare("SELECT rodadas, moedas FROM tb_usuario WHERE nome_usuario = ?");
    $stmt->bind_param("s", $nome_user);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $saldo_rodada = $row['rodadas'];
        $saldo_moedas = $row['moedas'];
    }
    //------------------------------------------\\
    //seleciona itens
    $stmt = $conn->prepare("SELECT preco, tipo from tb_itens_loja where id = ? AND tipo = ?");
    $stmt->bind_param("is", $id_card, $tipo);
    $stmt->execute();
    $result_item = $stmt->get_result();

    if ($result_item->num_rows > 0) {
        $row = $result->fetch_assoc();
        $preco_iten = $row['preco'];
        $tipo_iten = $row['tipo'];
    }
    //------------------------------------------\\

    //-------------Debito---------\\
    $stmt = $conn->prepare("UPDATE tb_usuario SET moedas = ? WHERE nome_usuario = ?");
    $saldo_moedas = $saldo_moedas - $preco_iten;
    $stmt->bind_param("is", $saldo_moedas, $nome_user);
    $stmt->execute();

    if($tipo== "Carta"){
        $stmt = $conn->prepare("INSERT INTO tb_usuarios_itens (user_id, item_id, tipo_item)
                            VALUES ((SELECT id_user FROM tb_usuario WHERE nome_usuario = ?), ?, ?)");
    
        // Vincular os parâmetros e executar a consulta
        $stmt->bind_param("sis", $nome_user, $id_card, $tipo);
        $stmt->execute();


        echo json_encode(array(
            "status" => "success, carta adicionada"));

    }elseif($tipo== "Rodada"){

        //Define quantidade rodadas
        if($preco_iten ==100){
            $add_rodadas= 5;
        }elseif($preco_iten == 1000){
            $add_rodadas= 100;
        }

        // Atualiza o saldo do usuário no banco de dados         
                $stmt = $conn->prepare("UPDATE tb_usuario SET rodadas = ? WHERE nome_usuario = ?");
                $saldo_rodada = $saldo_rodada + $add_rodadas;
                $stmt->bind_param("is", $saldo_rodada, $usuario);
                $stmt->execute();


        echo json_encode(array(
        "status" => "success, mais "+ $add_rodadas+ " rodasdas adicionadas"));
    }else{
        echo json_encode(array(
        "status" => "fail, tipo não encontrado"));
    }

    

   

    // Fechar a declaração
    $stmt->close();
} else {
    // Parâmetro 'nome_user' não fornecido
    echo json_encode(array("mensagem" => "Falha Na requisisao"), JSON_PRETTY_PRINT);
}


// Fechar a conexão com o banco de dados
$conn->close();

?>