<?php
header('Content-Type: application/json');

// Database connection
$conn = new mysqli("127.0.0.1", "root", "yourpassword", "new_scandiweb");

if ($conn->connect_error) {
    die(json_encode(["error" => $conn->connect_error]));
}

// Fetch products with their photos
$products = [];
$result = $conn->query("SELECT * FROM products");
while ($row = $result->fetch_assoc()) {
    $product_id = $row['id'];
    $photo_result = $conn->query("SELECT photo_url FROM photos WHERE product_id = '$product_id'");
    $photos = [];
    while ($photo_row = $photo_result->fetch_assoc()) {
        $photos[] = $photo_row['photo_url'];
    }
    $row['gallery'] = $photos;
    $products[] = $row;
}

echo json_encode($products);
$conn->close();
?>
