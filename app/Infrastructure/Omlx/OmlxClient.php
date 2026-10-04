<?php

declare(strict_types=1);

namespace Intentio\Infrastructure\Omlx;

use Intentio\Shared\Exceptions\IntentioException;

/**
 * The one place where INTENTIO talks to the local oMLX server.
 */
final class OmlxClient
{
    private string $baseUrl;
    private string $apiKey;
    private int $timeout;

    public function __construct(array $omlxConfig)
    {
        $this->baseUrl = rtrim($omlxConfig['base_url'] ?? 'http://localhost:8000', '/');
        $this->apiKey = $omlxConfig['api_key'] ?? '';
        $this->timeout = $omlxConfig['timeout'] ?? 120;
    }

    public function getBaseUrl(): string
    {
        return $this->baseUrl;
    }

    /**
     * Reads a resource from the server.
     *
     * @param string $path The API path, e.g. '/v1/models'.
     * @return array The decoded response.
     */
    public function get(string $path): array
    {
        return $this->request('GET', $path, null);
    }

    /**
     * Sends a request body to the server.
     *
     * @param string $path The API path, e.g. '/v1/embeddings'.
     * @param array $body The request, encoded as JSON.
     * @return array The decoded response.
     */
    public function post(string $path, array $body): array
    {
        $content = json_encode($body);
        if ($content === false) {
            throw new IntentioException("The request to oMLX could not be encoded: " . json_last_error_msg());
        }

        return $this->request('POST', $path, $content);
    }

    private function request(string $method, string $path, ?string $content): array
    {
        $url = $this->baseUrl . $path;

        $headers = "Content-Type: application/json\r\n";
        if ($this->apiKey !== '') {
            $headers .= "Authorization: Bearer {$this->apiKey}\r\n";
        }

        $http = [
            'method' => $method,
            'header' => $headers,
            'timeout' => $this->timeout,
            'ignore_errors' => true, // Read the body of error responses, so oMLX's own message can be reported
        ];
        if ($content !== null) {
            $http['content'] = $content;
        }

        $result = @file_get_contents($url, false, stream_context_create(['http' => $http]));

        if ($result === false) {
            $error = error_get_last();
            throw new IntentioException(
                "No answer from oMLX at {$url}. " . ($error['message'] ?? 'Unknown error')
                . " Make sure the oMLX server is running and omlx.base_url is correct."
            );
        }

        $response = json_decode($result, true);
        if (!is_array($response)) {
            throw new IntentioException("oMLX at {$url} answered with something that is not JSON.");
        }

        if (isset($response['error'])) {
            $message = $response['error']['message'] ?? json_encode($response['error']);
            throw new IntentioException("oMLX returned an error: {$message}");
        }

        return $response;
    }
}
