<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");

// $apiUrl = 'http://tormanovi.infinityfreeapp.com/api.php'; // Remote API
$localApiUrl = 'http://localhost:8001/api.php'; // Local API

// Function to fetch data from an API
function fetchData($url) {
    // Use file_get_contents for local URLs
    if (strpos($url, 'localhost') !== false) {
        $data = @file_get_contents($url);
        if ($data === false) {
            return ["error" => "Unable to fetch data from $url"];
        }
        return $data;
    }

    // Use cURL for remote URLs
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/91.0.4472.124 Safari/537.36');
    $data = curl_exec($ch);

    if (curl_errno($ch)) {
        return ["error" => curl_error($ch)];
    }

    curl_close($ch);
    return $data;
}

// Decide which API URL to use
$apiUrlToUse = (strpos($_SERVER['HTTP_HOST'], 'localhost') !== false) ? $localApiUrl : $apiUrl;

// Handle POST requests (e.g., for placing orders)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $input = json_decode(file_get_contents('php://input'), true);
    $ch = curl_init($apiUrlToUse);
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

// Fetch data from the API
$data = fetchData($apiUrlToUse);

// Check if the response is valid JSON
$products = json_decode($data, true);
if (!is_array($products)) {
    die("Error: Invalid API response. Check the API at $apiUrlToUse.");
}

// For API clients, return JSON
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
</body>
</html>
