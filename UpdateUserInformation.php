<?php
$servername = "localhost"; 
$username = "root";
$password = "";
$dbname = "bandwebsitedatabase2"; 

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    
$requestData = json_decode(file_get_contents("php://input"), true);
    
    if (isset($requestData['userId']) && isset($requestData['firstName']) && isset($requestData['lastName']) && isset($requestData['street']) && isset($requestData['city']) 
    && isset($requestData['state']) && isset($requestData['mobile']) && isset($requestData['email']) ) {
        $conn = new mysqli($servername, $username, $password, $dbname);
        
        if ($conn->connect_error) {
            error_log("Connection failed: " . $conn->connect_error);
            die(json_encode(["success" => false, "message" => "Connection failed: " . $conn->connect_error]));
        }
        
        $conn->begin_transaction();
        
        try {
           
            $sql = "INSERT INTO users (User_ID, First_Name, Last_Name, Street, Suburb, State, Mobile_Number, Email_Address) VALUES (?, ?, ?, ?, ?, ?, ?, ?) 
            ON DUPLICATE KEY UPDATE
            First_Name = VALUES(First_Name),
            Last_Name = VALUES(Last_Name),
            Street = VALUES(Street),
            Suburb = VALUES(Suburb),
            State = VALUES(State),
            Mobile_Number = VALUES(Mobile_Number),
            Email_Address = VALUES(Email_Address);";
            
            $stmt = $conn->prepare($sql);
           
            if (!$stmt) {
                throw new Exception($conn->error);
            }
            
            // Use $requestData['orderId'] instead of $orderId
            $stmt->bind_param("ssssssss", $requestData['userId'], $requestData['firstName'], $requestData['lastName'], $requestData['street'], $requestData['city'], $requestData['state'], $requestData['mobile']
        , $requestData['email']);
            $stmt->execute(); 
            $stmt->close();
            
            // Commit the transaction
            $conn->commit();
            
            // Send success response 
            echo json_encode([
                "success" => true,
                "message" => "User details added successfully",
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