<?php

namespace App\Services;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;

class BrickLinkService
{
    private $client;
    private $baseUrl = 'https://api.bricklink.com/api/store/v1/';
    private $consumerKey;
    private $consumerSecret;
    private $accessToken;
    private $tokenSecret;

    public function __construct()
    {
        // load the settings from the json file and decrypt them
        $settings = json_decode(file_get_contents(storage_path('app/settings.json')), true);

        $this->consumerKey = decrypt($settings['consumerKey']) ?? '';
        $this->consumerSecret = decrypt($settings['consumerSecret']) ?? '';
        $this->accessToken = decrypt($settings['accessToken']) ?? '';
        $this->tokenSecret = decrypt($settings['tokenSecret']) ?? '';

        // Guzzle-Client erstellen
        $this->client = new Client([
            'base_uri' => $this->baseUrl,
        ]);
    }

    /**
     * Führt einen API-Call zu BrickLink aus.
     *
     * @param string $method HTTP-Methode (GET, POST, etc.)
     * @param string $endpoint API-Endpunkt (z.B. "items/SET/{item_no}")
     * @param array $params Query-Parameter oder POST-Daten
     * @return mixed
     * @throws \Exception
     */
    public function callApi($method, $endpoint, $params = [])
    {
        $url = $this->baseUrl . $endpoint;

        // OAuth-Header generieren
        $authHeader = $this->generateOAuthHeader($method, $url, $params);

        try {

            $response = $this->client->request($method, $url, [
                'headers' => [
                    'Authorization' => $authHeader,
                    'Content-Type' => 'application/json',
                ],
                'query' => $method === 'GET' ? $params : [],
                'json' => $method === 'POST' ? $params : [],
            ]);

            return json_decode($response->getBody()->getContents(), true);
        } catch (RequestException $e) {
            $response = $e->getResponse();
            if ($response) {
                $body = $response->getBody()->getContents();
                throw new \Exception('API Call failed: ' . $body);
            }
            throw new \Exception('API Call failed: ' . $e->getMessage());
        }
    }

    /**
     * Set-Informationen abrufen.
     *
     * @param string $setNumber Die Set-Nummer (z.B. "75218")
     * @return mixed
     */
    public function getSetInfo($setNumber)
    {
        $endpoint = "items/SET/{$setNumber}";
        return $this->callApi('GET', $endpoint);
    }

    /**
     * Teileliste für ein Set abrufen.
     *
     * @param string $setNumber Die Set-Nummer
     * @return mixed
     */
    public function getSetParts($setNumber)
    {
        $endpoint = "items/SET/{$setNumber}/subsets";

        return $this->callApi('GET', $endpoint);
    }

    /**
     * OAuth-Header für die BrickLink API generieren.
     *
     * @param string $method HTTP-Methode
     * @param string $url Die vollständige URL
     * @param array $params Zusätzliche Parameter
     * @return string
     */
    private function generateOAuthHeader($method, $url, $params = [])
    {
        $nonce = bin2hex(random_bytes(16));
        $timestamp = time();

        // OAuth-Parameter
        $baseParams = [
            'oauth_consumer_key' => $this->consumerKey,
            'oauth_nonce' => $nonce,
            'oauth_signature_method' => 'HMAC-SHA1',
            'oauth_timestamp' => $timestamp,
            'oauth_token' => $this->accessToken,
            'oauth_version' => '1.0',
        ];

        // Mische OAuth-Parameter und Query-Parameter
        $allParams = array_merge($baseParams, $params);
        ksort($allParams); // Alphabetisch sortieren

        // Base String für die Signatur
        $baseString = strtoupper($method) . '&' . rawurlencode($url) . '&' . rawurlencode(http_build_query($allParams, '', '&', PHP_QUERY_RFC3986));

        // Signing Key
        $signingKey = rawurlencode($this->consumerSecret) . '&' . rawurlencode($this->tokenSecret);

        // Signatur generieren
        $signature = base64_encode(hash_hmac('sha1', $baseString, $signingKey, true));

        // Signatur zu den OAuth-Parametern hinzufügen
        $baseParams['oauth_signature'] = $signature;

        // Header zusammenstellen
        $authHeader = 'OAuth ';
        foreach ($baseParams as $key => $value) {
            $authHeader .= rawurlencode($key) . '="' . rawurlencode($value) . '", ';
        }

        // Letztes Komma entfernen
        return rtrim($authHeader, ', ');
    }
}
