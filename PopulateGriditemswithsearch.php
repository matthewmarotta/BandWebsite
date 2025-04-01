<?php
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "bandwebsitedatabase2";

if (isset($_GET['query'])) {
    $query = $_GET['query'];

    $conn = new mysqli($servername, $username, $password, $dbname);

    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }

    $sql = "SELECT i.Image_URL, c.Category_Name, i.Name, i.Price, i.Availability 
    FROM items i inner join categories c
    on i.Category_ID = c.Category_ID
    WHERE Name LIKE ? or Category_Name LIKE ?";


    
    $stmt = $conn->prepare($sql);
    $searchTerm = "%" . $query . "%";
    $stmt->bind_param("ss", $searchTerm, $searchTerm);
    $stmt->execute();
    $result3 = $stmt->get_result();

    $query_data3 = array();

    if ($result3->num_rows > 0) {
        while ($row = $result3->fetch_assoc()) {
            $query_data[] = $row;
        }
    }

    echo json_encode($query_data);

    $stmt3->close();
    $conn->close();
} else {
    echo "Search returned no results";
}
