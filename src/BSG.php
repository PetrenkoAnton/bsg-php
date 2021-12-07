<?php
declare(strict_types=1);

namespace BSG;

use BSG\Clients\HLRApiClient;
use BSG\Clients\SmsApiClient;
use BSG\Clients\ViberApiClient;

class BSG
{
    private $apiKey;
    private $sender;
    private $tariff;
    private $viberSender;
    private $apiSource;

    public function __construct($apiKey, $sender = null, $viberSender = null, $tariff = null, $apiSource = null) {
        $this->apiKey = $apiKey;
        $this->sender = $sender;
        $this->tariff = $tariff;
        $this->viberSender = $viberSender;
        $this->apiSource = $apiSource;
    }

    public function getSmsClient(): SmsApiClient {
        return new SmsApiClient($this->apiKey, $this->sender, $this->tariff, $this->apiSource);
    }

    public function getHLRClient(): HLRApiClient {
        return new HLRApiClient($this->apiKey, $this->tariff, $this->apiSource);
    }

    public function getViberClient(): ViberApiClient {
        return new ViberApiClient($this->apiKey, $this->viberSender, $this->apiSource);
    }

}