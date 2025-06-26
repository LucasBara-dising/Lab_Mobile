<?php

// $user = "root";
// $password = "password";




$conn = new mysqli($host, $user, $password, $db);

if ($conn->connect_error) {
    echo json_encode(array("status" => "error", "message" => "Falha na conexão com o banco de dados"));
    exit();
}
?>
