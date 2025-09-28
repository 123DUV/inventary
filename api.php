<?php

// Configurar cabeceras para JSON
header("Content-Type: application/json; charset=UTF-8");

// $host = 'localhost';
// $user = 'root';
// $password = '';
// $dbname = 'inventario';

$host = 'sql213.infinityfree.com';
$user = 'if0_40035413';
$password = 'pereirasii78';
$dbname = 'if0_40035413_inventario';

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
         $sql = "SELECT * FROM datos";
        $result = $conn->query($sql);

        $productos = [];
        if ($result->num_rows > 0) {
            $productos = array();
            while ($row = $result->fetch_assoc()) {
                $productos[] = $row;
            }
        }

        $name = $_POST['name'] ?? '';
        $amount = $_POST['amount'] ?? 0;
        $price = $_POST['price'] ?? 0;
        $totalPrice = $_POST['totalPrice'] ?? 0;
        
        foreach ($productos as $producto) {
            if (strcasecmp($producto['name'], $name) === 0) {
                http_response_code(409); // Conflict
                echo json_encode(["error" => "El producto ya existe"]);
                exit;
            }
        }

        $sql = $conn->prepare("INSERT INTO datos (name, amount, price, totalPrice) VALUES (?, ?, ?, ?)");
        $sql->bind_param("sidd", $name, $amount, $price, $totalPrice);
        if ($sql->execute()) {
            echo json_encode([
                "success" => true,
                "id" => $sql->insert_id, // ID del nuevo registro
                "name" => $name,
                "amount" => $amount,
                "price" => $price,
                "totalPrice"=> $totalPrice
            ]);
        } else {
            echo json_encode(["error" => $stmt->error]);
        }
        break;
        case "sumAmount":
            $id = $_POST['id'] ?? 0;

            $sql = $conn->prepare("UPDATE datos SET amount = amount + 1, totalPrice = amount*price WHERE id = ?");
            $sql->bind_param("i", $id);
            if ($sql->execute()) {
                http_response_code(200);
                echo json_encode(["success" => true]);
            } else {
                http_response_code(400);
                echo json_encode(["error" => $sql->error]);
            }
            break;
            case "less":
                $id = $_POST['id'] ?? 0;
                $sql = $conn->prepare("UPDATE datos SET amount = amount - 1, totalPrice = amount*price WHERE id = ? AND amount > 0");
                $sql->bind_param("i", $id);
                if ($sql->execute()) {
                    if ($sql->affected_rows > 0) {
                        http_response_code(200);
                        echo json_encode(["success" => true]);
                    } else {
                        http_response_code(400);
                        echo json_encode(["error" => "No se puede reducir más la cantidad"]);
                    }
                } else {
                    http_response_code(400);
                    echo json_encode(["error" => $sql->error]);
                }
                break;
                case "sumTotalPrice":
                    $sql = "SELECT  FROM datos";
                    break;
    default:
        echo json_encode(["error" => "Acción no válida"]);
        exit;
}


// Responder con los datos en formato JSON

?>