<?php

header('Content-Type: application/json'); // Define o tipo de conteúdo como JSON

require_once 'conn_db.php'; // Inclui o arquivo de configuração do banco de dados


if (isset($_GET['nome_user']) && isset($_GET['deck']) && isset($_GET['deck_bot'])) { // Verifica se o parâmetro 'nome_user' foi fornecido
    $nome_user = $_GET['nome_user'];
    $deck = $_GET['deck'];
    $deck_bot = $_GET['deck_bot'];

    // Preparar a declaração SQL para evitar injeção de SQL
    $stmt = $conn->prepare("INSERT INTO tb_batalha (id_user, deck, deck_bot, jogada)
                            VALUES ((Select id_user from tb_usuario where nome_usuario= ?), ?,?, 'Jogada Inicial')");
    $stmt->bind_param("sss", $nome_user, $deck, $deck_bot); // "s" indica que o parâmetro é uma string

   
    // Executar a consulta
    $stmt->execute();

      // // Retorna o resultado e o novo saldo
         echo json_encode(array(
            "status" => "success",
            "resultado" => 'Jogada Inicial',
            "jogador_id"=>  $nome_user,
            "hp_jogador"=>  100,
            "hp_bot"=>  100,
            "deck_jogador" =>  $deck,
            "deck_bot" =>  $deck_bot,
        ));


    // Fechar a declaração
    $stmt->close();
} else {
    // Parâmetro 'nome_user' não fornecido
    echo json_encode(array("mensagem" => "Parâmetro 'nome_user' é obrigatório"), JSON_PRETTY_PRINT);
}

// Fechar a conexão com o banco de dados
$conn->close();

?>