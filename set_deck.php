<?php

header('Content-Type: application/json'); // Define o tipo de conteúdo como JSON

require_once 'conn_db.php'; // Inclui o arquivo de configuração do banco de dados


if (isset($_GET['nome_user']) && isset($_GET['deck'])) { // Verifica se o parâmetro 'nome_user' foi fornecido
    $nome_user = $_GET['nome_user'];
    $deck = $_GET['deck'];

    // Preparar a declaração SQL para evitar injeção de SQL
    $stmt = $conn->prepare("UPDATE tb_usuario SET deck = ? WHERE nome_usuario = ?");
    $stmt->bind_param("ss", $deck , $nome_user); // "s" indica que o parâmetro é uma string

   
    // Executar a consulta
    $stmt->execute();

     echo json_encode(array("mensagem" => "Alteração feita com sucesso"), JSON_PRETTY_PRINT);

    // Fechar a declaração
    $stmt->close();
} else {
    // Parâmetro 'nome_user' não fornecido
    echo json_encode(array("mensagem" => "Parâmetro 'nome_user' é obrigatório"), JSON_PRETTY_PRINT);
}

// Fechar a conexão com o banco de dados
$conn->close();

?>