<?php

namespace App\Services\BrickLink;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;
use Illuminate\Support\Facades\Log;

class BrickLinkApiClient
{
    private Client $client;
    private string $baseUrl = 'https://api.bricklink.com/api/store/v1/';
    private array $credentials;

    public function __construct()
    {
        // Load and decrypt API credentials
        $this->credentials = json_decode(file_get_contents(storage_path('app/settings.json')), true);
        array_walk($this->credentials, fn(&$val) => $val = decrypt($val));

        $this->client = new Client(['base_uri' => $this->baseUrl]);
    }

    /**
     * Makes an API request to BrickLink.
     */
    public function request(string $method, string $endpoint, array $params = []): mixed
    {
        $url = "{$this->baseUrl}{$endpoint}";

        try {
            $response = $this->client->request($method, $url, [
                'headers' => [
                    'Authorization' => $this->generateOAuthHeader($method, $url, $params),
                    'Content-Type' => 'application/json',
                ],
                'query' => $method === 'GET' ? $params : [],
                'json' => $method === 'POST' ? $params : [],
            ]);

            return json_decode($response->getBody()->getContents(), true);

        } catch (RequestException $e) {
            $response = $e->getResponse();
            $errorMessage = $response ? $response->getBody()->getContents() : $e->getMessage();
            Log::error("BrickLink API Error ({$endpoint}): {$errorMessage}");
            return null;
        }
    }

    /**
     * Generates an OAuth authorization header for BrickLink API.
     */
    private function generateOAuthHeader(string $method, string $url, array $params = []): string
    {
        $nonce = bin2hex(random_bytes(16));
        $timestamp = time();

        // OAuth base parameters
        $baseParams = [
            'oauth_consumer_key' => $this->credentials['consumerKey'],
            'oauth_nonce' => $nonce,
            'oauth_signature_method' => 'HMAC-SHA1',
            'oauth_timestamp' => $timestamp,
            'oauth_token' => $this->credentials['accessToken'],
            'oauth_version' => '1.0',
        ];

        // Merge OAuth and API parameters
        $allParams = array_merge($baseParams, $params);
        ksort($allParams); // Sort alphabetically

        // Build the signature base string
        $baseString = strtoupper($method) . '&' . rawurlencode($url) . '&' . rawurlencode(http_build_query($allParams, '', '&', PHP_QUERY_RFC3986));

        // Generate the signing key
        $signingKey = rawurlencode($this->credentials['consumerSecret']) . '&' . rawurlencode($this->credentials['tokenSecret']);

        // Generate the signature
        $signature = base64_encode(hash_hmac('sha1', $baseString, $signingKey, true));

        // Add signature to the parameters
        $baseParams['oauth_signature'] = $signature;

        // Generate the OAuth header string
        $authHeader = 'OAuth ';
        foreach ($baseParams as $key => $value) {
            $authHeader .= rawurlencode($key) . '="' . rawurlencode($value) . '", ';
        }

        return rtrim($authHeader, ', ');
    }
}
