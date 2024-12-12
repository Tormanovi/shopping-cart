<?php

namespace Tests;

use PHPUnit\Framework\Attributes\CoversNothing;
use PHPUnit\Framework\TestCase;
use mysqli;

class BaseTest extends TestCase
{
    protected $db;

    protected function setUp(): void
    {
        $this->db = new mysqli("127.0.0.1", "root", "yourpassword", "new_scandiweb");

        if ($this->db->connect_error) {
            throw new \Exception("Database connection failed: " . $this->db->connect_error);
        }
    }

    protected function tearDown(): void
    {
        if ($this->db) {
            $this->db->close();
        }
    }

    #[CoversNothing]
    public function testDatabaseConnection(): void
    {
        $this->assertTrue($this->db->ping(), "Database connection is not active.");
    }
}
