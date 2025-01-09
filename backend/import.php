<?php
// Database connection
$conn = new mysqli("fdb1029.awardspace.net", "4572775_scandiweb", "Martinelli11", "4572775_scandiweb");

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Load JSON data
$jsonData = file_get_contents("http://tormanovi.com/scandiweb/backend/data.json");
$data = json_decode($jsonData, true);

// Insert products and their attributes
foreach ($data['data']['products'] as $product) {
    // Check if the product already exists
    $productCheckStmt = $conn->prepare("SELECT id FROM products WHERE id = ?");
    $productCheckStmt->bind_param("s", $product['id']);
    $productCheckStmt->execute();
    $productCheckStmt->store_result();

    if ($productCheckStmt->num_rows === 0) {
        // Insert product if it doesn't exist
        $stmt = $conn->prepare("INSERT INTO products (id, name, category, in_stock, price, currency_symbol, description) VALUES (?, ?, ?, ?, ?, ?, ?)");
        $price = $product['prices'][0]['amount'];
        $currency = $product['prices'][0]['currency']['symbol'];
        $in_stock = $product['inStock'] ? 1 : 0; // Convert to boolean (1 or 0)
        $description = strip_tags($product['description']); // Strip HTML tags for database storage
        $stmt->bind_param(
            "sssidss",
            $product['id'],
            $product['name'],
            $product['category'],
            $in_stock,
            $price,
            $currency,
            $description
        );
        $stmt->execute();
        $stmt->close();
    }
    $productCheckStmt->close();

    // Insert attributes
    foreach ($product['attributes'] as $attribute) {
        foreach ($attribute['items'] as $item) {
            // Check if the attribute already exists
            $attrCheckStmt = $conn->prepare("SELECT id FROM product_attributes WHERE product_id = ? AND attribute_name = ? AND attribute_value = ?");
            $attrCheckStmt->bind_param("sss", $product['id'], $attribute['name'], $item['value']);
            $attrCheckStmt->execute();
            $attrCheckStmt->store_result();

            if ($attrCheckStmt->num_rows === 0) {
                // Insert attribute if it doesn't exist
                $attrStmt = $conn->prepare("INSERT INTO product_attributes (product_id, attribute_name, attribute_value) VALUES (?, ?, ?)");
                $attrStmt->bind_param("sss", $product['id'], $attribute['name'], $item['value']);
                $attrStmt->execute();
                $attrStmt->close();
            }
            $attrCheckStmt->close();
        }
    }

    // Insert photos
    foreach ($product['gallery'] as $photo_url) {
        // Check if the photo already exists
        $photoCheckStmt = $conn->prepare("SELECT id FROM photos WHERE product_id = ? AND photo_url = ?");
        $photoCheckStmt->bind_param("ss", $product['id'], $photo_url);
        $photoCheckStmt->execute();
        $photoCheckStmt->store_result();

        if ($photoCheckStmt->num_rows === 0) {
            // Insert photo if it doesn't exist
            $photoStmt = $conn->prepare("INSERT INTO photos (product_id, photo_url) VALUES (?, ?)");
            $photoStmt->bind_param("ss", $product['id'], $photo_url);
            $photoStmt->execute();
            $photoStmt->close();
        }
        $photoCheckStmt->close();
    }
}

echo "Data imported successfully!";
$conn->close();
?>
