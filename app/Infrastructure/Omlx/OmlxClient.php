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
        return $this->request('POST', $path, $this->encode($body));
    }

    /**
     * Sends a request body to the server and reads the answer while the server is still writing it.
     *
     * @param string $path The API path, e.g. '/v1/chat/completions'.
     * @param array $body The request, encoded as JSON. It must ask the server to stream.
     * @param callable $onEvent Called with each decoded event of the answer, in the order they arrive.
     */
    public function stream(string $path, array $body, callable $onEvent): void
    {
        $url = $this->baseUrl . $path;

        $response = @fopen($url, 'r', false, $this->context('POST', $this->encode($body)));
        if ($response === false) {
            throw $this->noAnswer($url);
        }

        // PHP waits until it has read a whole block (8192 bytes) before it hands over a line, which would
        // hold a short answer back until its end. With a block of one byte, each line is handed over as it arrives.
        stream_set_chunk_size($response, 1);

        // A streamed answer is a series of lines "data: {...}", closed by the line "data: [DONE]"
        $complete = false;
        $otherLines = '';
        while (($line = fgets($response)) !== false) {
            $line = trim($line);

            if ($line === '' || str_starts_with($line, ':')) {
                continue; // Blank lines separate events, and a line starting with a colon is a comment
            }

            if (!str_starts_with($line, 'data:')) {
                $otherLines .= $line;
                continue;
            }

            $data = trim(substr($line, strlen('data:')));
            if ($data === '[DONE]') {
                $complete = true;
                break;
            }

            $onEvent($this->decode($data, $url));
        }

        $timedOut = stream_get_meta_data($response)['timed_out'];
        fclose($response);

        if ($complete) {
            return;
        }

        if ($otherLines !== '') {
            // The server refused the request: its answer is one JSON document instead of a stream
            $this->decode($otherLines, $url);
        }

        if ($timedOut) {
            throw new IntentioException("oMLX at {$url} wrote nothing for {$this->timeout} seconds (omlx.timeout), so the answer was given up.");
        }

        throw new IntentioException("The answer of oMLX at {$url} ended before it was complete.");
    }

    private function request(string $method, string $path, ?string $content): array
    {
        $url = $this->baseUrl . $path;

        $result = @file_get_contents($url, false, $this->context($method, $content));
        if ($result === false) {
            throw $this->noAnswer($url);
        }

        return $this->decode($result, $url);
    }

    private function encode(array $body): string
    {
        $content = json_encode($body);
        if ($content === false) {
            throw new IntentioException("The request to oMLX could not be encoded: " . json_last_error_msg());
        }

        return $content;
    }

    /**
     * Builds the HTTP settings of a request.
     *
     * @return resource A stream context.
     */
    private function context(string $method, ?string $content)
    {
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

        return stream_context_create(['http' => $http]);
    }

    /**
     * Decodes what the server wrote, and reports an error the server put in it.
     */
    private function decode(string $json, string $url): array
    {
        $response = json_decode($json, true);
        if (!is_array($response)) {
            throw new IntentioException("oMLX at {$url} answered with something that is not JSON.");
        }

        if (isset($response['error'])) {
            $message = $response['error']['message'] ?? json_encode($response['error']);
            throw new IntentioException("oMLX returned an error: {$message}");
        }

        return $response;
    }

    private function noAnswer(string $url): IntentioException
    {
        $error = error_get_last();

        return new IntentioException(
            "No answer from oMLX at {$url}. " . ($error['message'] ?? 'Unknown error')
            . " Make sure the oMLX server is running and omlx.base_url is correct."
        );
    }
}
