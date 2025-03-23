<?php
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "bandwebsitedatabase2";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $requestData = json_decode(file_get_contents("php://input"), true);
    
    if (isset($requestData['itemName']) && isset($requestData['quantity'])) {
        $conn = new mysqli($servername, $username, $password, $dbname);
        
        if ($conn->connect_error) {
            die(json_encode(["success" => false, "message" => "Connection failed: " . $conn->connect_error]));
        }
        
        // Just check if there's enough quantity available WITHOUT updating
        $sql = "SELECT (Quantity_Available - ?) as remaining FROM items WHERE Name = ?";
        $stmt = $conn->prepare($sql);
        
        if (!$stmt) {
            echo json_encode(["success" => false, "message" => "Prepare failed: " . $conn->error]);
            exit();
        }
        
        $stmt->bind_param("is", $requestData['quantity'], $requestData['itemName']);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows > 0) {
            $row = $result->fetch_assoc();
            
            if ($row['remaining'] < 0) {
                $exceededAmount = abs($row['remaining']);
                $itemName = $requestData['itemName'];
                echo json_encode([
                    'success' => false,
                    'message' => "Not enough available stock, remove $exceededAmount of $itemName item from your cart"
                ]);
            } else {
                echo json_encode(["success" => true]);
            }
        } else {
            echo json_encode(["success" => false, "message" => "Item not found"]);
        }
        
        $stmt->close();
        $conn->close();
    } else {
        echo json_encode(["success" => false, "message" => "Invalid data received"]);
    }
} else {
    echo json_encode(["success" => false, "message" => "Invalid request method"]);
}

