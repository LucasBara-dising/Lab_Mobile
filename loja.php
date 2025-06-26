<?php

header('Content-Type: application/json;charset=utf-8'); // Define o tipo de conteúdo como JSON

require_once 'conn_db.php'; // Inclui o arquivo de configuração do banco de dados
ini_set('default_charset','UTF-8');

if ($_SERVER['REQUEST_METHOD'] =='GET'){
    
    $stmt = $conn->prepare("SELECT id, imagem, nome, preco, tipo FROM  tb_itens_loja;");
    // Executar a consulta
    $stmt->execute();

    // Obter o resultado
    $result = $stmt->get_result();
    $itens = [];    

    //Por algum motivo falha nas palvras com acesnto
    while ($item = $result->fetch_assoc()) {
        if(utf8_encode($item['tipo']) == "Rodada" ){
            $itens[] = array(
                "id" => $item['id'],
                "nome" => $item['nome'],
                "preco" => $item['preco'],
                "tipo" => utf8_encode($item['tipo']),
                "imagem" => $item['imagem'],
            );
        }
    }


    $stmt_card = $conn->prepare("SELECT card.id_carta, card.nome, card.raridade, card.tipo,  card.imagem FROM tb_carta AS card ");

    // Executar a consulta
    $stmt_card->execute();

    // Obter o resultado
    $result = $stmt_card->get_result();
    $cards = []; 
    
    

     //Por algum motivo falha nas palvras com acesnto
    while ($cards = $result->fetch_assoc()) {

        if($cards['id_carta'] <= 6 ){

            $cards_aberta[] = array(
                "id" => $cards['id_carta'],
                "nome" => utf8_encode($cards['nome']),
                "preco" => setValor(utf8_encode($cards['raridade'])),
                "tipo" => "Carta",
                "imagem" => $cards['imagem'],
                
            );

        }
    
    }

    $itens_loja = $itens + $cards_aberta;
    
     echo json_encode(array(
        "itens" => $itens_loja
    ), JSON_PRETTY_PRINT & JSON_UNESCAPED_UNICODE );


    if ($result->num_rows <= 0) {
        // Usuário encontrado
        echo json_encode(array("mensagem" => "Cartas não encontrado"), JSON_PRETTY_PRINT);
    }

    // Fechar a declaração
    $stmt->close();
} else {
    // Parâmetro 'nome_user' não fornecido
    echo json_encode(array("mensagem" => "Falha Na requisisao"), JSON_PRETTY_PRINT);
}


// Fechar a conexão com o banco de dados
$conn->close();

function setValor($raridade){
    $valor=0;
    if($raridade == "Comum" ){
        $valor = 150;
    }elseif($raridade == "Raro" ){
        $valor = 500;
    }elseif($raridade == "Épico" ){
        $valor = 1500;
    }elseif($raridade == "Lendario" ){
        $valor = 5000;
    }else{
        $valor = 2800;
    }

    return $valor;
}

?>