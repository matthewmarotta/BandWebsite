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
            die("Connection failed: " . $conn->connect_error);
        }


   
        $sql = "UPDATE items SET quantity = quantity - ? WHERE name = ?";
        $stmt = $conn->prepare($sql);


 
        $stmt->bind_param("is", $requestData['quantity'], $requestData['itemName']);
        $stmt->execute();


        if ($stmt->affected_rows > 0) {
            echo json_encode(["success" => true]);
        } else {
            echo json_encode(["success" => false, "message" => "Failed to update quantity"]);
        }


   
        $stmt->close();
        $conn->close();
    } else {
        echo json_encode(["success" => false, "message" => "Invalid data received"]);
    }
} else {
    echo json_encode(["success" => false, "message" => "Invalid request method"]);
}

