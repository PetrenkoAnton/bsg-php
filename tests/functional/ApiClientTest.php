<?php
declare(strict_types=1);

namespace BSG\Tests\functional;

use BSG\Clients\ApiClient;
use Exception;

class ApiClientTest extends TestCase
{
    private ApiClient $apiClient;

    public function setUp(): void
    {
        parent::setUp();
        $this->apiClient = new ApiClient($this->testApiKey);
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
        } catch (Exception $e) {
            $this->failed($e);
        }
    }
}
