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
        $itens[] = array(
            "id" => $item['id'],
            "nome" => $item['nome'],
            "preco" => $item['preco'],
            //"tipo" => $item['tipo'],
            "imagem" => $item['imagem'],
        );
    }


    echo json_encode(array(
        "status" => "success",
        "itens" => $itens
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

?>