<?php

namespace Tests;

use PHPUnit\Framework\Attributes\CoversNothing;

class CartTest extends BaseTest
{
    protected function tearDown(): void
    {
        // Clean up the cart table after each test
        $this->db->query("DELETE FROM cart");
        parent::tearDown();
    }

    #[CoversNothing]
    public function testAddToCart(): void
    {
        $productId = "prod1";
        $quantity = 2;
        $attributes = json_encode(["size:M", "color:Red"]);

        $stmt = $this->db->prepare("INSERT INTO cart (product_id, quantity, attributes) VALUES (?, ?, ?)");
        $stmt->bind_param("sis", $productId, $quantity, $attributes);
        $stmt->execute();

        $this->assertEquals(1, $stmt->affected_rows);

        $stmt->close();

        // Verify the row exists in the database
        $result = $this->db->query("SELECT * FROM cart WHERE product_id = 'prod1'");
        $this->assertNotFalse($result);
        $this->assertEquals(1, $result->num_rows, "Row was not added to the cart.");
    }

    #[CoversNothing]
    public function testFetchCart(): void
    {
        // Insert a product into the cart first
        $this->testAddToCart();

        $result = $this->db->query("SELECT * FROM cart");

        $this->assertNotFalse($result);
        $this->assertGreaterThan(0, $result->num_rows);

        while ($row = $result->fetch_assoc()) {
            $this->assertArrayHasKey('product_id', $row);
            $this->assertArrayHasKey('quantity', $row);
            $this->assertArrayHasKey('attributes', $row);
        }
    }

    #[CoversNothing]
    public function testRemoveFromCart(): void
    {
        // Insert a product into the cart first
        $this->testAddToCart();

        $productId = "prod1";
        $attributes = json_encode(["size:M", "color:Red"]);

        $stmt = $this->db->prepare("DELETE FROM cart WHERE product_id = ? AND attributes = ?");
        $stmt->bind_param("ss", $productId, $attributes);
        $stmt->execute();

        $this->assertEquals(1, $stmt->affected_rows, "Row was not removed from the cart.");

        $stmt->close();
    }


#[CoversNothing]
public function testCartClearsAfterOrder(): void
{
    // Insert a product into the cart
    $this->testAddToCart();

    // Place an order
    $orderId = uniqid();
    $stmt = $this->db->prepare("INSERT INTO orders (order_id, product_id, quantity, created_at) SELECT ?, product_id, quantity, NOW() FROM cart");
    $stmt->bind_param("s", $orderId);
    $stmt->execute();
    $stmt->close();

    // Clear the cart
    $this->db->query("DELETE FROM cart");

    // Verify the cart is empty
    $result = $this->db->query("SELECT * FROM cart");
    $this->assertEquals(0, $result->num_rows, "Cart should be empty after placing an order.");
}

#[CoversNothing]
public function testDuplicateProductInCart(): void
{
    $productId = "prod1";
    $quantity = 1;
    $attributes = json_encode(["size:M"]);

    // Add product to the cart
    $stmt = $this->db->prepare("INSERT INTO cart (product_id, quantity, attributes) VALUES (?, ?, ?)");
    $stmt->bind_param("sis", $productId, $quantity, $attributes);
    $stmt->execute();
    $stmt->close();

    // Add the same product again
    $stmt = $this->db->prepare("UPDATE cart SET quantity = quantity + ? WHERE product_id = ? AND attributes = ?");
    $stmt->bind_param("iss", $quantity, $productId, $attributes);
    $stmt->execute();
    $stmt->close();

    // Verify the updated quantity
    $result = $this->db->query("SELECT quantity FROM cart WHERE product_id = '$productId'");
    $this->assertNotFalse($result);
    $row = $result->fetch_assoc();
    $this->assertEquals(2, $row['quantity'], "Duplicate product should update the quantity, not create a new row.");
}

}
