<?php
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "bandwebsitedatabase2";

if (isset($_GET['category'])) {
    $category = $_GET['category'];
    
    $conn = new mysqli($servername, $username, $password, $dbname);

    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }

    $sql = "SELECT Image_URL, Category_ID, Name, Price, Category_Name, Availability 
    FROM items natural join categories
    WHERE Category_ID = ?";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $category);
    $stmt->execute();
    $result = $stmt->get_result();

    $category_data = array();

    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $category_data[] = $row;
        }
    }

    echo json_encode($category_data);

    $stmt->close();
    $conn->close();
} else {
    echo "Category parameter not provided";
}
