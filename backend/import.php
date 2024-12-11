<?php
// Database connection
$conn = new mysqli("127.0.0.1", "root", "yourpassword", "new_scandiweb");

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Load JSON data
$jsonData = file_get_contents("data.json");
$data = json_decode($jsonData, true);

// Insert products and their photos
foreach ($data['data']['products'] as $product) {
    // Insert product
    $stmt = $conn->prepare("INSERT INTO products (id, name, category, in_stock, price, currency_symbol, description) VALUES (?, ?, ?, ?, ?, ?, ?)");
    $price = $product['prices'][0]['amount'];
    $currency = $product['prices'][0]['currency']['symbol'];
    $stmt->bind_param(
        "sssdsds",
        $product['id'],
        $product['name'],
        $product['category'],
        $product['inStock'],
        $price,
        $currency,
        $product['description']
    );
    $stmt->execute();

    // Insert photos
    foreach ($product['gallery'] as $photo_url) {
        $photo_stmt = $conn->prepare("INSERT INTO photos (product_id, photo_url) VALUES (?, ?)");
        $photo_stmt->bind_param("ss", $product['id'], $photo_url);
        $photo_stmt->execute();
    }
}

echo "Data imported successfully!";
$conn->close();
?>
