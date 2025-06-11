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
            echo json_encode(["success" => false, "message" => "Connection failed: " . $conn->connect_error]);
            exit;
        }

        $sql = "SELECT SUM(Price) AS total_price FROM cart WHERE User_ID = ?";
        $stmt = $conn->prepare($sql);

        if (!$stmt) {
            echo json_encode(["success" => false, "message" => $conn->error]);
            exit;
        }

        $stmt->bind_param("s", $requestData['userId']);
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();

        $total = $row["total_price"] ?? 0;

        echo json_encode([
            "success" => true,
            "total" => $total
        ]);

        $stmt->close();
        $conn->close();
    } else {
        echo json_encode([
            "success" => false,
            "message" => "Missing userId"
        ]);
    }
} else {
    echo json_encode([
        "success" => false,
        "message" => "Invalid request method"
    ]);
}
?>



