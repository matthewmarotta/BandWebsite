<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
require __DIR__ . '/vendor/autoload.php';

$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->load();


$servername = "localhost";
$username = "root";
$password = "";
$dbname = "bandwebsitedatabase2";

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

function generateOrderConfirmationEmail($email_data) {
    $html = "
    <!DOCTYPE html>
    <html>
    <head>
        <title>Order Confirmation</title>
    </head>
    <body>
    <div style='max-width:600px; margin: 0 auto;'>
    <h1>Order Confirmation</h1>
    <p>Order ID:</p>
    <p>Thanks for your purchase</p>
    
    <h2>Order Detail</h2>
    <table style='width: 100%; border-collapse: collapse;'>
        <tr>
            <th></th>
            <th>Item</th>
            <th>Quantity</th>
            <th>Price</th>
        </tr> 

        <tr>";

        foreach($email_data as $item) {
         $html .= "<tr>
            <td><img src='" . $item['Image_URL'] . "' alt='Product Image' style='max-width:100px;'></td>
            <td>" . $item['Name'] . "</td>
            <td>" . $item['Order_Quantity'] . "</td> 
           <td>$" . number_format($item['Item_Price'], 2) . "</td>
        </tr>";
         }
       
         $html .= "</table>
    
                <p>Total Amount: $" . number_format($email_data[count($email_data) - 1]['Total_Amount'], 2) . "</p>
    
                <h2>Shipping Information:</h2>
                <p>" . $email_data[count($email_data) - 1]['First_Name'] . " " . $email_data[count($email_data) - 1]['Last_Name'] . "</p>
                <p>" . $email_data[count($email_data) - 1]['Street'] . "</p>
                <p>" . $email_data[count($email_data) - 1]['Suburb'] . ", " . $email_data[count($email_data) - 1]['State'] . "</p>
            </div>
        </body>
        </html>";
    
        return $html;
    }
    
    // Email sending function
    function sendOrderConfirmationEmail($email_data) {
        $mail = new PHPMailer(true);

    try {
        // Server settings
        $mail->isSMTP();
        $mail->Host       = 'smtp.gmail.com';
        $mail->SMTPAuth   = true;
        $mail->Username = $_ENV['MAIL_USERNAME'];
        $mail->Password = $_ENV['MAIL_PASSWORD'];
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port       = 587;

        // Recipients
        $mail->setFrom('noreply@yourdomain.com', 'Your Band Name');
        $mail->addAddress($email_data[count($email_data) - 1]['Email_Address']);

        // Content
        $mail->isHTML(true);
        $mail->CharSet = 'UTF-8';
        $mail->Encoding = 'base64';
        $mail->Subject = 'Order Confirmation';
        $mail->Body    = generateOrderConfirmationEmail($email_data);

        $mail->send();
        return true;
    } catch (Exception $e) {
        error_log('Mailer Error: ' . $mail->ErrorInfo);
        return false;
    }
    }      

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $requestData = json_decode(file_get_contents("php://input"), true);
    error_log("Received data: " . print_r($requestData, true));
    if (isset($requestData['orderId']) && isset($requestData['userId'])) {
        $conn = new mysqli($servername, $username, $password, $dbname);
        
        if ($conn->connect_error) {
            error_log("Connection failed: " . $conn->connect_error);
            die(json_encode(["success" => false, "message" => "Connection failed: " . $conn->connect_error]));
        }
        
        $conn->begin_transaction();
        
        try { 
            $sql = "SELECT 
            t.Order_Quantity, 
            t.Item_Price, 
            i.Name,
            i.Image_URL, 
            u.First_Name, 
            u.Last_Name, 
            u.Street, 
            u.Suburb, 
            u.State, 
            u.Email_Address, 
            u.Mobile_Number, 
            o.Order_Date, 
            o.Total_Amount
            FROM 
            users u
            JOIN 
            orders o ON o.User_ID = u.User_ID
            JOIN 
            orderinformationtable t ON t.Order_ID = o.Order_ID
            JOIN 
            items i ON i.Item_ID = t.Item_ID
            WHERE o.Order_ID = ? AND o.User_ID = ?
            ";
            $stmt = $conn->prepare($sql);
            
            if (!$stmt) {
                throw new Exception($conn->error);
            }
            
            $stmt->bind_param("ss", $requestData['orderId'], $requestData['userId']);
            $stmt->execute();
            // Commit the transaction
            $conn->commit();
            $result = $stmt->get_result();

            $email_data = array();

            if ($result->num_rows > 0) {
                while ($row = $result->fetch_assoc()) {
                    $email_data[] = $row;
                }
                
                // Actually send the email
                $emailSent = sendOrderConfirmationEmail($email_data);
                
                // Return a single combined response
                echo json_encode([
                    "success" => true,
                    "message" => "Order created successfully",
                    "email_sent" => $emailSent,
                    "data" => $email_data
                ]);
            } else {
                echo json_encode([
                    "success" => false,
                    "message" => "No order data found"
                ]);
            }
        } catch (Exception $e) {
            // Rollback the transaction in case of error
            $conn->rollback();
            echo json_encode([
                "success" => false,
                "message" => $e->getMessage()
            ]);
        } finally {
            $stmt->close();
            $conn->close();
        }
} 
}
