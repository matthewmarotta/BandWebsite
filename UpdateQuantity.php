<?php
$servername = "localhost"; 
$username = "root";
$password = "";
$dbname = "bandwebsitedatabase"; 

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $requestData = json_decode(file_get_contents("php://input"), true);

    if (isset($requestData['itemName']) && isset($requestData['quantity'])) {
        $conn = new mysqli($servername, $username, $password, $dbname);

        if ($conn->connect_error) {
            error_log("Connection failed: " . $conn->connect_error);
            die(json_encode(["success" => false, "message" => "Connection failed: " . $conn->connect_error]));
        }

       
        $conn->begin_transaction();

        try {
          
            $sql = "UPDATE items SET Quantity_Available = Quantity_Available - ? WHERE Name = ?";
            $stmt = $conn->prepare($sql);  
            if (!$stmt) {
                throw new Exception($conn->error);
            }
            $stmt->bind_param("is", $requestData['quantity'], $requestData['itemName']); 
            $stmt->execute(); 

            if ($stmt->affected_rows > 0) {
             
                $sql = "SELECT Quantity_Available FROM items WHERE Name = ?";
                $stmt = $conn->prepare($sql);
                if (!$stmt) {
                    throw new Exception($conn->error);
                }
                $stmt->bind_param("s", $requestData['itemName']);
                $stmt->execute();
                $result = $stmt->get_result();
                $row = $result->fetch_assoc();

                if ($row['Quantity_Available'] == 0) {
                   
                    $sql = "UPDATE items SET Availability = 0 WHERE Name = ?";
                    $stmt = $conn->prepare($sql);
                    if (!$stmt) {
                        throw new Exception($conn->error);
                    }
                    $stmt->bind_param("s", $requestData['itemName']);
                    $stmt->execute();
                }

                if ($row['Quantity_Available'] < 0) {
                  $exceededAmount = abs($row['Quantity_Available']);
                  $itemName = $requestData['itemName'];
                  $response = [
                      'success' => false,
                      'message' => "Not enough available stock, remove $exceededAmount of $itemName item from your cart",
                  ];

                  header('Content-Type: application/json');
                  echo json_encode($response);

                    $conn->rollback(); //restores original database value
                    $stmt->close();
                    $conn->close();
                    exit();
                }
    
               
                $conn->commit();

                echo json_encode(["success" => true, "available" => $row['Quantity_Available'] > 0]);
            } else {
               
                $conn->rollback();
                echo json_encode(["success" => false, "message" => "Failed to update quantity"]);
            }
        } catch (Exception $e) {
           
            $conn->rollback();
            error_log("Transaction error: " . $e->getMessage());
            echo json_encode(["success" => false, "message" => "An error occurred: " . $e->getMessage()]);
        }

        $stmt->close();
        $conn->close();
    } else {
        echo json_encode(["success" => false, "message" => "Invalid data received"]);
    }
}







