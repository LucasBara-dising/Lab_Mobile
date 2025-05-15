<?php
header('Content-Type: application/json');

require_once 'conn_db.php';

// Verifica se os dados foram enviados via POST
if ($_SERVER['REQUEST_METHOD'] == 'POST') {

    $nome_user = $_POST['nome_user'];

    // Verificar se os campos estão preenchidos
    if (empty($nome_user)) {
        echo json_encode(array("status" => "error", "message" => "Usuário  não podem estar vazios"));
        exit();
    }

    echo $nome_user ;

    // Verifica o saldo do usuário
    $stmt = $conn->prepare("SELECT rodadas FROM tb_usuario WHERE nome_usuario = ?");
    $stmt->bind_param("s", $nome_user);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $saldo = $row['rodadas'];


        // Verifica se o usuário tem saldo suficiente para apostar
        if ($saldo < 1) {
            echo json_encode(array("status" => "error", "message" => "Saldo insuficiente"));
            exit();
        }

        // Gira a roleta e calcula o resultado
        $resultado = girarRoleta();

        // Verifica se o usuário ganhou
        $ganhou = verificarVitoria($resultado);

        $figura = verifica_figura($resultado);
        
        $premio = define_premios($figura, $usuario);

        // Atualiza o saldo do usuário no banco de dados
        $stmt = $conn->prepare("UPDATE tb_usuario SET rodadas = ? WHERE nome_usuario = ?");
        $novoSaldo = $saldo - 1;
        $stmt->bind_param("is", $novoSaldo, $usuario);
        $stmt->execute();
       

        // // Retorna o resultado e o novo saldo
         echo json_encode(array(
            "status" => "success",
            "resultado" => $resultado,
            "ganhou" => $ganhou,
            "item_sequencia" => utf8_encode($figura),
            "premio" => utf8_encode($premio),
            "saldo" => $novoSaldo
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
    //$simbolos = array("Boto", "Onça");

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

    function verifica_figura($matrix){
        // Verifica linhas
        for ($i = 0; $i < 3; $i++) {
            if ($matrix[$i][0] === $matrix[$i][1] && $matrix[$i][1] === $matrix[$i][2]) {
                return  $matrix[$i][0];
            }
        }
    
        // Verifica colunas
        for ($j = 0; $j < 3; $j++) {
            if ($matrix[0][$j] === $matrix[1][$j] && $matrix[1][$j] === $matrix[2][$j]) {
                return $matrix[0][$j];
            }
        }
    
         // Verifica diagonal principal
         if ($matrix[0][0] === $matrix[1][1] && $matrix[1][1] === $matrix[2][2]) {
            return  $matrix[0][0];
        }
    
        // Verifica diagonal secundária
        if ($matrix[0][2] === $matrix[1][1] && $matrix[1][1] === $matrix[2][0]) {
            return  $matrix[0][2];
        }
    
        return "Nada";
    }


    function define_premios($item, $usuario){
        switch ($item) {
            case "Boto":
                return "carta";
                break;
            case "Onça":
                return "carta boa";
                break;
            case "Arara":
                // Atualiza o saldo do usuário no banco de dados
                $stmt = $conn->prepare("UPDATE tb_usuario SET rodadas = ? WHERE nome_user = ?");
                $novoSaldo = $saldo + 2;
                $stmt->bind_param("is", $novoSaldo, $usuario);
                $stmt->execute();
                return "Mais 2 rodadas";
                break;
            case "macaco":
                return "Nada dessa vez";
                break;
            case "Moedas":
                // Atualiza o saldo do usuário no banco de dados
                $stmt = $conn->prepare("UPDATE tb_usuario SET moedas = ? WHERE nome_user = ?");
                $novoSaldo = $saldo + 50;
                $stmt->bind_param("is", $novoSaldo, $usuario);
                $stmt->execute();
                return "Mais 50 moedas";
                break;
            case "Espinho":
                return "carta media";
                break;
            case "Tucano":
                // Atualiza o saldo do usuário no banco de dados
                $stmt = $conn->prepare("UPDATE tb_usuario SET rodadas = ? WHERE nome_user = ?");
                $novoSaldo = $saldo + 5;
                $stmt->bind_param("is", $novoSaldo, $usuario);
                $stmt->execute();
                return "mais 5 rodadas";
                break;
            case "Tesouro":
                 // Atualiza o saldo do usuário no banco de dados
                 $stmt = $conn->prepare("UPDATE tb_usuario SET moedas = ? WHERE nome_user = ?");
                 $novoSaldo = $saldo + 250;
                 $stmt->bind_param("is", $novoSaldo, $usuario);
                 $stmt->execute();
                return " 250 moedas";
                break;
            case "Nada":
                return " Sem sequencia";
                break;
            
        }
        
    }
?>
