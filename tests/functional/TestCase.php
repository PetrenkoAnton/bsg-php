<?php
declare(strict_types=1);

namespace BSG\Tests\functional;

use Dotenv\Dotenv;
use Exception;
use PHPUnit\Framework\TestCase as CoreTestCase;

class TestCase extends CoreTestCase
{
    protected const ERR_NO = 0;

    protected string $testApiKey;

    public function setUp(): void
    {
        (new Dotenv(__DIR__ . "/../../"))->load();
        $this->testApiKey = $_ENV["TEST_API_KEY"];
    }

    protected function failed(Exception $e): void
    {
        $this->fail("Request failed. Error: {$e->getMessage()}");
    }
}
