<?php

declare(strict_types=1);

namespace Intentio\Application\Console\Commands;

use Intentio\Infrastructure\Filesystem\LocalSpaceRepository;
use Intentio\Shared\Exceptions\IntentioException;

final class StatusCommand implements CommandInterface
{
    private const NAME = 'status';
    private const DESCRIPTION = 'Displays the current INTENTIO system status and configuration.';

    public function __construct(
        private readonly LocalSpaceRepository $spaceRepository,
        private readonly array $config
    ) {
    }

    public function getName(): string
    {
        return self::NAME;
    }

    public function getDescription(): string
    {
        return self::DESCRIPTION;
    }

    public function execute(array $arguments, array $options): int
    {
        fwrite(STDOUT, "--- INTENTIO System Status ---" . PHP_EOL);

        // 1. Application Info
        fwrite(STDOUT, "\nApplication:" . PHP_EOL);
        fwrite(STDOUT, sprintf("  Name: %s" . PHP_EOL, $this->config['app_name'] ?? 'INTENTIO'));
        fwrite(STDOUT, sprintf("  Version: %s" . PHP_EOL, $this->config['app_version'] ?? 'N/A'));

        // 2. Core Paths
        fwrite(STDOUT, "\nPaths:" . PHP_EOL);
        fwrite(STDOUT, sprintf("  Spaces Base Path: %s" . PHP_EOL, $this->config['spaces_base_path'] ?? 'N/A'));
        fwrite(STDOUT, sprintf("  Blueprints Base Path: %s" . PHP_EOL, $this->config['blueprints_base_path'] ?? 'N/A'));

        // 3. Ollama Configuration
        $ollamaConfig = $this->config['ollama'] ?? [];
        fwrite(STDOUT, "\nOllama Configuration:" . PHP_EOL);
        fwrite(STDOUT, sprintf("  Base URL: %s" . PHP_EOL, $ollamaConfig['base_url'] ?? 'N/A'));
        fwrite(STDOUT, sprintf("  Embeddings API Path: %s" . PHP_EOL, $ollamaConfig['api_path_embeddings'] ?? 'N/A'));
        fwrite(STDOUT, sprintf("  Generate API Path: %s" . PHP_EOL, $ollamaConfig['api_path_generate'] ?? 'N/A'));
        fwrite(STDOUT, sprintf("  Timeout: %d seconds" . PHP_EOL, $ollamaConfig['timeout'] ?? 120));

        // 4. LLM Configuration
        $llmConfig = $this->config['llm'] ?? [];
        fwrite(STDOUT, "\nLLM Configuration:" . PHP_EOL);
        fwrite(STDOUT, sprintf("  Model Name: %s" . PHP_EOL, $llmConfig['model_name'] ?? 'N/A'));
        fwrite(STDOUT, sprintf("  Default Prompt Template: %s" . PHP_EOL, $llmConfig['default_prompt_template_name'] ?? 'N/A'));
        fwrite(STDOUT, "  Options: " . json_encode($llmConfig['options'] ?? [], JSON_PRETTY_PRINT) . PHP_EOL);

        // 5. Embedding Configuration
        $embeddingConfig = $this->config['embedding'] ?? [];
        fwrite(STDOUT, "\nEmbedding Configuration:" . PHP_EOL);
        fwrite(STDOUT, sprintf("  Model Name: %s" . PHP_EOL, $embeddingConfig['model_name'] ?? 'N/A'));

        // 6. Available Cognitive Spaces
        fwrite(STDOUT, "\nAvailable Cognitive Spaces:" . PHP_EOL);
        try {
            $spaces = $this->spaceRepository->findAll();
            if (empty($spaces)) {
                fwrite(STDOUT, "  No spaces found." . PHP_EOL);
            } else {
                foreach ($spaces as $space) {
                    fwrite(STDOUT, sprintf("  - %s (Path: %s)" . PHP_EOL, $space->getName(), $space->getPath()));
                }
            }
        } catch (IntentioException $e) {
            fwrite(STDERR, "  Error listing spaces: " . $e->getMessage() . PHP_EOL);
        }

        // 7. Ollama Server Status & Model Availability
        fwrite(STDOUT, "\nOllama Server Status:" . PHP_EOL);
        $ollamaStatus = $this->checkOllamaServer($ollamaConfig['base_url'] ?? '');
        fwrite(STDOUT, sprintf("  Server Reachable: %s" . PHP_EOL, $ollamaStatus['reachable'] ? 'Yes' : 'No'));

        if ($ollamaStatus['reachable']) {
            fwrite(STDOUT, "  Available Models on Server:" . PHP_EOL);
            $availableModels = $this->getOllamaModels($ollamaConfig['base_url'] ?? '');
            if (empty($availableModels)) {
                fwrite(STDOUT, "    No models found on Ollama server." . PHP_EOL);
            } else {
                foreach ($availableModels as $model) {
                    $llmStatus = ($llmConfig['model_name'] === $model['name']) ? ' (Configured LLM)' : '';
                    $embeddingStatus = ($embeddingConfig['model_name'] === $model['name']) ? ' (Configured Embedding)' : '';
                    fwrite(STDOUT, sprintf("    - %s%s%s" . PHP_EOL, $model['name'], $llmStatus, $embeddingStatus));
                }
            }
        } else {
            fwrite(STDERR, "  Error: " . $ollamaStatus['error'] . PHP_EOL);
        }


        fwrite(STDOUT, "\n--- End Status Report ---" . PHP_EOL);

        return 0;
    }

    private function checkOllamaServer(string $baseUrl): array
    {
        if (empty($baseUrl)) {
            return ['reachable' => false, 'error' => 'Ollama base URL not configured.'];
        }
        $url = $baseUrl; // Simple ping to base URL is usually enough to check reachability
        $options = [
            'http' => [
                'method'  => 'GET',
                'timeout' => 5, // Short timeout for ping
            ],
        ];
        $context  = stream_context_create($options);

        // Suppress errors with @ and handle manually
        $result = @file_get_contents($url, false, $context);

        if ($result === false) {
            $error = error_get_last();
            return ['reachable' => false, 'error' => $error['message'] ?? 'Unknown error'];
        }
        return ['reachable' => true, 'error' => null];
    }

    private function getOllamaModels(string $baseUrl): array
    {
        if (empty($baseUrl)) {
            return [];
        }
        $url = $baseUrl . '/api/tags';
        $options = [
            'http' => [
                'method'  => 'GET',
                'timeout' => 10,
            ],
        ];
        $context  = stream_context_create($options);

        $result = @file_get_contents($url, false, $context);
        if ($result === false) {
            $error = error_get_last();
            fwrite(STDERR, "  Error fetching Ollama models: " . ($error['message'] ?? 'Unknown error') . PHP_EOL);
            return [];
        }

        $response = json_decode($result, true);
        if (json_last_error() !== JSON_ERROR_NONE || !isset($response['models'])) {
            fwrite(STDERR, "  Error decoding Ollama models response: " . json_last_error_msg() . PHP_EOL);
            return [];
        }

        $models = [];
        foreach ($response['models'] as $modelData) {
            $models[] = [
                'name' => $modelData['name'],
                'size' => $modelData['size'],
                'digest' => $modelData['digest'],
            ];
        }
        return $models;
    }
}
