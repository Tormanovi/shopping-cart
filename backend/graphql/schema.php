<?php
require_once __DIR__ . '/../vendor/autoload.php';

use GraphQL\Type\Definition\Type;
use GraphQL\Type\Definition\ObjectType;
use GraphQL\Type\Schema;

$attributeType = new ObjectType([
    'name' => 'Attribute',
    'fields' => [
        'name' => Type::string(),
        'value' => Type::string(),
    ]
]);

// Define the Product Type
$productType = new ObjectType([
    'name' => 'Product',
    'fields' => [
        'id' => Type::string(),
        'name' => Type::string(),
        'category' => Type::string(),
        'inStock' => Type::boolean(),
        'price' => Type::float(),
        'currency_symbol' => Type::string(),
        'description' => Type::string(),
        'gallery' => Type::listOf(Type::string()),
        'attributes' => [
            'type' => Type::listOf($attributeType),
            'resolve' => function ($product) {
                $conn = new mysqli("127.0.0.1", "root", "yourpassword", "new_scandiweb");

                if ($conn->connect_error) {
                    throw new \Exception("Database connection failed: " . $conn->connect_error);
                }

                $product_id = $product['id'];
                $result = $conn->query("SELECT attribute_name, attribute_value FROM product_attributes WHERE product_id = '$product_id'");

                $attributes = [];
                while ($row = $result->fetch_assoc()) {
                    $attributes[] = [
                        'name' => $row['attribute_name'],
                        'value' => $row['attribute_value']
                    ];
                }

                $conn->close();
                return $attributes;
            }
        ]
    ]
]);


// Define the Order Type
$orderType = new ObjectType([
    'name' => 'Order',
    'fields' => [
        'id' => Type::nonNull(Type::id()),
        'productIds' => Type::listOf(Type::string()),
        'total' => Type::float(),
        'currency' => Type::string(),
        'status' => Type::string()
    ]
]);

// Root Query
$queryType = new ObjectType([
    'name' => 'Query',
    'fields' => [
        'products' => [
            'type' => Type::listOf($productType),
            'resolve' => function () {
                $conn = new mysqli("127.0.0.1", "root", "yourpassword", "new_scandiweb");

                if ($conn->connect_error) {
                    throw new \Exception("Database connection failed: " . $conn->connect_error);
                }

                $result = $conn->query("SELECT * FROM products");
                $products = [];
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

                $conn->close();
                return $products;
            }
        ]
    ]
]);

// Root Mutation
$mutationType = new ObjectType([
    'name' => 'Mutation',
    'fields' => [
        'placeOrder' => [
            'type' => $orderType,
            'args' => [
                'productIds' => Type::nonNull(Type::listOf(Type::string())),
                'total' => Type::nonNull(Type::float()),
                'currency' => Type::nonNull(Type::string())
            ],
            'resolve' => function ($rootValue, $args) {
                $conn = new mysqli("127.0.0.1", "root", "yourpassword", "new_scandiweb");
            
                if ($conn->connect_error) {
                    throw new \Exception("Database connection failed: " . $conn->connect_error);
                }
            
                $orderId = uniqid(); // Generate a unique order ID
                $status = 'Pending';
            
                foreach ($args['productIds'] as $productId) {
                    $stmt = $conn->prepare("INSERT INTO orders (order_id, product_id, quantity, created_at) VALUES (?, ?, ?, NOW())");
                    if (!$stmt) {
                        throw new \Exception("Prepare statement failed: " . $conn->error);
                    }
            
                    $quantity = 1; // Assuming quantity is 1 for now
                    $stmt->bind_param("ssi", $orderId, $productId, $quantity);
            
                    if (!$stmt->execute()) {
                        throw new \Exception("Execute failed: " . $stmt->error);
                    }
            
                    $stmt->close();
                }
            
                $conn->close();
            
                return [
                    'id' => $orderId,
                    'productIds' => $args['productIds'],
                    'total' => $args['total'],
                    'currency' => $args['currency'],
                    'status' => $status
                ];
            }
            
        ]
    ]
]);

// Return the schema
return new Schema([
    'query' => $queryType,
    'mutation' => $mutationType
]);

