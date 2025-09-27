<?php

// Configurar cabeceras para JSON
header("Content-Type: application/json; charset=UTF-8");

$host = 'localhost';
$user = 'root';
$password = '';
$dbname = 'inventario';

// $host = 'sql213.infinityfree.com';
// $user = 'if0_40035413';
// $password = 'pereirasii78';
// $dbname = 'if0_40035413_inventario';

$conn = new mysqli($host, $user, $password, $dbname);

if ($conn->connect_error) {
    die("Conexión fallida: " . $conn->connect_error);
}

$action = $_GET['action'] ?? '';

switch ($action) {
    case 'list':
        $sql = "SELECT * FROM datos";
        $result = $conn->query($sql);

        $productos = [];
        if ($result->num_rows > 0) {
            $productos = array();
            while ($row = $result->fetch_assoc()) {
                $productos[] = $row;
            }
        } else {
            $productos = array();
        }
        echo json_encode($productos);
        break;
    case 'save':
        $name = $_POST['name'] ?? '';
        $amount = $_POST['amount'] ?? 0;
        $price = $_POST['price'] ?? 0;
        $sql = $conn->prepare("INSERT INTO datos (name, amount, price) VALUES (?, ?, ?)");
        $sql->bind_param("sid", $name, $amount, $price);
        if ($sql->execute()) {
            echo json_encode([
                "success" => true,
                "id" => $sql->insert_id, // ID del nuevo registro
                "name" => $name,
                "amount" => $amount,
                "price" => $price
            ]);
        } else {
            echo json_encode(["error" => $stmt->error]);
        }
        break;
    default:
        echo json_encode(["error" => "Acción no válida"]);
        exit;
}


// Responder con los datos en formato JSON

?>