<?php
require_once __DIR__ . '/../vendor/autoload.php';

use GraphQL\Type\Definition\Type;
use GraphQL\Type\Definition\ObjectType;
use GraphQL\Type\Schema;

// Define the Attribute Type
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

// Define the Cart Type
$cartItemType = new ObjectType([
    'name' => 'CartItem',
    'fields' => [
        'productId' => Type::string(),
        'quantity' => Type::int(),
        'attributes' => Type::listOf($attributeType),
        'product' => [
            'type' => $productType,
            'resolve' => function ($cartItem) {
                $conn = new mysqli("127.0.0.1", "root", "yourpassword", "new_scandiweb");

                if ($conn->connect_error) {
                    throw new \Exception("Database connection failed: " . $conn->connect_error);
                }

                $productId = $cartItem['productId'];
                $result = $conn->query("SELECT * FROM products WHERE id = '$productId'");

                $product = $result->fetch_assoc();

                $conn->close();
                return $product;
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
        ],
        'cart' => [
            'type' => Type::listOf($cartItemType),
            'resolve' => function () {
                $conn = new mysqli("127.0.0.1", "root", "yourpassword", "new_scandiweb");

                if ($conn->connect_error) {
                    throw new \Exception("Database connection failed: " . $conn->connect_error);
                }

                $result = $conn->query("SELECT product_id, quantity, attributes FROM cart");
                $cartItems = [];
                while ($row = $result->fetch_assoc()) {
                    $attributes = json_decode($row['attributes'], true);
                    $cartItems[] = [
                        'productId' => $row['product_id'],
                        'quantity' => $row['quantity'],
                        'attributes' => array_map(function ($attribute) {
                            $parts = explode(':', $attribute);
                            return ['name' => $parts[0], 'value' => $parts[1]];
                        }, $attributes)
                    ];
                }

                $conn->close();
                return $cartItems;
            }
        ]
    ]
]);

// Root Mutation
$mutationType = new ObjectType([
    'name' => 'Mutation',
    'fields' => [
        'addToCart' => [
            'type' => Type::boolean(),
            'args' => [
                'productId' => Type::nonNull(Type::string()),
                'quantity' => Type::nonNull(Type::int()),
                'attributes' => Type::listOf(Type::string())
            ],
            'resolve' => function ($rootValue, $args) {
                $conn = new mysqli("127.0.0.1", "root", "yourpassword", "new_scandiweb");

                if ($conn->connect_error) {
                    throw new \Exception("Database connection failed: " . $conn->connect_error);
                }

                $attributesJson = json_encode($args['attributes']);

                $stmt = $conn->prepare("SELECT id FROM cart WHERE product_id = ? AND attributes = ?");
                $stmt->bind_param("ss", $args['productId'], $attributesJson);
                $stmt->execute();
                $result = $stmt->get_result();

                if ($result->num_rows > 0) {
                    $stmt = $conn->prepare("UPDATE cart SET quantity = quantity + ? WHERE product_id = ? AND attributes = ?");
                    $stmt->bind_param("iss", $args['quantity'], $args['productId'], $attributesJson);
                } else {
                    $stmt = $conn->prepare("INSERT INTO cart (product_id, quantity, attributes) VALUES (?, ?, ?)");
                    $stmt->bind_param("sis", $args['productId'], $args['quantity'], $attributesJson);
                }

                if (!$stmt->execute()) {
                    throw new \Exception("Failed to add to cart: " . $stmt->error);
                }

                $stmt->close();
                $conn->close();

                return true;
            }
        ],
        'removeFromCart' => [
            'type' => Type::boolean(),
            'args' => [
                'productId' => Type::nonNull(Type::string()),
                'attributes' => Type::listOf(Type::string())
            ],
            'resolve' => function ($rootValue, $args) {
                $conn = new mysqli("127.0.0.1", "root", "yourpassword", "new_scandiweb");

                if ($conn->connect_error) {
                    throw new \Exception("Database connection failed: " . $conn->connect_error);
                }

                $attributesJson = json_encode($args['attributes']);

                $stmt = $conn->prepare("DELETE FROM cart WHERE product_id = ? AND attributes = ?");
                $stmt->bind_param("ss", $args['productId'], $attributesJson);

                if (!$stmt->execute()) {
                    throw new \Exception("Failed to remove from cart: " . $stmt->error);
                }

                $stmt->close();
                $conn->close();

                return true;
            }
        ],
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
                    $stmt = $conn->prepare("INSERT INTO orders (order_id, product_id, quantity, created_at) SELECT ?, product_id, quantity, NOW() FROM cart WHERE product_id = ?");
                    if (!$stmt) {
                        throw new \Exception("Prepare statement failed: " . $conn->error);
                    }

                    $stmt->bind_param("ss", $orderId, $productId);

                    if (!$stmt->execute()) {
                        throw new \Exception("Execute failed: " . $stmt->error);
                    }

                    $stmt->close();
                }

                // Clear the cart after placing the order
                $conn->query("DELETE FROM cart");

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
