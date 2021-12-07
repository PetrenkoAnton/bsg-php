<?php
declare(strict_types=1);

namespace BSG;

use BSG\Clients\HLRApiClient;
use BSG\Clients\SmsApiClient;
use BSG\Clients\ViberApiClient;

class BSG
{
    private string $apiKey;
    private ?string $sender;
    private ?string $tariff;
    private ?string $viberSender;
    private ?string $apiSource;

    public function __construct(
        string $apiKey,
        ?string $sender = null,
        ?string $viberSender = null,
        ?string $tariff = null,
        ?string $apiSource = null
    )
    {
        $this->apiKey = $apiKey;
        $this->sender = $sender;
        $this->tariff = $tariff;
        $this->viberSender = $viberSender;
        $this->apiSource = $apiSource;
    }

    public function getSmsClient(): SmsApiClient
    {
        return new SmsApiClient($this->apiKey, $this->sender, $this->tariff, $this->apiSource);
    }

    public function getHLRClient(): HLRApiClient
    {
        return new HLRApiClient($this->apiKey, $this->tariff, $this->apiSource);
    }

    public function getViberClient(): ViberApiClient
    {
        return new ViberApiClient($this->apiKey, $this->viberSender, $this->apiSource);
    }

}