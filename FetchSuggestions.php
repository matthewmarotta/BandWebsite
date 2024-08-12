<?php
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "bandwebsitedatabase";

if (isset($_GET['query'])) {
    $query = "%" . $_GET['query'] . "%";

    $conn = new mysqli($servername, $username, $password, $dbname);

    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }

    $sql = "SELECT DISTINCT Name FROM items WHERE Name LIKE ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $query);
    $stmt->execute();
    $result = $stmt->get_result();

    $suggestions = array();

    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $suggestions[] = $row['Name'];
        }
    }

    echo json_encode($suggestions);

    $stmt->close();
    $conn->close();
} else {
    echo json_encode([]);
}


