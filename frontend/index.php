<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");

// Fetch data from the API
$apiUrl = 'http://localhost:8001/api.php';
$data = @file_get_contents($apiUrl);
if ($data === false) {
    die("Error: Unable to fetch data from API at $apiUrl.");
}

$products = json_decode($data, true);
if (!is_array($products)) {
    die("Error: Invalid API response.");
}
if (isset($_SERVER['HTTP_ACCEPT']) && strpos($_SERVER['HTTP_ACCEPT'], 'application/json') !== false) {
    echo json_encode(["products" => $products]);
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Product List</title>
</head>
<body>
    <h1>Product List</h1>
    <table border="1">
        <thead>
            <tr>
                <th>ID</th>
                <th>Name</th>
                <th>Category</th>
                <th>Price</th>
                <th>In Stock</th>
                <th>Description</th>
                <th>Photos</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($products as $product): ?>
                <tr>
                    <td><?php echo htmlspecialchars($product['id']); ?></td>
                    <td><?php echo htmlspecialchars($product['name']); ?></td>
                    <td><?php echo htmlspecialchars($product['category']); ?></td>
                    <td><?php echo htmlspecialchars($product['currency_symbol'] . ' ' . $product['price']); ?></td>
                    <td><?php echo $product['in_stock'] ? 'Yes' : 'No'; ?></td>
                    <td><?php echo htmlspecialchars($product['description']); ?></td>
                    <td>
                        <?php foreach ($product['gallery'] as $photo_url): ?>
                            <img src="<?php echo htmlspecialchars($photo_url); ?>" width="100" alt="Product Photo">
                        <?php endforeach; ?>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</body>
</html>
