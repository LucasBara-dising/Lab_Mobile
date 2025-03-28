<?php

$host = '127.0.0.1';
$db = 'API_mobile';
$port = 3306;
$user = '######';
$pass = '#######';


$conn = new mysqli($host, $user, $pass, $db);

if ($conn->connect_error) {
    echo json_encode(array("status" => "error", "message" => "Falha na conexão com o banco de dados"));
    exit();
}
?>