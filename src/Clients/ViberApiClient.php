<?php
declare(strict_types=1);

namespace BSG\Clients;

use Exception;

class ViberApiClient extends ApiClient
{
    protected array $messages = [];
    protected string $sender;

    public function __construct(string $api_key, string $sender, ?string $source = null)
    {
        $this->sender = $sender;
        parent::__construct($api_key, $source);
    }

    public function getStatusByReference(string $reference): array
    {
        return $this->getStatus('viber/reference/' . $reference);
    }

    public function getStatusById(int $message_id): array
    {
        return $this->getStatus('viber/' . $message_id);
    }

    public function getPrices(?string $tariff = null): array
    {
        try {
            $resp = $this->sendRequest('viber/prices' . ($tariff !== NULL ? ('/' . $tariff) : ''));
        } catch (Exception $e) {
            return $this->getErrorFromException($e);
        }

        return json_decode($resp, true);
    }

    public function clearMessages(): void
    {
        $this->messages = [];
    }

    /**
     * Param $to is an array of ['msisdn' => $msisdn, 'reference' => $reference], where 'reference' is optional
     */
    public function addMessage(
        array $to,
        string $text, array $viber_options = [],
        string $alpha_name = null,
        bool $is_promotional = true,
        string $callback_url = ''
    ): void
    {
        $alpha_name = $alpha_name ?: $this->sender;

        $message = [];

        $message['to'] = $to;
        $message['text'] = $text;
        $message['alpha_name'] = $alpha_name;

        if (!$is_promotional)
            $message['is_promotional'] = $is_promotional;

        if ($callback_url != '')
            $message['callback_url'] = $callback_url;

        if (count($viber_options) > 0)
            $message['options']['viber'] = $viber_options;

        $this->messages[] = $message;
    }

    public function getMessagesPrice(int $validity = 86400, ?string $tariff = null): array
    {
        return $this->sendMessages($validity, $tariff, true);
    }

    public function sendMessages(int $validity = 86400, ?string $tariff = null, bool $only_price = false): array
    {
        if (count($this->messages) == 0)
            return ['error' => 'No messages to send'];

        $message = [];

        $message['validity'] = $validity;

        if ($tariff !== NULL)
            $message['tariff'] = $tariff;

        $message['messages'] = $this->messages;

        $endpoint = $only_price ? 'viber/price' : 'viber/create';

        try {
            $resp = $this->sendRequest($endpoint, json_encode($message), 'PUT');
        } catch (Exception $e) {
            return $this->getErrorFromException($e);
        }

        return json_decode($resp, true);
    }
}