<?php
require_once 'shared.php';

$servername = "localhost";
$username = "root";
$password = "";
$dbname = "bandwebsitedatabase2";

// Set content type to JSON
header('Content-Type: application/json');

// Suppress PHP errors from being displayed as HTML
error_reporting(0);
ini_set('display_errors', 0);

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    try {
        // Create database connection
        $conn = new mysqli($servername, $username, $password, $dbname);
        
        if ($conn->connect_error) {
            throw new Exception("Database connection failed: " . $conn->connect_error);
        }
        
        $requestData = json_decode(file_get_contents("php://input"), true);
        
        // Fixed typo: usedId -> userId
        if (isset($requestData['total']) && isset($requestData['userId'])) {
            
            $sql = "SELECT SUM(Price) AS total_price FROM cart WHERE User_ID = ?";
            $stmt = $conn->prepare($sql);
            
            if (!$stmt) {
                throw new Exception("Database prepare failed: " . $conn->error);
            }
            
            $stmt->bind_param("s", $requestData['userId']);
            $stmt->execute();
            $result = $stmt->get_result();
            $row = $result->fetch_assoc();
            
            $totalFromServer = $row["total_price"] ?? 0;
            
            // Check if totals match
            if ($requestData['total'] == $totalFromServer) {
                try {
                    $total = $requestData['total'];
                    $paymentIntent = $stripe->paymentIntents->create([
                        'automatic_payment_methods' => ['enabled' => true],
                        'amount' => $total*100,
                        'currency' => 'aud',
                    ]);
                    
                    // Return the client secret and other data as JSON
                    echo json_encode([
                        "success" => true,
                        "client_secret" => $paymentIntent->client_secret,
                        "payment_intent_id" => $paymentIntent->id,
                        "amount" => $total
                    ]);
                    
                } catch (\Stripe\Exception\ApiErrorException $e) {
                    http_response_code(400);
                    error_log($e->getError()->message);
                    echo json_encode([
                        "success" => false,
                        "message" => "Failed to create PaymentIntent: " . $e->getError()->message
                    ]);
                }
            } else {
                echo json_encode([
                    "success" => false,
                    "message" => "Total doesn't match database. Client: " . $requestData['total'] . ", Server: " . $totalFromServer
                ]);
            }
            
        } else {
            echo json_encode([
                "success" => false,
                "message" => "Missing required parameters: total and userId"
            ]);
        }
        
    } catch (Exception $e) {
        error_log("Payment intent error: " . $e->getMessage());
        http_response_code(500);
        echo json_encode([
            "success" => false,
            "message" => "Server error: " . $e->getMessage()
        ]);
    } finally {
        if (isset($conn)) {
            $conn->close();
        }
    }
    
} else {
    echo json_encode([
        "success" => false,
        "message" => "Invalid request method"
    ]);
}
