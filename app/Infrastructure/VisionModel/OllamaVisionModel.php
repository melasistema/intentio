<?php

declare(strict_types=1);

namespace Intentio\Infrastructure\VisionModel;

use Intentio\Domain\Model\VisionModelInterface;
use Intentio\Shared\Exceptions\IntentioException;

final class OllamaVisionModel implements VisionModelInterface
{
    private string $visionModelName;
    private array $defaultOptions;
    private string $ollamaBaseUrl;

    public function __construct(array $visionModelConfig, array $ollamaConfig)
    {
        $this->visionModelName = $visionModelConfig['model_name'] ?? 'llava:latest';
        $this->defaultOptions = $visionModelConfig['options'] ?? [];
        $this->ollamaBaseUrl = rtrim($ollamaConfig['base_url'] ?? 'http://127.0.0.1:11434', '/');
    }

    /**
     * Processes an image and returns a textual interpretation or analysis.
     *
     * @param string $imagePath The path to the image file to process.
     * @param string $prompt An optional prompt or question to guide the vision model's interpretation.
     * @param array $options Additional options for the vision model.
     * @return string A textual interpretation or analysis of the image.
     * @throws IntentioException If the image processing fails.
     */
    public function analyzeImage(string $imagePath, string $prompt = '', array $options = []): string
    {
        if (!file_exists($imagePath) || !is_readable($imagePath)) {
            throw new IntentioException("Image file not found or not readable at: {$imagePath}");
        }

        // Encode image to base64
        $imageData = base64_encode(file_get_contents($imagePath));

        $url = $this->ollamaBaseUrl . '/api/chat'; // Use Ollama's chat endpoint for LLaVA

        $mergedOptions = array_merge($this->defaultOptions, $options);

        $data = [
            'model' => $this->visionModelName,
            'stream' => false,
            'messages' => [
                [
                    'role' => 'user',
                    'content' => $prompt,
                    'images' => [$imageData],
                ]
            ],
            'options' => $mergedOptions,
        ];

        $requestOptions = [
            'http' => [
                'header'  => "Content-type: application/json\r\n",
                'method'  => 'POST',
                'content' => json_encode($data),
                'timeout' => 300, // Vision models might take longer
            ],
        ];
        $context  = stream_context_create($requestOptions);

        $result = @file_get_contents($url, false, $context);

        if ($result === false) {
            $error = error_get_last();
            $message = "Failed to get vision model response from Ollama at {$url}. " . ($error['message'] ?? 'Unknown error');
            if (str_contains($message, 'Connection refused')) {
                $message .= " Make sure Ollama server is running and the model '{$this->visionModelName}' is pulled.";
            }
            throw new IntentioException($message);
        }

        $response = json_decode($result, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new IntentioException("Failed to decode Ollama API response: " . json_last_error_msg());
        }

        if (!isset($response['message']['content'])) {
            throw new IntentioException("Ollama API response missing 'message.content' field from vision model.");
        }

        return $response['message']['content'];
    }
}
