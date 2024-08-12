<?php
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "bandwebsitedatabase";

if (isset($_GET['query'])) {
    $query = $_GET['query'];

    $conn = new mysqli($servername, $username, $password, $dbname);

    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }

    $sql3 = "SELECT i.Image_URL, c.Category_Name, i.Name, i.Price, i.Availability 
    FROM items i inner join categories c
    on i.Category_ID = c.Category_ID
    WHERE Name LIKE ? or Category_Name LIKE ?";


    
    $stmt3 = $conn->prepare($sql3);
    $searchTerm = "%" . $query . "%";
    $stmt3->bind_param("ss", $searchTerm, $searchTerm);
    $stmt3->execute();
    $result3 = $stmt3->get_result();

    $query_data3 = array();

    if ($result3->num_rows > 0) {
        while ($row3 = $result3->fetch_assoc()) {
            $query_data3[] = $row3;
        }
    }

    echo json_encode($query_data3);

    $stmt3->close();
    $conn->close();
} else {
    echo "Search returned no results";
}
