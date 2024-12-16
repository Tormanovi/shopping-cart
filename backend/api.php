<?php
header('Content-Type: application/json');

// Database connection
$conn = new mysqli("sql7.freesqldatabase.com", "sql7752412", "Jc8bE4w1z9", "sql7752412");

if ($conn->connect_error) {
    die(json_encode(["error" => $conn->connect_error]));
}

// Fetch products with their photos and attributes
$products = [];
$result = $conn->query("SELECT * FROM products");
while ($row = $result->fetch_assoc()) {
    $product_id = $row['id'];

    // Fetch photos
    $photo_result = $conn->query("SELECT photo_url FROM photos WHERE product_id = '$product_id'");
    $photos = [];
    while ($photo_row = $photo_result->fetch_assoc()) {
        $photos[] = $photo_row['photo_url'];
    }
    $row['gallery'] = $photos;

    // Fetch attributes
    $attribute_result = $conn->query("SELECT attribute_name, attribute_value FROM product_attributes WHERE product_id = '$product_id'");
    $attributes = [];
    while ($attribute_row = $attribute_result->fetch_assoc()) {
        $attributes[] = [
            'name' => $attribute_row['attribute_name'],
            'value' => $attribute_row['attribute_value']
        ];
    }
    $row['attributes'] = $attributes;

    $products[] = $row;
}

echo json_encode($products);
$conn->close();
?>
