<?php
// Enable error reporting for debugging
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Allow CORS and set JSON headers
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");
header('Content-Type: application/json');

// Handle OPTIONS (Preflight) Requests
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

// Database connection
$host = "sql7.freesqldatabase.com";
$username = "sql7752412";
$password = "Jc8bE4w1z9";
$database = "sql7752412";

$conn = new mysqli($host, $username, $password, $database);

// Check connection
if ($conn->connect_error) {
    echo json_encode(["error" => "Database connection failed: " . $conn->connect_error]);
    exit;
}

// Initialize empty category filter
$category = null;

// Handle POST Requests to Filter Products
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $input = json_decode(file_get_contents("php://input"), true);

    // Check if the input JSON has a 'category' key
    if (isset($input['category'])) {
        $category = $conn->real_escape_string($input['category']);
    } else {
        echo json_encode(["error" => "Invalid input. Please provide a 'category' field."]);
        exit;
    }
}

// Build SQL Query
$query = "SELECT * FROM products";
if ($category) {
    $query .= " WHERE category = '$category'";
}

// Execute Query
$result = $conn->query($query);
if (!$result) {
    echo json_encode(["error" => "Query failed: " . $conn->error]);
    exit;
}

// Fetch products and their related data
$products = [];
while ($row = $result->fetch_assoc()) {
    $product_id = $row['id'];

    // Fetch photos
    $photos = [];
    $photo_result = $conn->query("SELECT photo_url FROM photos WHERE product_id = '$product_id'");
    while ($photo_row = $photo_result->fetch_assoc()) {
        $photos[] = $photo_row['photo_url'];
    }
    $row['gallery'] = $photos;

    // Fetch attributes
    $attributes = [];
    $attribute_result = $conn->query("SELECT attribute_name, attribute_value FROM product_attributes WHERE product_id = '$product_id'");
    while ($attribute_row = $attribute_result->fetch_assoc()) {
        $attributes[] = [
            "name" => $attribute_row['attribute_name'],
            "value" => $attribute_row['attribute_value']
        ];
    }
    $row['attributes'] = $attributes;

    $products[] = $row;
}

$conn->close();

// Return JSON response
echo json_encode($products);
exit;
?>
