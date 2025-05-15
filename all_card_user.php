<?php

header('Content-Type: application/json'); // Define o tipo de conteúdo como JSON

require_once 'conn_db.php'; // Inclui o arquivo de configuração do banco de dados

if (isset($_GET['nome_user'])) { // Verifica se o parâmetro 'nome_user' foi fornecido
    $nome_user = $_GET['nome_user'];
    
    //Preparar a declaração SQL para evitar injeção de SQL
    $stmt = $conn->prepare("SELECT card.id_carta, card.nome, card.raridade, card.tipo, card.vida, card.mana, card.energia, card.imagem, card.descricao, 
	col.nome_colecao, col.tipo_colecao, 
    IF(itens_user.tipo_item='carta' AND itens_user.item_id = card.id_carta AND itens_user.user_id = (Select id_user from tb_usuario where nome_usuario= ?), TRUE, FALSE) AS 'hasCard' from tb_carta as card 
        left join tb_usuarios_itens as itens_user ON card.id_carta = itens_user.item_id 	
        inner join tb_colecao as col ON card.id_colecao = col.id_colecao"  
    );

    $stmt->bind_param("s", $nome_user); // "s" indica que o parâmetro é uma string

    // Executar a consulta
    $stmt->execute();

    // Obter o resultado
    $result = $stmt->get_result();
    
    $cards = [];
    while ($card = $result->fetch_assoc()) {
        
        $cards[] = array(
            "id" => $card['id_carta'],
            "nome" => $card['nome'],
            "descricao" => utf8_encode($card['descricao']),
            "raridade" => utf8_encode($card['raridade']),
            "tipo" => utf8_encode($card['tipo']),
            "vida" => $card['vida'],
            "mana" => $card['mana'],
            "energia" => $card['energia'],
            "imagem" => $card['imagem'],
            "colecao" => $card['nome_colecao'],
            "tipo_colecao" => utf8_encode($card['tipo_colecao']),
            "tem_carta" => $card['hasCard'],
        );
    }

    echo json_encode(array(
        "status" => "success",
        "itens" => $cards
    ),JSON_PRETTY_PRINT);

    if ($result->num_rows <= 0) {
        // Usuário encontrado
        echo json_encode(array("mensagem" => "Usuário não encontrado"), JSON_PRETTY_PRINT);
    }

    // Fechar a declaração
    $stmt->close();
} else {
    // Parâmetro 'nome_user' não fornecido
    echo json_encode(array("mensagem" => "Parâmetro 'nome_user' é obrigatório"), JSON_PRETTY_PRINT);
}


// Fechar a conexão com o banco de dados
$conn->close();

?>