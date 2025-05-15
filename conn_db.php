<?php

// $user = "root";
// $password = "password";

$host = "oncinha.mysql.dbaas.com.br";
$user = "oncinha";
$password = "DD32HL34q3uA#";
$db = "oncinha";


$conn = new mysqli($host, $user, $password, $db);

if ($conn->connect_error) {
    echo json_encode(array("status" => "error", "message" => "Falha na conexão com o banco de dados"));
    exit();
}
?>
