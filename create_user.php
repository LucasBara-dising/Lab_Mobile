<?php
header('Content-Type: application/json'); // Define o tipo de conteúdo como JSON

require_once 'conn_db.php'; // Inclui o arquivo de configuração do banco de dados


if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Obtém os dados do formulário (JSON)
    $nome_user = $_POST['nome_user'];
    $email= $_POST['email'];
    $senha_fornecida = $_POST["senha"];


    // Verifica se os campos obrigatórios foram fornecidos
    if ((isset($nome_user) && isset($email) && isset($senha_fornecida))) {

        // Validação básica dos dados (adicione validações mais robustas conforme necessário)
        if (strlen($nome_user) < 3) {
            echo json_encode(array("mensagem" => "Nome de usuário deve ter pelo menos 3 caracteres"), JSON_PRETTY_PRINT);
            exit();
        }
        if (strlen($senha_fornecida) < 5) {
            echo json_encode(array("mensagem" => "Senha deve ter pelo menos 5 caracteres"), JSON_PRETTY_PRINT);
            exit();
        }

        if (str_contains($email, "@")) {
            echo json_encode(array("mensagem" => "o Email esta invalido"), JSON_PRETTY_PRINT);
            exit();
        }

        // Verifica se o nome de usuário já existe
        $stmt = $conn->prepare("SELECT nome_usuario FROM tb_usuario WHERE nome_usuario = ?");
        $stmt->bind_param("s", $nome_user);
        $stmt->execute();
        $stmt->store_result();

        if ($stmt->num_rows > 0) {
            echo json_encode(array("mensagem" => "Nome de usuário já está em uso"), JSON_PRETTY_PRINT);
            $stmt->close();
            exit();
        }
        $stmt->close(); // Fecha a declaração anterior

        // Hasheia a senha com bcrypt
        $senha_hasheada = password_hash($senha_plana, PASSWORD_BCRYPT);

        // Prepara a consulta SQL para inserir o novo usuário
        $stmt = $conn->prepare("INSERT INTO tb_usuario (nome_usuario, senha, email) VALUES (?, ?, ?)");
        $stmt->bind_param("sss", $nome_user, $senha_hasheada, $email); // "ss" indica que os dois parâmetros são strings

        // Executa a consulta
        if ($stmt->execute()) {
            // Cadastro bem-sucedido
            echo json_encode(array("mensagem" => "Usuário cadastrado com sucesso"), JSON_PRETTY_PRINT);
        } else {
            // Erro ao cadastrar o usuário
            echo json_encode(array("mensagem" => "Erro ao cadastrar o usuário"), JSON_PRETTY_PRINT);
        }

        // Fecha a declaração
        $stmt->close();
    } else {
        // Campos obrigatórios não fornecidos
        echo json_encode(array("mensagem" => "Os campos 'nome_user' e 'senha' são obrigatórios"), JSON_PRETTY_PRINT);
    }
} else {
    // Método de requisição inválido
    echo json_encode(array("mensagem" => "Método de requisição inválido. Use POST."), JSON_PRETTY_PRINT);
}

// Fecha a conexão com o banco de dados
$conn->close();

?>