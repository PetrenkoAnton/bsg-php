<?php
declare(strict_types=1);

namespace BSG\Tests\functional;

use BSG\Clients\SmsApiClient;
use BSG\Tests\TestConfig;
use Exception;

class SmsApiClientTest extends TestCase
{
    const ERR_WRONG_TARIFF = 6;
    const ERR_SMS_NOT_FOUND = 20;
    const ERR_WRONG_PHONE_NUM = 21;
    const ERR_ABSENT_EXT_ID = 22;
    const ERR_EXT_ALREADY_EXIST = 23;
    const ERR_WRONG_PAYLOAD = 24;
    const ERR_WRONG_SENDER = 25;
    const ERR_WRONG_BODY = 26;
    const ERR_WRONG_EXTERNAL_ID = 27;
    const ERR_WRONG_LIFETIME = 28;
    const ERR_WRONG_TASK_ID = 29;
    const ERR_TASK_NOT_FOUND = 30;
    const ERR_PHONE_ALREADY_IN_USE = 31;

    private $smsClient;

    public function setUp(): void
    {
        parent::setUp();
        $this->smsClient = new SmsApiClient($this->testApiKey, TestConfig::SMS_SENDER_NAME);
    }

    /**
     * @test
     * @group ok
     */
    public function SmsNotFoundTest()
    {
        $answer = $this->smsClient->getStatusById((string)99999999999);
        $this->assertEquals(self::ERR_SMS_NOT_FOUND, $answer['error']);
    }

    /**
     * @test
     * @group failed
     */
    public function sendSuccessSmsTest()
    {
        try {
            $answer = $this->smsClient->sendSms(TestConfig::TEST_PHONE_1, 'test', 'successSend' . (string)time());
            $this->assertArrayHasKey('result', $answer);
            $this->assertArrayHasKey('price', $answer['result']);
            $this->assertArrayHasKey('currency', $answer['result']);
            $this->assertEquals(self::ERR_NO, $answer['result']['error']);
        } catch (Exception $e) {
            $this->failed($e);
        }
    }

    /**
     * @test
     * @group failed
     */
    public function sendSuccessSmsStatusTest()
    {
        try {
            $answer = $this->smsClient->sendSms(TestConfig::TEST_PHONE_1, 'test', 'successSend' . (string)time());
            sleep(5); //wait for creating sms
            $answer = $this->smsClient->getStatusById((string)$answer['result']['id']);
            $this->assertEquals(self::ERR_NO, $answer['error']);
        } catch (Exception $e) {
            $this->failed($e);
        }
    }

    /**
     * @test
     * @group failed
     */
    public function sendSuccessSmsMultiTest()
    {
        try {
            $answer = $this->smsClient->sendSmsMulti([
                array(
                    'msisdn' => '333333',
                    'body' => 'Новый статус заказа NSC-29: Принят, ожидается оплата',
                    'reference' => 'successSendModul43',
                    'originator' => 'testsms',
                )
            ]);
            $this->assertArrayHasKey('result', $answer);
            $this->assertArrayHasKey('total_price', $answer);
            $this->assertArrayHasKey('currency', $answer);
            $this->assertEquals(self::ERR_NO, $answer['result'][0]['error']);
            $this->assertEquals(self::ERR_NO, $answer['result'][1]['error']);
        } catch (Exception $e) {
            $this->failed($e);
        }
    }

    /**
     * @test
     * @group failed
     */
    public function sendInvalidPhoneSmsTest()
    {
        try {
            $answer = $this->smsClient->sendSms('invalidPhone', 'test', 'failed' . (string)time());
            $this->assertArrayHasKey('result', $answer);
            $this->assertArrayNotHasKey('price', $answer['result']);
            $this->assertArrayNotHasKey('currency', $answer['result']);
            $this->assertEquals(self::ERR_WRONG_PHONE_NUM, $answer['result']['error']);
        } catch (Exception $e) {
            $this->failed($e);
        }
    }

    /**
     * @test
     * @group failed
     */
    public function sendNoExternalSmsTest()
    {
        try {
            $answer = $this->smsClient->sendSms(TestConfig::TEST_PHONE_1, 'test');
            $this->assertArrayHasKey('result', $answer);
            $this->assertArrayNotHasKey('price', $answer['result']);
            $this->assertArrayNotHasKey('currency', $answer['result']);
            $this->assertEquals(self::ERR_ABSENT_EXT_ID, $answer['result']['error']);
        } catch (Exception $e) {
            $this->failed($e);
        }
    }

    /**
     * @test
     * @group failed
     */
    public function sendAlreadyExistExtSmsTest()
    {
        try {
            $this->smsClient->sendSms(TestConfig::TEST_PHONE_1, 'test', 'existedExt'); //set ext_id if it isn't exist yet
            $answer = $this->smsClient->sendSms(TestConfig::TEST_PHONE_1, 'test', 'existedExt');
            $this->assertArrayHasKey('result', $answer);
            $this->assertArrayNotHasKey('price', $answer['result']);
            $this->assertArrayNotHasKey('currency', $answer['result']);
            $this->assertEquals(self::ERR_EXT_ALREADY_EXIST, $answer['result']['error']);
        } catch (Exception $e) {
            $this->failed($e);
        }
    }

    /**
     * @test
     * @group failed
     */
    public function sendToBigOriginatorSmsTest()
    {
        try {
            $answer = $this->smsClient->sendSms(TestConfig::TEST_PHONE_1, 'test', 'sn' . (string)time(), 72, 0,
                'lets try to insert very big originator VERRYYY BIG ORIGINATOR');
            $this->assertArrayNotHasKey('result', $answer);
            $this->assertArrayHasKey('error', $answer);
            $this->assertEquals(self::ERR_WRONG_SENDER, $answer['error']);
        } catch (Exception $e) {
            $this->failed($e);
        }
    }

    /**
     * @test
     * @group failed
     */
    public function sendEmptyBodySmsTest()
    {
        try {
            $answer = $this->smsClient->sendSms(TestConfig::TEST_PHONE_1, '', 'swb' . (string)time());
            $this->assertArrayNotHasKey('result', $answer);
            $this->assertArrayHasKey('error', $answer);
            $this->assertEquals(self::ERR_WRONG_BODY, $answer['error']);
        } catch (Exception $e) {
            $this->failed($e);
        }
    }

    /**
     * @test
     * @group failed
     */
    public function sendInvalidExtSmsTest()
    {
        try {
            $answer = $this->smsClient->sendSms(TestConfig::TEST_PHONE_1, 'test', '__\\||\\/');
            $this->assertArrayHasKey('result', $answer);
            $this->assertArrayHasKey('error', $answer['result']);
            $this->assertEquals(self::ERR_WRONG_EXTERNAL_ID, $answer['result']['error']);
        } catch (Exception $e) {
            $this->failed($e);
        }
    }

    /**
     * @test
     * @group failed
     */
    public function sendWrongValidationSmsTest()
    {
        try {
            $answer = $this->smsClient->sendSms(TestConfig::TEST_PHONE_1, 'test', 'wv' . (string)time(), -5);
            $this->assertArrayHasKey('result', $answer);
            $this->assertArrayHasKey('error', $answer['result']);
            $this->assertEquals(self::ERR_WRONG_LIFETIME, $answer['result']['error']);
        } catch (Exception $e) {
            $this->failed($e);
        }
    }

    /**
     * @test
     */
    public function sendSuccessTaskAndGetStatusSmsTest()
    {
        try {
            $answer = $this->smsClient->sendTask([
                ['msisdn' => TestConfig::TEST_PHONE_1, 'reference' => 't' . (string)time()],
            ], 'body');
            $this->assertArrayHasKey('result', $answer);
            $this->assertArrayHasKey('task_id', $answer);
            $this->assertArrayHasKey('error', $answer['result']);
            $this->assertEquals(self::ERR_NO, $answer['result']['error']);

            $taskInfo = $this->smsClient->getTaskStatus($answer['task_id']);
            $this->assertArrayHasKey('originator', $taskInfo);
            $this->assertArrayHasKey('body', $taskInfo);
            $this->assertArrayHasKey('totalprice', $taskInfo);
        } catch (Exception $e) {
            $this->failed($e);
        }
    }

    /**
     * @test
     * @group ok
     */
    public function sendWrongStatusIdSmsTest()
    {
        try {
            $answer = $this->smsClient->getTaskStatus(-5);
            $this->assertArrayHasKey('error', $answer);
            $this->assertEquals(self::ERR_WRONG_TASK_ID, $answer['error']);
        } catch (Exception $e) {
            $this->failed($e);
        }
    }

    /**
     * @test
     * @group ok
     */
    public function sendMissedIdTaskSmsTest()
    {
        try {
            $answer = $this->smsClient->getTaskStatus(99999999999);
            $this->assertArrayHasKey('error', $answer);
            $this->assertEquals(self::ERR_TASK_NOT_FOUND, $answer['error']);
        } catch (Exception $e) {
            $this->failed($e);
        }
    }

    /**
     * @test
     * @group failed
     */
    public function sendInvalidPayloadSmsTest()
    {
        try {
            $answer = $this->smsClient->sendSmsMulti([]);
            $this->assertArrayHasKey('error', $answer);
            $this->assertEquals(self::ERR_WRONG_PAYLOAD, $answer['error']);
        } catch (Exception $e) {
            $this->failed($e);
        }
    }

    /**
     * @test
     * @group failed
     */
    public function sendSamePhoneTest()
    {
        try {
            $answer = $this->smsClient->sendSmsMulti([
                ['msisdn' => TestConfig::TEST_PHONE_1, 'body' => 'test', 'reference' => (string)time()],
                ['msisdn' => TestConfig::TEST_PHONE_1, 'body' => 'test', 'reference' => (string)(time() + 1)],
            ]);
            $this->assertArrayHasKey('result', $answer);
            $this->assertArrayHasKey('1', $answer['result']);
            $this->assertEquals(self::ERR_PHONE_ALREADY_IN_USE, $answer['result'][1]['error']);
        } catch (Exception $e) {
            $this->failed($e);
        }
    }
}