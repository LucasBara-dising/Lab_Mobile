<?php
header('Content-Type: application/json');

require_once 'conn_db.php';

global $saldo_moedas, $saldo_rodada;

// Verifica se os dados foram enviados via POST
if ($_SERVER['REQUEST_METHOD'] == 'GET') {

    // Verificar se os campos estão preenchidos
    if (empty($_GET['nome_user'])) {
        echo json_encode(array("status" => "error", "message" => "Usuário não podem estar vazios"));
        exit();
    }

    $nome_user = $_GET['nome_user'];

    // Verifica o saldo do usuário
    $stmt = $conn->prepare("SELECT rodadas, moedas FROM tb_usuario WHERE nome_usuario = ?");
    $stmt->bind_param("s", $nome_user);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $saldo_rodada = $row['rodadas'];
        $saldo_moedas = $row['moedas'];

        // Verifica se o usuário tem saldo suficiente para apostar
        if ($saldo_rodada < 1) {
            echo json_encode(array("status" => "error", "message" => "Saldo insuficiente"));
            exit();
        }

        list($resultado, $ganhou) = gerarJogadaComChance(0.7);

        $figura = verifica_figura($resultado);

        // Atualiza o saldo do usuário no banco de dados
        $stmt = $conn->prepare("UPDATE tb_usuario SET rodadas = ? WHERE nome_usuario = ?");
        $saldo_rodada = $saldo_rodada - 1;
        $stmt->bind_param("is", $saldo_rodada, $nome_user);
        $stmt->execute();
       
        $premio = define_premios($conn, $figura, $nome_user, $saldo_moedas, $saldo_rodada);

        //Atualiza valor de saldo
        $stmt = $conn->prepare("SELECT rodadas, moedas FROM tb_usuario WHERE nome_usuario = ?");
        $stmt->bind_param("s", $nome_user);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            $row = $result->fetch_assoc();
            $saldo_rodada = $row['rodadas'];
            $saldo_moedas = $row['moedas'];
        }
        

        // // Retorna o resultado e o novo saldo
         echo json_encode(array(
            "status" => "success",
            "resultado" => utf8_encode($figura),
            "ganhou" => $ganhou,
            "item_sequencia" => $resultado,
            "premio" => utf8_encode($premio),
            "saldo" => $saldo_rodada,
            "moedas" => $saldo_moedas
        ));
    } else {
        echo json_encode(array("status" => "error", "message" => "Usuário não encontrado"));
    }


    $stmt->close();
}
$conn->close();


function gerarJogadaComChance($chanceDeVitoria = 0.7) {
    $simbolos = array("Boto", "Onça", "Arara", "Macaco", "Capivara", "Moedas", "Espinho", "Tucano", "Tesouro");

    // A matriz 3x3 de símbolos (simulando 3 linhas x 3 colunas)
    $matriz = array_fill(0, 3, array_fill(0, 3, null));

    // Decide se será uma jogada vencedora ou não
    $comVitoria = mt_rand(0, 100) < ($chanceDeVitoria * 100);

    if ($comVitoria) {
        $simbolo = $simbolos[array_rand($simbolos)];
        $linha = rand(0, 2);
        // Força uma linha com símbolos iguais
        for ($col = 0; $col < 3; $col++) {
            $matriz[$linha][$col] = $simbolo;
        }
        // Preenche o resto com símbolos aleatórios
        for ($i = 0; $i < 3; $i++) {
            for ($j = 0; $j < 3; $j++) {
                if ($matriz[$i][$j] === null) {
                    $matriz[$i][$j] = $simbolos[array_rand($simbolos)];
                }
            }
        }
    } else {
        // Preenche tudo aleatoriamente (sem garantir vitória)
        for ($i = 0; $i < 3; $i++) {
            for ($j = 0; $j < 3; $j++) {
                $matriz[$i][$j] = $simbolos[array_rand($simbolos)];
            }
        }
    }

    return [$matriz, $comVitoria];
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

function define_premios($conn, $item, $usuario, $saldoMoedas, $saldo_rodada){
         
    switch ($item) {
        case "Boto":
            return envia_carta($conn, $usuario, 7, 4, 1);
            break;
        case "Onça":
            return  envia_carta($conn, $usuario, 1, 3, 6);
            break;
        case "Arara":
            $stmt = $conn->prepare("UPDATE tb_usuario SET rodadas = ? WHERE nome_usuario = ?");
            $saldo_rodada = $saldo_rodada + 4;
            $stmt->bind_param("is", $saldo_rodada, $usuario);
            $stmt->execute();
            return "Mais 3 rodadas";
            break;
        case "Macaco":
            return "Nada dessa vez";
            break;
        case "Moedas":
            // Atualiza o saldo do usuário no banco de dados
            $stmt = $conn->prepare("UPDATE tb_usuario SET moedas = ? WHERE nome_usuario = ?");
            $saldo_moedas = $saldoMoedas + 50;
            $stmt->bind_param("is", $saldo_moedas, $usuario);
            $stmt->execute();
            return "Mais 50 moedas";
            break;
        case "Espinho":
            return envia_carta($conn, $usuario, 1, 5, 4);
            break;
        case "Tucano":
            // Atualiza o saldo do usuário no banco de dados         
            $stmt = $conn->prepare("UPDATE tb_usuario SET rodadas = ? WHERE nome_usuario = ?");
            $saldo_rodada = $saldo_rodada + 6;
            $stmt->bind_param("is", $saldo_rodada, $usuario);
            $stmt->execute();
            return "mais 5 rodadas";
            break;

        case "Capivara":
            /// Atualiza o saldo do usuário no banco de dados
            $stmt = $conn->prepare("UPDATE tb_usuario SET rodadas = ? WHERE nome_usuario = ?");
            $saldo_rodada = $saldo_rodada + 10;
            $stmt->bind_param("is", $saldo_rodada, $usuario);
            $stmt->execute();
            return "mais 10 rodadas";
            break;
        case "Tesouro":
            // Atualiza o saldo do usuário no banco de dados
            $stmt = $conn->prepare("UPDATE tb_usuario SET moedas = ? WHERE nome_usuario = ?");
            $saldo_moedas = $saldoMoedas + 250;
            $stmt->bind_param("is", $saldo_moedas, $usuario);
            $stmt->execute();
            return " 250 moedas";
            break;
        case "Nada":
            return "Sem sequencia";
            break;       
    }
        
}

function weighted_random_simple($values, $weights){ 
    $count = count($values); 
    $i = 0; 
    $n = 0; 
    $num = mt_rand(0, array_sum($weights)); 
    while($i < $count){
        $n += $weights[$i]; 
        if($n >= $num){
            break; 
        }
        $i++; 
    } 
    return $values[$i]; 
}
    
function envia_carta($conn, $nome_user, $peso_raridade_comum, $peso_raridade_raro, $peso_raridade_epico ) {
    // Verifica as cartas que ainda não possui
    $stmt = $conn->prepare("SELECT card.id_carta, itens_user.item_id, itens_user.user_id, card.nome, card.raridade from tb_carta as card 
                    left join tb_usuarios_itens as itens_user ON card.id_carta = itens_user.item_id  
                    where  itens_user.user_id <> (Select id_user from tb_usuario where nome_usuario= ?) OR  itens_user.user_id is null");

    $stmt->bind_param("s", $nome_user); // "s" indica que o parâmetro é uma string
    $stmt->execute();
    
    // Obter o resultado
    $result = $stmt->get_result();

    $usuario = $result->fetch_assoc();
    echo json_encode($usuario, JSON_PRETTY_PRINT); // Retorna os dados como JSON formatado

    if ($result->num_rows <= 0) {
        // Usuário encontrado
        echo json_encode(array("mensagem" => "Usuário não encontrado"), JSON_PRETTY_PRINT);
    }

    $cards_id = [];
    $cards_Pesos = [];
    
    // Processar os resultados
    while ($card = $result->fetch_assoc()) {
        // Definir o peso baseado na raridade
        $raridade = utf8_encode($card['raridade']);
        if ($raridade == 'Comum') {
            $peso_raridade = $peso_raridade_comum;
        } elseif ($raridade == 'Raro') {
            $peso_raridade = $peso_raridade_raro;
        } elseif ($raridade == 'Épico') {
            $peso_raridade = $peso_raridade_epico;
        }

        // Adicionar o card e seu peso à lista
        $cards_id[] = $card['id_carta'];
        $cards_Pesos[] = $peso_raridade;

    }

    // Preparar a consulta para inserir o item
    $stmt = $conn->prepare("INSERT INTO tb_usuarios_itens (user_id, item_id, tipo_item)
                            VALUES ((SELECT id_user FROM tb_usuario WHERE nome_usuario = ?), ?, ?)");
    
    // Selecionar uma carta com base nos pesos
    $id_nova_carta = weighted_random_simple($cards_id, $cards_Pesos);
    $tipo = "Carta";
    
    // Vincular os parâmetros e executar a consulta
    $stmt->bind_param("sis", $nome_user, $id_nova_carta, $tipo);
    $stmt->execute();

    // Verifica o saldo do usuário
    $stmt = $conn->prepare("SELECT nome, raridade from tb_carta where id_carta = ?");
    $stmt->bind_param("i", $id_nova_carta);
    $stmt->execute();
    $result = $stmt->get_result();
    
     if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $nome_carta = $row['nome'];
        $raridade_carta = $row['raridade'];
     }

    // Fecha a declaração
    $stmt->close();

    return "Carta ganha: $nome_carta, $raridade_carta";
}

?>
