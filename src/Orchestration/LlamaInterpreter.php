<?php

declare(strict_types=1);

namespace Intentio\Orchestration;

use Intentio\Cli\Output;

/**
 * The LlamaInterpreter, which interacts with a local LLaMA model via Ollama.
 *
 * This class sends a structured prompt to the Ollama chat API and
 * streams the response back, adhering to the "interpreter" philosophy.
 */
final class LlamaInterpreter
{
    public function __construct(
        private readonly string $modelName,
        private readonly array $ollamaConfig,
        private readonly array $modelOptions
    ) {
    }

    /**
     * Interprets a prompt by sending it to the Ollama chat API.
     *
     * @param string $prompt The user's query combined with retrieved context.
     * @return string The response from the language model.
     * @throws \RuntimeException If the API call fails.
     */
    public function interpret(string $prompt): string
    {
        $url = $this->ollamaConfig['base_url'] . $this->ollamaConfig['api_path_chat'];

        // Correct payload for /api/chat endpoint
        $payload = json_encode([
            'model' => $this->modelName,
            'messages' => [
                [
                    'role' => 'user',
                    'content' => $prompt,
                ]
            ],
            'stream' => false,
            'options' => $this->modelOptions,
        ]);

        $options = [
            'http' => [
                'header' => "Content-Type: application/json\r\n" .
                            "Content-Length: " . strlen($payload) . "\r\n",
                'method' => 'POST',
                'content' => $payload,
                'ignore_errors' => true,
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

        if (isset($responseData['message']['content'])) {
            return trim($responseData['message']['content']);
        }

        Output::error("Dumping Ollama API response due to unexpected structure:");
        Output::error(print_r($responseData, true));
        throw new \RuntimeException("Invalid response from Ollama API: 'message.content' field not found.");
    }
}
