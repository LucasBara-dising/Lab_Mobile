<?php

header('Content-Type: application/json'); // Define o tipo de conteúdo como JSON

require_once 'conn_db.php'; // Inclui o arquivo de configuração do banco de dados


if (isset($_GET['nome_user'])) { // Verifica se o parâmetro 'nome_user' foi fornecido
    $nome_user = $_GET['nome_user'];

    // Preparar a declaração SQL para evitar injeção de SQL
    $stmt = $conn->prepare("SELECT nome_usuario, rodadas, moedas, avatar_id FROM tb_usuario WHERE nome_usuario = ?");
    $stmt->bind_param("s", $nome_user); // "s" indica que o parâmetro é uma string

   
    // Executar a consulta
    $stmt->execute();

    // Obter o resultado
    $result = $stmt->get_result();

    $usuario = $result->fetch_assoc();
    echo json_encode($usuario, JSON_PRETTY_PRINT); // Retorna os dados como JSON formatado

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