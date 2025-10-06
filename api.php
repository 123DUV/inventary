<?php
// Configurar cabeceras para JSON
header("Content-Type: application/json; charset=UTF-8");

//$host = '127.0.0.1'; //localhost
//$user = 'root';
//$password = '';
//$dbname = 'inventario';
$host = 'sql213.infinityfree.com';
$user = 'if0_40035413';
$password = 'pereirasii78';
$dbname = 'if0_40035413_inventario';

// Crear conexión
$conn = new mysqli($host, $user, $password, $dbname);

// 🔹 Asegurar que la conexión use UTF-8
$conn->set_charset("utf8mb4");

if ($conn->connect_error) {
  die(json_encode(["error" => "Conexión fallida: " . $conn->connect_error]));
}

$action = $_GET['action'] ?? '';

switch ($action) {
  case 'list':
    $sql = "SELECT * FROM datos";
    $result = $conn->query($sql);

    $productos = [];
    if ($result && $result->num_rows > 0) {
      while ($row = $result->fetch_assoc()) {
        $productos[] = $row;
      }
    }

    // 🔹 Aquí usamos JSON_UNESCAPED_UNICODE para que no salgan símbolos raros
    echo json_encode($productos, JSON_UNESCAPED_UNICODE);
    break;

  case 'save':
    $name = $_POST['name'] ?? '';
    $amount = $_POST['amount'] ?? 0;
    $price = $_POST['price'] ?? 0;
    $totalPrice = $_POST['totalPrice'] ?? 0;

    $sql = "SELECT * FROM datos";
    $result = $conn->query($sql);

    $productos = [];
    if ($result && $result->num_rows > 0) {
      while ($row = $result->fetch_assoc()) {
        $productos[] = $row;
      }
    }

    foreach ($productos as $producto) {
      if (strcasecmp($producto['name'], $name) === 0) {
        http_response_code(409); // Conflict
        echo json_encode(["error" => "El producto ya existe"], JSON_UNESCAPED_UNICODE);
        exit;
      }
    }

    $stmt = $conn->prepare("INSERT INTO datos (name, amount, price, totalPrice) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("sidd", $name, $amount, $price, $totalPrice);
    if ($stmt->execute()) {
      http_response_code(200);
      echo json_encode([
        "success" => true,
        "id" => $stmt->insert_id,
        "name" => $name,
        "amount" => $amount,
        "price" => $price,
        "totalPrice" => $totalPrice
      ], JSON_UNESCAPED_UNICODE);
    } else {
      echo json_encode(["error" => $stmt->error], JSON_UNESCAPED_UNICODE);
    }
    break;

  case "sumAmount":
    $id = $_POST['id'] ?? 0;
    $stmt = $conn->prepare("UPDATE datos SET amount = amount + 1, totalPrice = amount*price WHERE id = ?");
    $stmt->bind_param("i", $id);
    if ($stmt->execute()) {
      echo json_encode(["success" => true], JSON_UNESCAPED_UNICODE);
    } else {
      echo json_encode(["error" => $stmt->error], JSON_UNESCAPED_UNICODE);
    }
    break;

  case "less":
    $id = $_POST['id'] ?? 0;
    $stmt = $conn->prepare("UPDATE datos SET amount = amount - 1, totalPrice = amount*price WHERE id = ? AND amount > 0");
    $stmt->bind_param("i", $id);
    if ($stmt->execute()) {
      if ($stmt->affected_rows > 0) {
        echo json_encode(["success" => true], JSON_UNESCAPED_UNICODE);
      } else {
        echo json_encode(["error" => "No se puede reducir más la cantidad"], JSON_UNESCAPED_UNICODE);
      }
    } else {
      echo json_encode(["error" => $stmt->error], JSON_UNESCAPED_UNICODE);
    }
    break;

  case "deleteRow":
    $id = $_POST['id'] ?? 0;
    $stmt = $conn->prepare("DELETE FROM datos WHERE id = ?");
    $stmt->bind_param("i", $id);
    if ($stmt->execute()) {
      if ($stmt->affected_rows > 0) {
        echo json_encode(["success" => true], JSON_UNESCAPED_UNICODE);
      } else {
        echo json_encode(["error" => "No se puede borrar más"], JSON_UNESCAPED_UNICODE);
      }
    } else {
      echo json_encode(["error" => $stmt->error], JSON_UNESCAPED_UNICODE);
    }
    break;

  default:
    echo json_encode(["error" => "Acción no válida"], JSON_UNESCAPED_UNICODE);
    exit;
}

// Cerrar conexión
$conn->close();
?>