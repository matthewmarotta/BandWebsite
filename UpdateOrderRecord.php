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
    error_log("Received data: " . print_r($requestData, true));
    if (isset($requestData['orderId']) && isset($requestData['userId'])) {
        $conn = new mysqli($servername, $username, $password, $dbname);
        
        if ($conn->connect_error) {
            error_log("Connection failed: " . $conn->connect_error);
            die(json_encode(["success" => false, "message" => "Connection failed: " . $conn->connect_error]));
        }
        
        $conn->begin_transaction();
        
        try { 
            $timestamp = date("Y-m-d H:i:s");
            $sql = "INSERT INTO orders (Order_ID, Order_Date, Order_Status, User_ID) VALUES (?, ?, 'pending', ?)";
            $stmt = $conn->prepare($sql);
            
            if (!$stmt) {
                throw new Exception($conn->error);
            }
            
            $stmt->bind_param("sss", $requestData['orderId'], $timestamp, $requestData['userId']);
            $stmt->execute();
            $stmt->close();

            $sql = "UPDATE orders
                    SET Total_Amount = (
                    SELECT SUM(Item_Price)
                    FROM orderinformationtable
                    WHERE orderinformationtable.Order_ID = Order_ID
                    );";
            $stmt = $conn->prepare($sql);
            
            if (!$stmt) {
                throw new Exception($conn->error);
            }
            
            $stmt->execute();
            $stmt->close();
            // Commit the transaction
            $conn->commit();
            
            // Send success response with the generated orderId
            echo json_encode([
                "success" => true,
                "message" => "Order created successfully",
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
?>