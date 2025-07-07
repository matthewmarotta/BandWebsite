<?php
$servername = "localhost"; 
$username = "root";
$password = "";
$dbname = "bandwebsitedatabase2"; 

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    
    $requestData = json_decode(file_get_contents("php://input"), true);

    if (isset($requestData['id']) && isset($requestData['status']) && isset($requestData['amount']) 
    && isset($requestData['payment_method']) && isset($requestData['currency']) 
    && isset($requestData['orderId']) && isset($requestData['date'])) {
        $conn = new mysqli($servername, $username, $password, $dbname);

        if ($conn->connect_error) {
            error_log("Connection failed: " . $conn->connect_error);
            die(json_encode(["success" => false, "message" => "Connection failed: " . $conn->connect_error]));
        }
       
        $conn->begin_transaction();
        
        try {
            $success = true;
            $messages = [];

            $id = $requestData['id'];
            $orderId = $requestData['orderId'];
            $date = $requestData['date'];
            $payment_method = $requestData['payment_method'];
            $status = $requestData['status'];
            $currency = $requestData['currency'];
            $amount = $requestData['amount']; 
               
                $sql = "INSERT IGNORE INTO payments (Payment_ID, Order_ID, Payment_Date, Payment_Method, Payment_Status, Currency, Total_Amount) VALUES (?, ?, ?, ?, ?, ?, ?)";
                
                $stmt = $conn->prepare($sql);  
                if (!$stmt) {
                    throw new Exception($conn->error);
                }
              
              
                $stmt->bind_param("ssssssi", 
                    $id,
                    $orderId, 
                    $date, 
                    $payment_method, 
                    $status,
                    $currency,
                    $amount
                ); 
                
                $result = $stmt->execute();
                if (!$result) {
                    $success = false;
                    $messages[] = "Failed to send payment info: ";
                }
                
                $stmt->close();
           // }
            
            if ($success) {
                $conn->commit();
                header('Content-Type: application/json');
                echo json_encode([
                    "success" => true,
                    "message" => "Payment info saved successfully"
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
            "message" => "Invalid data received. Required fields: Payment_ID, Order_ID, Payment_Date, Payment_Method, Payment_Status, Currency, Total_Amount"
        ]);
    }
}




