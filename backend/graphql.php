<?php
require_once __DIR__ . '/vendor/autoload.php';
use GraphQL\GraphQL;

header('Content-Type: application/json');
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");

$schema = require_once __DIR__ . '/graphql/schema.php';

$rawInput = file_get_contents('php://input');
$input = json_decode($rawInput, true);
$query = $input['query'] ?? null;

if (!$query) {
    echo json_encode(['error' => 'No query provided']);
    exit;
}

try {
    $result = GraphQL::executeQuery($schema, $query);
    $output = $result->toArray();
} catch (\Exception $e) {
    $output = [
        'errors' => [
            ['message' => $e->getMessage()]
        ]
    ];
}

echo json_encode($output);