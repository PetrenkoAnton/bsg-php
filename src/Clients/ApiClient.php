<?php
declare(strict_types=1);

namespace BSG\Clients;

use Exception;

class ApiClient
{
    private const API_URL = 'https://api.bsg.hk/v1.0/';

    protected string $api_key;
    protected string $api_source;

    public function __construct(string $api_key, ?string $api_source = null)
    {
        $this->api_key = $api_key;
        $this->api_source = !$api_source ? 'BSG PHP Library' : $api_source;
    }

    /**
     * @throws Exception
     */
    public function sendRequest(string $resource_url, $post_data = null, ?string $custom_request = null)
    {
        $client = curl_init();

        if ($post_data === NULL || !is_array($post_data))
            curl_setopt($client, CURLOPT_HTTPHEADER, array('X-API-KEY: ' . $this->api_key, 'X-API-SOURCE: ' . $this->api_source, 'Content-type: text/json; charset=utf-8'));
        else
            curl_setopt($client, CURLOPT_HTTPHEADER, array('X-API-KEY: ' . $this->api_key, 'X-API-SOURCE: ' . $this->api_source));

        curl_setopt($client, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($client, CURLOPT_FOLLOWLOCATION, false);

        if ($custom_request !== NULL)
            curl_setopt($client, CURLOPT_CUSTOMREQUEST, $custom_request);

        curl_setopt($client, CURLOPT_URL, self::API_URL . $resource_url);

        if ($post_data !== NULL and $custom_request === NULL)
            curl_setopt($client, CURLOPT_POST, true);

        if ($post_data !== NULL)
            curl_setopt($client, CURLOPT_POSTFIELDS, $post_data);

        $result = curl_exec($client);

        if (!$result)
            throw new Exception (curl_error($client), curl_errno($client));

        return $result;
    }

    public function addLog(string $message): void
    {
        // TODO! Need to be implemented!
    }

    /**
     * @throws Exception
     */
    public function getBalance(): array
    {
        try {
            $resp = $this->sendRequest('common/balance');
        } catch (Exception $e) {
            $error = 'Request failed (code: ' . $e->getCode() . '): ' . $e->getMessage();
            $this->addLog($error);

            throw new Exception($error, -1);
        }

        return json_decode($resp, true);
    }

    protected function getStatus(string $endpoint): array
    {
        try {
            $resp = $this->sendRequest($endpoint);
        } catch (Exception $e) {
            return $this->getErrorFromException($e);
        }

        return json_decode($resp, true);
    }

    protected function getErrorFromException(Exception $e): array
    {
        $error = 'Request failed (code: ' . $e->getCode() . '): ' . $e->getMessage();
        return ['error' => $error];
    }
}
