<?php
header('Content-Type: application/json'); // Define o tipo de conteúdo como JSON

require_once 'conn_db.php'; // Inclui o arquivo de configuração do banco de dados

// Verifica se os dados foram enviados via POST
if ($_SERVER["REQUEST_METHOD"] == "POST") {

    // Obtém os dados do formulário
    $nome_user = $_POST['nome_user'];
    $senha_fornecida = $_POST["senha"]; // Senha digitada pelo usuário

    // Verificar se os campos estão preenchidos
    if (empty($nome_user) || empty($senha_fornecida)) {
        echo json_encode(array("status" => "error", "message" => "Usuário  não podem estar vazios"));
        exit();
    }

    // Prepara a consulta SQL para buscar o usuário pelo nome de usuário
    $stmt = $conn->prepare("SELECT nome_usuario, senha FROM tb_usuario WHERE nome_usuario = ?");
    $stmt->bind_param("s", $nome_user);

    // Executa a consulta
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows == 1) {
        // Usuário encontrado
        $usuario = $result->fetch_assoc();

        $senha = $senha_fornecida;

        $hash = $usuario["senha"];// Hash da senha armazenado no banco de dados
        //erro no login para fazer hash da senha

        echo "$senha \n $hash";

        if (password_verify($senha, $hash)) {
            $teste = "Senha correta";
            echo $teste;
        } else {
            $teste = "Senha incorreta";
            echo $teste;
        }

        

        // Verifica se a senha fornecida corresponde ao hash armazenado usando bcrypt
        if (password_verify($senha_fornecida, $senha_hasheada)) {
            // Senha correta!
            echo "entrou";
            // Define variáveis de sessão
            $_SESSION["id_usuario"] = $usuario["id_usuario"];
            $_SESSION["nome_user"] = $usuario["nome_user"];

            // Redireciona para a página principal (ou para onde você quiser)
            echo json_encode(array("Login" => "Autorizado"), JSON_PRETTY_PRINT);
            exit(); // Encerra o script após o redirecionamento

        } else {
            // Senha incorreta
            echo json_encode(array("mensagem" => "Nome de usuário ou senha incorretos"), JSON_PRETTY_PRINT);
        }
    } else {
        // Usuário não encontrado
        echo json_encode(array("mensagem" => "Nome de usuário incorretos"), JSON_PRETTY_PRINT);
    }

    // Fecha a declaração
    $stmt->close();
}

// Fecha a conexão com o banco de dados
$conn->close();
?>
