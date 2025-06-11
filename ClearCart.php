<?php
$servername = "localhost"; 
$username = "root";
$password = "";
$dbname = "bandwebsitedatabase2"; 

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    
    $requestData = json_decode(file_get_contents("php://input"), true);

    if (isset($requestData['userId'])) {
        $conn = new mysqli($servername, $username, $password, $dbname);

        if ($conn->connect_error) {
            error_log("Connection failed: " . $conn->connect_error);
            die(json_encode(["success" => false, "message" => "Connection failed: " . $conn->connect_error]));
        }
       
        $conn->begin_transaction();
        $userId = $requestData['userId'];
        
        try {
            $success = true;
            $messages = [];
            $sql = "DELETE FROM cart WHERE User_ID = ?";
            
                $stmt = $conn->prepare($sql);  
                if (!$stmt) {
                    throw new Exception($conn->error);
                }
              
                $stmt->bind_param("s", 
                    $userId,
                ); 
                
                $result = $stmt->execute();
                if (!$result) {
                    $success = false;
                    $messages[] = "Failed to clear cart: " . $itemName;
                }
                
                $stmt->close();
            
            
            if ($success) {
                $conn->commit();
                header('Content-Type: application/json');
                echo json_encode([
                    "success" => true,
                    "message" => "Cart items cleared successfully",
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
            "message" => "Invalid data received. Required fields: userId"
        ]);
    }
}


