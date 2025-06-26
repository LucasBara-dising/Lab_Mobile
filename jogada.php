<?php

header('Content-Type: application/json'); // Define o tipo de conteúdo como JSON

require_once 'conn_db.php'; // Inclui o arquivo de configuração do banco de dados

// Função para executar as consultas SQL que retorna o
function fetchCardsDeck($conn, $ids) {
    $placeholders = implode(',', array_fill(0, count($ids), '?'));
    $sql = "SELECT id_carta, nome, raridade, tipo, vida, mana, energia, imagem
            FROM tb_carta
            WHERE id_carta IN ($placeholders)";
    
    $stmt = $conn->prepare($sql);
    $types = str_repeat('i', count($ids)); // 'i' para inteiros
    $stmt->bind_param($types, ...$ids);
    $stmt->execute();
    return $stmt->get_result();
}

// Verifica se os parâmetros estão presentes
if (isset($_GET['nome_user'], $_GET['deck'], $_GET['deck_bot'], $_GET['carta'], $_GET['carta_bot'], $_GET['vida_user'], $_GET['energia_user'], $_GET['vida_bot'], $_GET['energia_bot'])) {
    $nome_user = $_GET['nome_user'];
    $deck = explode(',', $_GET['deck']); // Converte o deck em array
    $carta = $_GET['carta'];
    $vida_user=$_GET['vida_user'];
    $energia_user=$_GET['energia_user'];


    $deck_bot = explode(',', $_GET['deck_bot']);
    $carta_bot = $_GET['carta_bot'];
    $vida_bot=$_GET['vida_bot'];
    $energia_bot=$_GET['energia_bot'];

    

    // Busca as cartas do deck do usuário
    $result = fetchCardsDeck($conn, $deck);
    $deck_user_obj = [];
    while ($card = $result->fetch_assoc()) {
        $deck_user_obj[] = [
            "id" => $card['id_carta'],
            "nome" => utf8_encode($card['nome']),
            "raridade" => utf8_encode($card['raridade']),
            "tipo" => utf8_encode($card['tipo']),
            "vida" => $card['vida'],
            "ataque" => $card['mana'],
            "energia" => $card['energia'],
            "imagem" => $card['imagem'],
            "morta" => 0,
        ];
    }

    // Busca as cartas do deck do bot
    $result = fetchCardsDeck($conn, $deck_bot);
    $deck_bot_obj = [];
    while ($card = $result->fetch_assoc()) {
        $deck_bot_obj[] = [
            "id" => $card['id_carta'],
            "nome" => utf8_encode($card['nome']),
            "raridade" => utf8_encode($card['raridade']),
            "tipo" => utf8_encode($card['tipo']),
            "vida" => $card['vida'],
            "ataque" => $card['mana'],
            "energia" => $card['energia'],
            "imagem" => $card['imagem'],
            "morta" => 0,
        ];
    }

    // Busca a carta do usuário
    $stmt = $conn->prepare("SELECT id_carta, nome, raridade, tipo, vida, mana, energia, imagem FROM tb_carta WHERE id_carta = ?");
    $stmt->bind_param("i", $carta);
    $stmt->execute();
    $result = $stmt->get_result();
    $carta_user = [];
    while ($card = $result->fetch_assoc()) {
        $carta_user=[
            "id" => $card['id_carta'],
            "nome" => utf8_encode($card['nome']),
            "raridade" => utf8_encode($card['raridade']),
            "tipo" => utf8_encode($card['tipo']),
            "vida" => $card['vida'],
            "ataque" => $card['mana'],
            "energia" => $card['energia'],
            "imagem" => $card['imagem'],
            "morta" => 0,
        ];
    }

    // Busca a carta do bot
    $stmt->bind_param("i", $carta_bot);
    $stmt->execute();
    $result = $stmt->get_result();
    $carta_bot_obj = [];
    while ($card = $result->fetch_assoc()) {
        $carta_bot_obj=[
            "id" => $card['id_carta'],
            "nome" => utf8_encode($card['nome']),
            "raridade" => utf8_encode($card['raridade']),
            "tipo" => utf8_encode($card['tipo']),
            "vida" => $card['vida'],
            "ataque" => $card['mana'],
            "energia" => $card['energia'],
            "imagem" => $card['imagem'],
            "morta" => 0,
        ];
    }

    //debita energia
    $energia_user = $energia_user-$carta_user['energia'];
    $energia_bot = $energia_bot-$carta_bot_obj['energia'];


    //-----Batalha----
    $dano_user = $carta_user['ataque'];
    $dano_bot = $carta_bot_obj['ataque'];

    $vida_carta_user =  $carta_user['vida'] - $dano_bot;
    $vida_carta_bot =  $carta_bot_obj['vida'] - $dano_user;

    //exendeo tira do jogador
    $dano_vida_user = $vida_user + $vida_carta_user < 0 ? 0 : -abs($vida_carta_user);
    $dano_vida_bot = $vida_bot + $vida_carta_bot < 0 ? 0 : -abs($vida_carta_bot);

    $vida_user_pos = $vida_bot + $dano_vida_bot;

    // Atualizando a vida do personagem no deck do user
    $ids = array_column($deck_user_obj, 'id');
    $indice = array_search($carta_user['id'], $ids);

    $deck_user_obj[$indice]['vida']=$vida_carta_user;

     // Atualizando a vida do personagem no deck do bot
    $ids = array_column($carta_bot_obj, 'id');
    $indice = array_search($deck_bot_obj['id'], $ids);

    $deck_bot_obj[$indice]['vida']=$vida_carta_bot;

    
    //verifica se a enegria ou hp zerou
    if(($vida_bot <0 && $vida_user<0) || ($energia_bot <0 && $energia_user<0)){
        $result = "Empate";
    }elseif(($vida_bot <0 && $energia_bot<0)){
        $result = "Vitoria Do Jogador";
    }elseif(($vida_user <0 && $energia_user<0)){
        $result = "Vitoria Do BOT";
    }else{
        $result = "Continua";
    }


    // logs de jogo
    $stmt = $conn->prepare("INSERT INTO tb_batalha (id_user, deck, deck_bot, jogada)
                            VALUES ((Select id_user from tb_usuario where nome_usuario= ?), ?,?,?)");
    $stmt->bind_param("ssss", $nome_user, $deck, $deck_bot, $result); // "s" indica que o parâmetro é uma string

    // Resposta JSON
    echo json_encode([
        "status" => "success",
        "jogador_id"=>  $nome_user,
        "hp_jogador"=>  $vida_user + $dano_vida_user,
        "hp_bot"=>  $vida_bot + $dano_vida_bot,
        "energia_jogador"=>  $energia_user,
        "energia_bot"=>  $energia_bot,
        "resultado" => $result,
        "deck-user" => $deck_user_obj,
        "deck-bot" => $deck_bot_obj,
        "carta-user" => $carta_user,
        "carta-bot" => $carta_bot_obj,
    ], JSON_PRETTY_PRINT);

    // Fechar a declaração
    $stmt->close();
} else {
    // Parâmetro 'nome_user' não fornecido
    echo json_encode(["mensagem" => "Parâmetros são obrigatório"], JSON_PRETTY_PRINT);
}

// Fechar a conexão com o banco de dados
$conn->close();

?>
