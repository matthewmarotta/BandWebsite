<?php
$servername = "localhost"; 
$username = "root";
$password = "";
$dbname = "bandwebsitedatabase2"; 

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    
    $requestData = json_decode(file_get_contents("php://input"), true);

    if (isset($requestData['userId']) && isset($requestData['cartItems']) && isset($requestData['expirationTime'])) {
        $conn = new mysqli($servername, $username, $password, $dbname);

        if ($conn->connect_error) {
            error_log("Connection failed: " . $conn->connect_error);
            die(json_encode(["success" => false, "message" => "Connection failed: " . $conn->connect_error]));
        }
       
        $conn->begin_transaction();
        
        $cartItems = $requestData['cartItems'];
        $userId = $requestData['userId'];
        $expirationTime = $requestData['expirationTime'];
        
        try {
            $success = true;
            $messages = [];
            
            foreach ($cartItems as $item) {
              
                $imageUrl = $item['imageUrl'];
                $itemName = $item['itemName'];
                $itemPrice = $item['itemPrice'];
                $quantity = $item['quantity'];
                
                if ($quantity == 0){
                   $sql = "DELETE FROM cart WHERE Name = ?";
                   $stmt = $conn->prepare($sql);
            
                   if (!$stmt) {
                       throw new Exception($conn->error);
                   }
            
                   $stmt->bind_param("s", $itemName);
                   $stmt->execute();
                   $stmt->close();
                   continue;
                }
               
                $sql = "INSERT INTO cart (User_ID, Name, Image_Url, Price, Quantity, Timestamp_Expiration)
                VALUES (?, ?, ?, ?, ?, ?)
                ON DUPLICATE KEY UPDATE 
                Quantity = VALUES(Quantity),
                Name = VALUES(Name),
                Image_Url = VALUES(Image_Url),
                Price = VALUES(Price)";
                
                $stmt = $conn->prepare($sql);  
                if (!$stmt) {
                    throw new Exception($conn->error);
                }
              
              
                $stmt->bind_param("sssdis", 
                    $userId,
                    $itemName, 
                    $imageUrl, 
                    $itemPrice, 
                    $quantity,
                    $expirationTime
                ); 
                
                $result = $stmt->execute();
                if (!$result) {
                    $success = false;
                    $messages[] = "Failed to save item: " . $itemName;
                }
                
                $stmt->close();
            }
            
            if ($success) {
                $conn->commit();
                header('Content-Type: application/json');
                echo json_encode([
                    "success" => true,
                    "message" => "Cart items saved successfully",
                    "itemCount" => count($cartItems)
                ]);
            } else {
                throw new Exception(implode(", ", $messages));
            }

        } catch (Exception $e) {
            $conn->rollback();
            error_log("Transaction error: " . $e->getMessage());
            header('Content-Type: application/json');
            echo json_encode([
                "success" => false, 
                "message" => "An error occurred: " . $e->getMessage()
            ]);
        }

        $conn->close();
    } else {
        header('Content-Type: application/json');
        echo json_encode([
            "success" => false, 
            "message" => "Invalid data received. Required fields: userId, cartItems, expirationTime, itemName"
        ]);
    }
}




