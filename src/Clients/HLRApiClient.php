<?php
declare(strict_types=1);

namespace BSG\Clients;

use Exception;

class HLRApiClient extends ApiClient
{
    protected ?string $tariff;

    public function __construct(string $api_key, ?string $tariff = null, string $source = null)
    {
        $this->tariff = $tariff;
        parent::__construct($api_key, $source);
    }

    public function getStatusByReference(string $reference): array
    {
        return $this->getStatus('hlr/reference/' . $reference);
    }

    public function getStatusById(int $message_id): array
    {
        return $this->getStatus('hlr/' . $message_id);
    }

    public function getPrices(?string $tariff = null): array
    {
        try {
            $resp = $this->sendRequest('hlr/prices' . ($tariff !== null ? ('/' . $tariff) : ''));
        } catch (Exception $e) {
            return $this->getErrorFromException($e);
        }

        return json_decode($resp, true);
    }

    public function sendHLR(string $msisdn, string $reference, ?string $tariff = null): array
    {
        $tariff = $tariff ?: $this->tariff;

        $message = [];
        $message['destination'] = 'phone';
        $message['msisdn'] = $msisdn;
        $message['reference'] = $reference;

        if ($tariff !== NULL)
            $message['tariff'] = $tariff;

        try {
            $resp = $this->sendRequest('hlr/create', $message);
        } catch (Exception $e) {
            return $this->getErrorFromException($e);
        }

        return json_decode($resp, true);
    }

    /**
     * Sends multiply HLR requests. $payload must contain array of arrays:
     * [$msisdn, $reference, $tariff, $callback_url], where $tariff and $callback_url
     * are optional
     */
    public function sendHLRs(array $payload): array
    {
        try {
            $resp = $this->sendRequest('hlr/create', json_encode($payload), 'PUT');
        } catch (Exception $e) {
            return $this->getErrorFromException($e);
        }

        return json_decode($resp, true);
    }
}