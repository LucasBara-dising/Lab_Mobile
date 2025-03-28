<?php
header('Content-Type: application/json');

require_once 'conn_db.php';

echo json_encode(array("status" => "error", "message" => "5tes5te ou aposta não podem estar vazios"));
// Verifica se os dados foram enviados via POST
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    //$input = json_decode(file_get_contents('php://input'), true);

    $nome_user = $_POST['nome_user'];
    $energia = $_POST['energia'];

    // Verificar se os campos estão preenchidos
    if (empty($nome_user) || empty($energia)) {
        echo json_encode(array("status" => "error", "message" => "Usuário ou aposta não podem estar vazios"));
        exit();
    }

    // Verifica o saldo do usuário
    $stmt = $conn->prepare("SELECT energia FROM TB_Usuario WHERE nome_user = ?");
    $stmt->bind_param("s", $nome_user);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $saldo = $row['energia'];

        // Verifica se o usuário tem saldo suficiente para apostar
        if ($saldo < $energia) {
            echo json_encode(array("status" => "error", "message" => "Saldo insuficiente"));
            exit();
        }

        // Gira a roleta e calcula o resultado
        $resultado = girarRoleta();

        // Verifica se o usuário ganhou
        $ganhou = verificarVitoria($resultado);
        $premio = 0;

        if ($ganhou) {
            $premio = $energia * 2; // Exemplo: multiplica por 2 o valor da aposta
            $novoSaldo = $saldo + $premio;
            $mensagem = "Parabéns! Você ganhou $premio FatecCoins!";
        } else {
            $novoSaldo = $saldo - $energia;
            $mensagem = "Que pena! Tente novamente.";
        }

        // Atualiza o saldo do usuário no banco de dados
        $stmt = $conn->prepare("UPDATE TB_Usuario SET energia = ? WHERE nome_user = ?");
        $stmt->bind_param("is", $novoSaldo, $usuario);
        $stmt->execute();

        // Retorna o resultado e o novo saldo
        echo json_encode(array(
            "status" => "success",
            "resultado" => $resultado,
            "ganhou" => $ganhou,
            "premio" => $premio,
            "saldo" => $novoSaldo,
            "message" => $mensagem
        ));
    } else {
        echo json_encode(array("status" => "error", "message" => "Usuário não encontrado"));
    }

    $stmt->close();
}
$conn->close();

// Função para girar a roleta e gerar o resultado
function girarRoleta() {
    // Exemplo de 9 símbolos possíveis
    $simbolos = array("Boto", "Onça", "Arara", "Macaco", "Capivara", "Moedas", "Espinho", "Tucano", "Tesouro");

    // Gera um array de 3x3 com símbolos aleatórios
    $roleta = array(
        array($simbolos[array_rand($simbolos)], $simbolos[array_rand($simbolos)], $simbolos[array_rand($simbolos)]),
        array($simbolos[array_rand($simbolos)], $simbolos[array_rand($simbolos)], $simbolos[array_rand($simbolos)]),
        array($simbolos[array_rand($simbolos)], $simbolos[array_rand($simbolos)], $simbolos[array_rand($simbolos)])
    );

    return $roleta;
}

// Função para verificar se houve vitória
function verificarVitoria($resultado) {
    // Verifica linhas horizontais, verticais e diagonais
    return (
        // Linhas horizontais
        ($resultado[0][0] === $resultado[0][1] && $resultado[0][1] === $resultado[0][2]) ||
        ($resultado[1][0] === $resultado[1][1] && $resultado[1][1] === $resultado[1][2]) ||
        ($resultado[2][0] === $resultado[2][1] && $resultado[2][1] === $resultado[2][2]) ||

        // Colunas verticais
        ($resultado[0][0] === $resultado[1][0] && $resultado[1][0] === $resultado[2][0]) ||
        ($resultado[0][1] === $resultado[1][1] && $resultado[1][1] === $resultado[2][1]) ||
        ($resultado[0][2] === $resultado[1][2] && $resultado[1][2] === $resultado[2][2]) ||

        // Diagonais
        ($resultado[0][0] === $resultado[1][1] && $resultado[1][1] === $resultado[2][2]) ||
        ($resultado[0][2] === $resultado[1][1] && $resultado[1][1] === $resultado[2][0])
    );
}
?>
