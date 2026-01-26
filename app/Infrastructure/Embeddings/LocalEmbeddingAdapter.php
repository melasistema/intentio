<?php

declare(strict_types=1);

namespace Intentio\Infrastructure\Embeddings;

use Intentio\Domain\Model\EmbeddingInterface;
use Intentio\Shared\Exceptions\IntentioException;

final class LocalEmbeddingAdapter implements EmbeddingInterface
{
    private string $baseUrl;
    private string $apiPathEmbeddings;
    private string $embeddingModel;
    private int $timeout;

    public function __construct(array $ollamaConfig, string $embeddingModel)
    {
        $this->baseUrl = rtrim($ollamaConfig['base_url'] ?? 'http://127.0.0.1:11434', '/');
        $this->apiPathEmbeddings = $ollamaConfig['api_path_embeddings'] ?? '/api/embeddings';
        $this->embeddingModel = $embeddingModel;
        $this->timeout = $ollamaConfig['timeout'] ?? 120; // Default to 120 seconds
    }

    public function embed(string $text): array
    {
        $url = $this->baseUrl . $this->apiPathEmbeddings;

        $data = [
            'model' => $this->embeddingModel,
            'prompt' => $text,
        ];

        $options = [
            'http' => [
                'header'  => "Content-type: application/json\r\n",
                'method'  => 'POST',
                'content' => json_encode($data),
                'timeout' => $this->timeout,
            ],
        ];
        $context  = stream_context_create($options);

        // Suppress errors with @ and handle them manually
        $result = @file_get_contents($url, false, $context);

        if ($result === false) {
            $error = error_get_last();
            $message = "Failed to get embedding from Ollama at {$url}. " . ($error['message'] ?? 'Unknown error');
            if (str_contains($message, 'Connection refused')) {
                $message .= " Make sure Ollama server is running and the model '{$this->embeddingModel}' is pulled.";
            }
            throw new IntentioException($message);
        }

        $response = json_decode($result, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new IntentioException("Failed to decode Ollama API response: " . json_last_error_msg());
        }

        if (!isset($response['embedding']) || !is_array($response['embedding'])) {
            throw new IntentioException("Ollama API response missing 'embedding' field or it's not an array.");
        }

        return $response['embedding'];
    }
}
