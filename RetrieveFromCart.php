<?php
$servername = "localhost"; 
$username = "root";
$password = "";
$dbname = "bandwebsitedatabase2";

header('Content-Type: application/json');


if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $requestData = json_decode(file_get_contents("php://input"), true);

    if (isset($requestData['userId'])) {
        $conn = new mysqli($servername, $username, $password, $dbname);

        if ($conn->connect_error) {
            die(json_encode(["success" => false, "message" => "Connection failed: " . $conn->connect_error]));
        }

        $sql = "SELECT Name, Image_Url, Price, Quantity, Timestamp_Expiration FROM cart WHERE User_ID = ?";
        $stmt = $conn->prepare($sql);
        if (!$stmt) {
            die(json_encode(["success" => false, "message" => $conn->error]));
        }

        $stmt->bind_param("s", $requestData['userId']);
        $stmt->execute();

        $result = $stmt->get_result();
        $cartItems = $result->fetch_all(MYSQLI_ASSOC);

        echo json_encode([
            "success" => true,
            "cartItems" => $cartItems
        ]);

        $stmt->close();
        $conn->close();
    } else {
        echo json_encode([
            "success" => false,
            "message" => "Missing userId"
        ]);
    }
}
?>


