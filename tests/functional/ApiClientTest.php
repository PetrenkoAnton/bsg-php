<?php
declare(strict_types=1);

namespace BSG\Tests\functional;

use BSG\Clients\ApiClient;
use BSG\Tests\TestConfig;
use PHPUnit\Framework\TestCase;

class ApiClientTest extends TestCase
{
    const ERR_NO = 0;

    private $apiClient;

    public function setUp(): void
    {
        $this->apiClient = new ApiClient(TestConfig::TEST_API_KEY);
    }

    /**
     * @test
     * @group ok
     */
    public function getBalanceTest()
    {
        try {
            $answer = $this->apiClient->getBalance();
            $this->assertArrayHasKey('error', $answer);
            $this->assertArrayHasKey('amount', $answer);
            $this->assertArrayHasKey('currency', $answer);
            $this->assertArrayHasKey('limit', $answer);
            $this->assertEquals(self::ERR_NO, $answer['error']);
        } catch (\Exception $e) {
            $this->fail(TestConfig::EXCEPTION_FAIL . $e->getMessage());
        }
    }
}
