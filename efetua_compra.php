<?php

header('Content-Type: application/json');

require_once 'conn_db.php';

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    echo json_encode(["status" => "error", "message" => "Método não permitido"]);
    exit;
}

// Verificar se os parâmetros estão presentes
$requiredFields = ['nome_user', 'id_card', 'tipo'];
foreach ($requiredFields as $field) {
    if (empty($_GET[$field])) {
        echo json_encode(["status" => "error", "message" => "Campo '$field' não pode estar vazio"]);
        exit;
    }
}

// Receber os dados
$nome_user = $_GET['nome_user'];
$id_card   = (int) $_GET['id_card'];
$tipo      = $_GET['tipo'];

// Inicializar saldos
$saldo_rodada = 0;
$saldo_moedas = 0;

//------------------------------------------\\
// Obter saldo do usuário
$stmt = $conn->prepare("SELECT rodadas, moedas FROM tb_usuario WHERE nome_usuario = ?");
$stmt->bind_param("s", $nome_user);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    echo json_encode(["status" => "error", "message" => "Usuário não encontrado"]);
    exit;
}

$user = $result->fetch_assoc();
$saldo_rodada = $user['rodadas'];
$saldo_moedas = $user['moedas'];
$stmt->close();

//------------------------------------------\\
if($tipo ==="Rodada"){
    // Obter item da loja
    $stmt = $conn->prepare("SELECT preco FROM tb_itens_loja WHERE id = ? AND tipo = ?");
    $stmt->bind_param("is", $id_card, $tipo);
    $stmt->execute();
    $result_item = $stmt->get_result();

    if ($result_item->num_rows === 0) {
        echo json_encode(["status" => "error", "message" => "Item não encontrado"]);
        exit;
    }

    $item = $result_item->fetch_assoc();
    $preco_item = $item['preco'];
    $stmt->close();
    
}elseif($tipo ==="Carta"){
    // Obter item carta da loja
    $stmt = $conn->prepare("SELECT raridade FROM tb_carta WHERE id_carta = ? ");
    $stmt->bind_param("i", $id_card);
    $stmt->execute();
    $result_item = $stmt->get_result();

    if ($result_item->num_rows === 0) {
        echo json_encode(["status" => "error", "message" => "Item de carta não encontrado"]);
        exit;
    }

    $item = $result_item->fetch_assoc();
    $preco_item = setValor($item['raridade']);
    $stmt->close();
}

//-------------------------------------------------------------------\\

//--------------------Debito-----------------\\
    // Verifica se o usuário tem moedas suficientes
if ($saldo_moedas < $preco_item) {
    echo json_encode(["status" => "error", "message" => "Saldo insuficiente"]);
    exit;
}
// Debitar moedas
$novo_saldo_moedas = $saldo_moedas - $preco_item;
$stmt = $conn->prepare("UPDATE tb_usuario SET moedas = ? WHERE nome_usuario = ?");
$stmt->bind_param("is", $novo_saldo_moedas, $nome_user);
$stmt->execute();
$stmt->close();


// Processar compra com base no tipo
if ($tipo === "Carta") {
    $stmt = $conn->prepare("INSERT INTO tb_usuarios_itens (user_id, item_id, tipo_item)
        VALUES ((SELECT id_user FROM tb_usuario WHERE nome_usuario = ?), ?, ?)");
    $stmt->bind_param("sis", $nome_user, $id_card, $tipo);
    $stmt->execute();
    $stmt->close();

    echo json_encode(["status" => "success", "message" => "Carta adicionada"]);
} elseif ($tipo === "Rodada") {
    $add_rodadas = match($preco_item) {
        100 => 5,
        1000 => 100,
        default => 0
    };

    if ($add_rodadas === 0) {
        echo json_encode(["status" => "error", "message" => "Preço inválido para tipo 'Rodada'"]);
        exit;
    }

    $novo_saldo_rodada = $saldo_rodada + $add_rodadas;
    $stmt = $conn->prepare("UPDATE tb_usuario SET rodadas = ? WHERE nome_usuario = ?");
    $stmt->bind_param("is", $novo_saldo_rodada, $nome_user);
    $stmt->execute();
    $stmt->close();

    echo json_encode(["status" => "success", "message" => "$add_rodadas rodadas adicionadas"]);
} else {
    echo json_encode(["status" => "error", "message" => "Tipo de item inválido"]);
}

// Fechar conexão
$conn->close();

function setValor($raridade){
    $valor=0;
    if($raridade == "Comum" ){
        $valor = 150;
    }elseif($raridade == "Raro" ){
        $valor = 500;
    }elseif($raridade == "Épico" ){
        $valor = 1500;
    }elseif($raridade == "Lendario" ){
        $valor = 5000;
    }else{
        $valor = 2800;
    }

    return $valor;
}
?>