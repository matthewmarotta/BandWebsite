<?php
$servername = "localhost"; 
$username = "root";
$password = "";
$dbname = "bandwebsitedatabase2"; 

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    
    $jsonData = file_get_contents('php://input');
    $requestData = json_decode($jsonData, true);


   // $requestData = json_decode(file_get_contents("php://input"), true);
    
    if (isset($requestData['itemName']) && isset($requestData['itemPrice']) && isset($requestData['quantity']) && isset($requestData['orderId'])) {
        $conn = new mysqli($servername, $username, $password, $dbname);
        
        if ($conn->connect_error) {
            error_log("Connection failed: " . $conn->connect_error);
            die(json_encode(["success" => false, "message" => "Connection failed: " . $conn->connect_error]));
        }
        
        $conn->begin_transaction();
        
        try {
            // Step 1: Fetch Item_ID from the item table based on itemName
            $sql = "SELECT Item_ID FROM items WHERE Name = ?";
            $stmt = $conn->prepare($sql);
           
            if (!$stmt) {
                throw new Exception($conn->error);
            }
            
            $stmt->bind_param("s", $requestData['itemName']);
            $stmt->execute();
            $stmt->bind_result($itemId);
            $stmt->fetch();
            $stmt->close();
            
            if (!$itemId) {
                throw new Exception("Item not found in the database.");
            }
            
            // Step 2: Insert into orderinformationtable
            // HERE'S THE FIX: Use the order ID from the request, not generate a new one
            $sql = "INSERT INTO orderinformationtable (Item_ID, Order_Quantity, Item_Price, Order_ID) VALUES (?, ?, ?, ?)";
            
            $stmt = $conn->prepare($sql);
           
            if (!$stmt) {
                throw new Exception($conn->error);
            }
            
            // Use $requestData['orderId'] instead of $orderId
            $stmt->bind_param("iiis", $itemId, $requestData['quantity'], $requestData['itemPrice'], $requestData['orderId']);
            $stmt->execute();
           
            if ($stmt->affected_rows <= 0) {
                throw new Exception("Failed to insert order information.");
            }
            
            $stmt->close();
            
            // Commit the transaction
            $conn->commit();
            
            // Send success response with the order ID
            echo json_encode([
                "success" => true,
                "message" => "Order item added successfully",
                "orderId" => $requestData['orderId']
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
        echo json_encode(["success" => false, "message" => "Invalid data received"]);
    }
}
?>