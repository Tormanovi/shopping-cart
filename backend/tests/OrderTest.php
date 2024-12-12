<?php

namespace Tests;

use PHPUnit\Framework\Attributes\CoversNothing;

class OrderTest extends BaseTest
{
    #[CoversNothing]
    public function testPlaceOrder(): void
    {
        $orderId = uniqid();
        $productId = "prod1";
        $quantity = 2;

        $stmt = $this->db->prepare("INSERT INTO orders (order_id, product_id, quantity, created_at) VALUES (?, ?, ?, NOW())");
        $stmt->bind_param("ssi", $orderId, $productId, $quantity);
        $stmt->execute();

        $this->assertEquals(1, $stmt->affected_rows);

        $stmt->close();

        // Verify the order exists
        $result = $this->db->query("SELECT * FROM orders WHERE order_id = '$orderId'");
        $this->assertNotFalse($result);
        $this->assertEquals(1, $result->num_rows);
    }
}
