<?php
// create_order.php - Creates a unique order ID on the server
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "bandwebsitedatabase2";

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $requestData = json_decode(file_get_contents("php://input"), true);
    
    if (isset($requestData['action']) && $requestData['action'] === 'create_orderID') {
        $conn = new mysqli($servername, $username, $password, $dbname);
        
        if ($conn->connect_error) {
            error_log("Connection failed: " . $conn->connect_error);
            die(json_encode(["success" => false, "message" => "Connection failed: " . $conn->connect_error]));
        }
        
        $conn->begin_transaction();
        
        try {
            // Generate a secure order ID
            $orderId = "ORDER_" . uniqid() . "_" . bin2hex(random_bytes(4));
            
            // Commit the transaction
            $conn->commit();
            
           
         // Send success response with the generated orderId
            header('Content-Type: application/json');
            echo json_encode([
                "success" => true,
                "message" => "Order created successfully",
                "orderId" => $orderId
            ]);
            
        } catch (Exception $e) {
            // Rollback the transaction in case of error
            $conn->rollback();
            echo json_encode([
                "success" => false,
                "message" => $e->getMessage()
            ]);
        } finally {
            $conn->close();
        }
    } else {
        echo json_encode(["success" => false, "message" => "Invalid request"]);
    }
}
