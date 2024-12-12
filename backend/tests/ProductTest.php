<?php

namespace Tests;

use PHPUnit\Framework\Attributes\CoversNothing;

class ProductTest extends BaseTest
{
    #[CoversNothing]
    public function testFetchProducts(): void
    {
        $query = "SELECT * FROM products";
        $result = $this->db->query($query);

        $this->assertNotFalse($result);
        $this->assertGreaterThan(0, $result->num_rows);

        while ($row = $result->fetch_assoc()) {
            $this->assertArrayHasKey('id', $row);
            $this->assertArrayHasKey('name', $row);
            $this->assertArrayHasKey('category', $row);
        }
    }

    #[CoversNothing]
public function testFetchProductsWithAttributes(): void
{
    $result = $this->db->query("SELECT p.id, p.name, pa.attribute_name, pa.attribute_value
                                 FROM products p
                                 LEFT JOIN product_attributes pa ON p.id = pa.product_id");

    $this->assertNotFalse($result);
    $this->assertGreaterThan(0, $result->num_rows, "No products found with attributes.");

    while ($row = $result->fetch_assoc()) {
        $this->assertArrayHasKey('id', $row);
        $this->assertArrayHasKey('name', $row);
        $this->assertArrayHasKey('attribute_name', $row);
        $this->assertArrayHasKey('attribute_value', $row);
    }
}

}
