<?php

declare(strict_types=1);

namespace Intentio\Infrastructure\LLM;

use Intentio\Domain\Model\LLMInterface;
use Intentio\Shared\Exceptions\IntentioException;

final class OllamaAdapter implements LLMInterface
{
    private string $baseUrl;
    private string $apiPathGenerate;
    private string $llmModel;
    private array $defaultOptions;
    private int $timeout;

    public function __construct(
        array $ollamaConfig,
        string $llmModel,
        array $defaultOptions
    ) {
        $this->baseUrl = rtrim($ollamaConfig['base_url'] ?? 'http://127.0.0.1:11434', '/');
        $this->apiPathGenerate = $ollamaConfig['api_path_generate'] ?? '/api/generate';
        $this->llmModel = $llmModel;
        $this->defaultOptions = $defaultOptions;
        $this->timeout = $ollamaConfig['timeout'] ?? 120;
    }

    public function generate(string $prompt, string $context = '', array $options = []): string
    {
        $url = $this->baseUrl . $this->apiPathGenerate;

        // Combine default options with specific call options
        $mergedOptions = array_merge($this->defaultOptions, $options);

        $data = [
            'model' => $this->llmModel,
            'prompt' => $prompt,
            'stream' => false, // We want a single response
            'options' => $mergedOptions,
        ];
        
        // Context is prepended to the prompt for Ollama generate API
        if (!empty($context)) {
            $data['prompt'] = $context . "\n\n" . $data['prompt'];
        }

        $requestOptions = [
            'http' => [
                'header'  => "Content-type: application/json\r\n",
                'method'  => 'POST',
                'content' => json_encode($data),
                'timeout' => $this->timeout,
            ],
        ];
        $context  = stream_context_create($requestOptions);

        $result = @file_get_contents($url, false, $context);

        if ($result === false) {
            $error = error_get_last();
            $message = "Failed to get LLM response from Ollama at {$url}. " . ($error['message'] ?? 'Unknown error');
            if (str_contains($message, 'Connection refused')) {
                $message .= " Make sure Ollama server is running and the model '{$this->llmModel}' is pulled.";
            }
            throw new IntentioException($message);
        }

        $response = json_decode($result, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new IntentioException("Failed to decode Ollama API response: " . json_last_error_msg());
        }

        if (!isset($response['response'])) {
            throw new IntentioException("Ollama API response missing 'response' field.");
        }

        return $response['response'];
    }
}
