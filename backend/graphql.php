<?php
require 'vendor/autoload.php';

use GraphQL\GraphQL;
use GraphQL\Type\Definition\Type;
use GraphQL\Type\Definition\ObjectType;
use GraphQL\Type\Schema;

// Database connection
// $conn = new mysqli("127.0.0.1", "root", "yourpassword", "new_scandiweb");
$conn = new mysqli("fdb1029.awardspace.net", "4572775_scandiweb", "Martinelli11", "4572775_scandiweb");

if ($conn->connect_error) {
    die("Database connection failed: " . $conn->connect_error);
}

// Define the GraphQL mutation for placing an order
$placeOrderMutation = new ObjectType([
    'name' => 'Mutation',
    'fields' => [
        'placeOrder' => [
            'type' => new ObjectType([
                'name' => 'Order',
                'fields' => [
                    'id' => Type::string(),
                    'productIds' => Type::listOf(Type::string()),
                    'total' => Type::float(),
                    'currency' => Type::string(),
                    'status' => Type::string(),
                ],
            ]),
            'args' => [
                'productIds' => Type::nonNull(Type::listOf(Type::string())),
                'total' => Type::nonNull(Type::float()),
                'currency' => Type::nonNull(Type::string()),
            ],
            'resolve' => function ($root, $args) use ($conn) {
                $orderId = uniqid(); // Generate a unique order ID
                $productIds = $args['productIds'];
                $total = $args['total'];
                $currency = $args['currency'];
                $status = 'Pending';

                // Validate product IDs
                foreach ($productIds as $productId) {
                    $productCheckQuery = "SELECT COUNT(*) AS count FROM products WHERE id = '$productId'";
                    $productCheckResult = $conn->query($productCheckQuery);
                    $row = $productCheckResult->fetch_assoc();
                    if ($row['count'] == 0) {
                        throw new Exception("Invalid product ID: $productId");
                    }
                }

                // Insert the order into the orders table
                $insertOrderQuery = "INSERT INTO orders (id, total, currency, status, created_at) VALUES ('$orderId', $total, '$currency', '$status', NOW())";
                if (!$conn->query($insertOrderQuery)) {
                    throw new Exception("Error inserting order: " . $conn->error);
                }

                // Insert order-product relationships
                foreach ($productIds as $productId) {
                    $insertOrderProductQuery = "INSERT INTO order_products (order_id, product_id) VALUES ('$orderId', '$productId')";
                    if (!$conn->query($insertOrderProductQuery)) {
                        throw new Exception("Error inserting product for order: " . $conn->error);
                    }
                }

                // Return the order details
                return [
                    'id' => $orderId,
                    'productIds' => $productIds,
                    'total' => $total,
                    'currency' => $currency,
                    'status' => $status,
                ];
            },
        ],
    ],
]);

// Define the GraphQL schema
$schema = new Schema([
    'mutation' => $placeOrderMutation,
]);

// Process incoming GraphQL requests
try {
    $rawInput = file_get_contents('php://input');
    $input = json_decode($rawInput, true);
    $query = $input['query'];
    $variableValues = isset($input['variables']) ? $input['variables'] : null;

    $result = GraphQL::executeQuery($schema, $query, null, null, $variableValues);
    $output = $result->toArray();

    // Log the query and response for debugging
    file_put_contents('logs.txt', "Query: " . $query . "\nResponse: " . json_encode($output) . "\n", FILE_APPEND);

    header('Content-Type: application/json');
    echo json_encode($output);
} catch (Exception $e) {
    // Log the error for debugging
    file_put_contents('logs.txt', "Error: " . $e->getMessage() . "\n", FILE_APPEND);

    header('Content-Type: application/json');
    echo json_encode(['errors' => [['message' => $e->getMessage()]]]);
}

$conn->close();
?>
