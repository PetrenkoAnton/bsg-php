<?php
declare(strict_types=1);

namespace BSG\Clients;

use Exception;

class SmsApiClient extends ApiClient
{
    private string $sender;
    private ?string $tariff;

    public function __construct(string $api_key, string $sender, ?string $tariff = null, ?string $source = null)
    {
        $this->sender = $sender;
        $this->tariff = $tariff;

        parent::__construct($api_key, $source);
    }

    public function getStatusByReference(string $reference): array
    {
        return $this->getStatus('sms/reference/' . $reference);
    }

    public function getStatusById(string $message_id): array
    {
        return $this->getStatus('sms/' . $message_id);
    }

    public function getTaskStatus(int $task_id): array
    {
        try {
            $resp = $this->sendRequest('sms/task/' . $task_id);
        } catch (Exception $e) {
            return $this->getErrorFromException($e);
        }

        return json_decode($resp, true);
    }

    public function getPrices(?string $tariff = null): array
    {
        try {
            $resp = $this->sendRequest('sms/prices' . ($tariff !== NULL ? ('/' . $tariff) : ''));
        } catch (Exception $e) {
            return $this->getErrorFromException($e);
        }

        return json_decode($resp, true);
    }

    public function getPrice
    (
        string $msisdn,
        string $originator,
        string $body,
        string $reference,
        int $validity = 72,
        ?string $tariff = null
    ): array
    {
        $originator = $originator ?: $this->sender;
        $tariff = $tariff ?: $this->tariff;
        return $this->sendSms($msisdn, $body, $reference, $validity, $tariff, $originator, true);
    }

    public function sendSms
    (
        string $msisdn,
        string $body,
        ?string $reference = null,
        int $validity = 72,
        ?int $tariff = null,
        ?string $originator = null,
        bool $only_price = false
    ): array
    {
        $originator = $originator ?: $this->sender;
        $tariff = $tariff ?: $this->tariff;
        $message = [];
        $message['destination'] = 'phone';
        $message['msisdn'] = $msisdn;
        $message['originator'] = $originator;
        $message['body'] = $body;
        $message['reference'] = $reference;
        $message['validity'] = $validity;

        if ($tariff !== null)
            $message['tariff'] = $tariff;

        $endpoint = $only_price ? 'sms/price' : 'sms/create';

        try {
            $resp = $this->sendRequest($endpoint, json_encode($message), 'PUT');
        } catch (Exception $e) {
            return $this->getErrorFromException($e);
        }

        return json_decode($resp, true);
    }

    public function getTaskPrice
    (
        array $msisdns,
        string $body,
        int $validity = 72,
        ?string $tariff = null,
        ?string $originator = NULL
    ): array
    {
        return $this->sendTask($msisdns, $body, $validity, $tariff, $originator, true);
    }

    /**
     * Sends the sms text to array of destination numbers
     * $msisdns are array of [$msisdn, $reference]
     */
    public function sendTask
    (
        array $msisdns,
        string $body,
        int $validity = 72,
        ?string $tariff = null,
        ?string $originator = null,
        bool $only_price = false
    ): array
    {
        $originator = $originator ?: $this->sender;

        $message = [];

        $message['destination'] = 'phones';
        $message['phones'] = $msisdns;
        $message['originator'] = $originator;
        $message['body'] = $body;
        $message['validity'] = $validity;

        if ($tariff !== NULL)
            $message['tariff'] = $tariff;

        $endpoint = $only_price ? 'sms/price' : 'sms/create';

        try {
            $resp = $this->sendRequest($endpoint, json_encode($message), 'PUT');
        } catch (Exception $e) {
            return $this->getErrorFromException($e);
        }

        return json_decode($resp, true);
    }

    public function getMultiPrice(array $messages, int $validity = 72, ?string $tariff = null)
    {
        return $this->sendSmsMulti($messages, $validity, $tariff, true);
    }

    /**
     * Sends a few sms with different senders / originators
     */
    public function sendSmsMulti
    (
        array $messages,
        int $validity = 72,
        ?string $tariff = null,
        bool $only_price = false
    ): array
    {
        foreach ($messages as &$msg)
            if (!isset($msg['originator']) && $this->sender)
                $msg['originator'] = $this->sender;

        $message = [];

        $message['destination'] = 'individual';
        $message['phones'] = $messages;
        $message['validity'] = $validity;

        if ($tariff !== NULL)
            $message['tariff'] = $tariff;

        $endpoint = $only_price ? 'sms/price' : 'sms/create';

        try {
            $resp = $this->sendRequest($endpoint, json_encode($message), 'PUT');
        } catch (Exception $e) {
            return $this->getErrorFromException($e);
        }

        return json_decode($resp, true);
    }
}