<?php

declare(strict_types=1);

namespace Intentio\Embedding;

use Intentio\Cli\Output;

/**
 * Generates embeddings using a real local model server (Ollama).
 * This class communicates with the Ollama API to generate embeddings
 * for text chunks using the 'nomic-embed-text' model.
 */
final class NomicEmbedder
{
    public function __construct(
        private readonly string $modelName,
        private readonly array $ollamaConfig
    ) {
    }

    /**
     * Generates an embedding for the given text by calling the Ollama API.
     *
     * @param string $text The text to embed.
     * @return array The embedding vector as an array of floats.
     * @throws \RuntimeException If the API call fails or returns an invalid response.
     */
    public function embed(string $text): array
    {
        $url = $this->ollamaConfig['base_url'] . $this->ollamaConfig['api_path_embeddings'];
        
        $payload = json_encode([
            'model' => $this->modelName,
            'prompt' => $text,
        ]);

        $options = [
            'http' => [
                'header' => "Content-Type: application/json\r\n" .
                            "Content-Length: " . strlen($payload) . "\r\n",
                'method' => 'POST',
                'content' => $payload,
                'ignore_errors' => true, // To handle non-2xx responses gracefully
            ],
        ];

        $context = stream_context_create($options);
        $response = file_get_contents($url, false, $context);
        
        if ($response === false) {
            Output::error("Failed to connect to Ollama server at {$url}.");
            Output::error("Please ensure Ollama is running and accessible.");
            throw new \RuntimeException("Could not connect to Ollama server.");
        }

        $responseData = json_decode($response, true);

        if (isset($responseData['error'])) {
            Output::error("Ollama API Error: " . $responseData['error']);
            if (str_contains($responseData['error'], 'model not found')) {
                Output::error("Model '{$this->modelName}' not found. Please run: ollama pull {$this->modelName}");
            }
            throw new \RuntimeException("Ollama API returned an error: " . $responseData['error']);
        }
        
        if (!isset($responseData['embedding']) || !is_array($responseData['embedding'])) {
            throw new \RuntimeException("Invalid response from Ollama API: 'embedding' field not found or is not an array.");
        }

        return $responseData['embedding'];
    }
}
