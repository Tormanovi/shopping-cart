<?php
$conn = new mysqli("fdb1029.awardspace.net", "4572775_scandiweb", "Martinelli11", "4572775_scandiweb");

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
} else {
    echo "Connected successfully";
}
?>
