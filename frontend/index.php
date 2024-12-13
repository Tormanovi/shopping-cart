<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");

$apiUrl = 'http://localhost:8001/api.php';

// Handle POST requests (e.g., for placing orders)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $input = json_decode(file_get_contents('php://input'), true);
    $ch = curl_init('http://localhost:8001/graphql.php'); // Send to backend
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($input));
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Content-Type: application/json',
    ]);
    $response = curl_exec($ch);
    curl_close($ch);

    echo $response;
    exit;
}

// Handle GET requests (e.g., fetch products)
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
    <style>
        table {
            width: 100%;
            border-collapse: collapse;
        }
        th, td {
            border: 1px solid #ddd;
            padding: 8px;
            text-align: left;
        }
        th {
            background-color: #f2f2f2;
        }
        img {
            max-width: 100px;
        }
        ul {
            padding: 0;
            list-style-type: none;
        }
    </style>
</head>
<body>
    <h1>Product List</h1>
    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>Name</th>
                <th>Category</th>
                <th>Price</th>
                <th>In Stock</th>
                <th>Description</th>
                <th>Photos</th>
                <th>Attributes</th>
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
                            <img src="<?php echo htmlspecialchars($photo_url); ?>" alt="Product Photo">
                        <?php endforeach; ?>
                    </td>
                    <td>
                        <ul>
                            <?php foreach ($product['attributes'] as $attribute): ?>
                                <li><?php echo htmlspecialchars($attribute['name'] . ': ' . $attribute['value']); ?></li>
                            <?php endforeach; ?>
                        </ul>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>

    <h2>Test Order Placement</h2>
    <form method="POST">
        <label for="productIds">Product IDs (comma-separated):</label><br>
        <input type="text" id="productIds" name="productIds" required><br><br>
        
        <label for="total">Total Amount:</label><br>
        <input type="number" step="0.01" id="total" name="total" required><br><br>
        
        <label for="currency">Currency:</label><br>
        <input type="text" id="currency" name="currency" value="$" required><br><br>
        
        <button type="submit">Place Order</button>
    </form>
</body>
</html>
