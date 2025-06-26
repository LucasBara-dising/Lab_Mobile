<?php
// Define o tipo de conteúdo como JSON
header('Content-Type: application/json');

// Verifica se a requisição é POST
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    
    // Lê os dados recebidos (se estiver no formato JSON)
    $data = json_decode(file_get_contents("php://input"), true);
    
    // Verifica se os dados foram recebidos corretamente
    if (isset($data['nome']) && isset($data['idade'])) {
        // Acessa os dados enviados via POST
        $nome = $data['nome'];
        $idade = $data['idade'];

        // Aqui você pode processar os dados como desejar (exemplo: salvar no banco de dados)
        
        // Simula um sucesso ao processar os dados
        $response = [
            'status' => 'success',
            'mensagem' => 'Dados recebidos com sucesso!',
            'dados' => [
                'nome' => $nome,
                'idade' => $idade
            ]
        ];
        
        // Retorna a resposta em formato JSON
        echo json_encode($response);
        
    } else {
        // Caso algum dado necessário não tenha sido enviado
        $response = [
            'status' => 'error',
            'mensagem' => 'Dados inválidos, nome e idade são obrigatórios.'
        ];
        echo json_encode($response);
    }
    
} else {
    // Caso o método da requisição não seja POST
    $response = [
        'status' => 'error',
        'mensagem' => 'Método inválido. Utilize POST para enviar dados.'
    ];
    echo json_encode($response);
}
?>
