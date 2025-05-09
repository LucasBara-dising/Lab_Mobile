<?php

header('Content-Type: application/json;charset=utf-8'); // Define o tipo de conteúdo como JSON

require_once 'conn_db.php'; // Inclui o arquivo de configuração do banco de dados
ini_set('default_charset','UTF-8');

if ($_SERVER['REQUEST_METHOD'] =='GET'){
    
    $stmt = $conn->prepare("SELECT card.id_carta, card.nome, card.raridade, card.tipo, card.vida, card.mana, card.energia, card.imagem, card.descricao, col.nome_colecao, col.tipo_colecao FROM tb_carta AS card INNER JOIN	tb_colecao AS col ON card.id_colecao = col.id_colecao ");

    // Executar a consulta
    $stmt->execute();

    // Obter o resultado
    $result = $stmt->get_result();
    $cards = [];    

    //Por algum motivo falha nas palvras com acesnto
    while ($card = $result->fetch_assoc()) {

        $cards[] = array(
            "id" => $card['id_carta'],
            "nome" => $card['nome'],
            "raridade" => $card['raridade'],
            "tipo" => $card['tipo'],
            "vida" => $card['vida'],
            "mana" => $card['mana'],
            "energia" => $card['energia'],
            "imagem" => $card['imagem'],
            "descricao" => $card['descricao'],
            "colecao" => $card['nome_colecao'],
            "tipo_colecao" => $card['tipo_colecao'],
        );
    }


    echo json_encode(array(
        "status" => "success",
        "itens" => $cards
    ), JSON_PRETTY_PRINT);

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

?>