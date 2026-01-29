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
    private string $imageRendererModel;
    private string $rendererFolder;

    public function __construct(
        array $ollamaConfig,
        string $llmModel,
        array $defaultOptions,
        array $imageRendererConfig
    ) {
        $this->baseUrl = rtrim($ollamaConfig['base_url'] ?? 'http://127.0.0.1:11434', '/');
        $this->apiPathGenerate = $ollamaConfig['api_path_generate'] ?? '/api/generate';
        $this->llmModel = $llmModel;
        $this->defaultOptions = $defaultOptions;
        $this->timeout = $ollamaConfig['timeout'] ?? 120;
        $this->imageRendererModel = $imageRendererConfig['model_name'] ?? '';
        $this->rendererFolder = $imageRendererConfig['renderer_folder'] ?? '';
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

    public function render(string $prompt, array $options = []): void
    {
        fwrite(STDOUT, "DEBUG: OllamaAdapter->render received prompt: '" . $prompt . "'" . PHP_EOL);
        // Ollama image generation typically saves to a file via CLI,
        // rather than returning base64 data via /api/generate for models like flux2-klein.
        // We will execute the ollama run command directly.

        $promptArg = escapeshellarg($prompt);
        $modelArg = escapeshellarg($this->imageRendererModel);

        // Construct the command to run Ollama CLI
        // We assume 'ollama' is in the system's PATH
        $command = "ollama run {$modelArg} {$promptArg} 2>&1"; // Redirect stderr to stdout

        fwrite(STDOUT, "Executing image generation command: {$command}" . PHP_EOL);

        // Execute the command
        $output = shell_exec($command);

        if ($output === null) {
            throw new IntentioException("Failed to execute Ollama CLI command. Check if 'ollama' is in your system's PATH.");
        }
        
        // Parse the output to find the "Image saved to: " line
        if (preg_match('/Image saved to: (.+)/', $output, $matches)) {
            $ollamaSavedPath = trim($matches[1]);
            
            // Ensure the renderer folder exists
            if (!is_dir($this->rendererFolder)) {
                if (!mkdir($this->rendererFolder, 0777, true)) {
                    throw new IntentioException("Failed to create renderer folder: '{$this->rendererFolder}'.");
                }
            }

            // Generate a unique filename for our application
            $filename = 'render_' . time() . '_' . basename($ollamaSavedPath);
            $targetPath = rtrim($this->rendererFolder, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . $filename;

            // Move the file to our target renderer folder
            if (copy($ollamaSavedPath, $targetPath)) {
                // Optionally remove the original file saved by Ollama
                @unlink($ollamaSavedPath); 
                fwrite(STDOUT, "Image rendered and saved to: {$targetPath}" . PHP_EOL);
            } else {
                throw new IntentioException("Failed to copy rendered image from '{$ollamaSavedPath}' to '{$targetPath}'. Check folder permissions.");
            }
        } else {
            // If the output does not contain the expected "Image saved to:" message
            // or if the Ollama model outputted an error.
            throw new IntentioException("Ollama image generation failed or did not return an image save path. Output: " . $output);
        }
    }
}
