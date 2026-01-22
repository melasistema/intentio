<?php

declare(strict_types=1);

namespace Intentio\Command;

use Intentio\Cli\Input;
use Intentio\Cli\Output;
use Intentio\Knowledge\Space; // Added this use statement

/**
 * Handles the 'status' command, displaying an overview of the INTENTIO setup.
 *
 * This command provides information about configured paths, available knowledge
 * spaces, and the connectivity status of the Ollama server and models.
 */
final class StatusCommand implements CommandInterface
{
    public function __construct(
        private readonly Input $input,
        private readonly array $config,
        private readonly string $knowledgeBasePath // Changed from Space to string
    ) {
    }

    public function execute(): int
    {
        Output::writeln("--- INTENTIO System Status ---");
        Output::writeln(sprintf("Application Name: %s", $this->config['app_name'] ?? 'INTENTIO'));
        Output::writeln(sprintf("PHP Version: %s", PHP_VERSION));
        Output::writeln("");

        // 1. Knowledge Base Configuration
        Output::writeln("--- Knowledge Base ---");
        $spacesBasePath = $this->config['spaces_base_path'];
        Output::writeln(sprintf("Configured Path: %s", $knowledgeBasePath));

        $availableSpaces = Space::getAvailableSpaces($knowledgeBasePath); // Updated call
        if (empty($availableSpaces)) {
            Output::writeln("Available Spaces: None found.");
        } else {
            Output::writeln("Available Spaces:");
            foreach ($availableSpaces as $spaceName) {
                Output::writeln(sprintf("  - %s", $spaceName));
            }
        }
        Output::writeln("");

        // 2. Ollama Server Status
        Output::writeln("--- Ollama Server ---");
        $ollamaBaseUrl = $this->config['ollama']['base_url'];
        Output::writeln(sprintf("Configured URL: %s", $ollamaBaseUrl));

        $ollamaStatus = $this->checkOllamaStatus($ollamaBaseUrl);
        if ($ollamaStatus) {
            Output::writeln("Status: Connected.");
            Output::writeln("Installed Models:");
            $models = $this->getOllamaModels($ollamaBaseUrl);
            if (!empty($models)) {
                foreach ($models as $model) {
                    Output::writeln(sprintf("  - %s (%s)", $model['name'], $model['size']));
                }
            } else {
                Output::writeln("  No models found. Please pull models (e.g., 'ollama pull llama3.1').");
            }
        } else {
            Output::writeln("Status: Disconnected or not reachable.");
            Output::error("Please ensure Ollama is running at {$ollamaBaseUrl}.");
        }
        Output::writeln("");

        // 3. Configured Models
        Output::writeln("--- Configured Models ---");
        Output::writeln(sprintf("LLM (Interpreter): %s", $this->config['interpreter']['model_name']));
        Output::writeln(sprintf("Embedding Model: %s", $this->config['embedding']['model_name']));
        Output::writeln("----------------------------");

        return 0;
    }

    /**
     * Checks if the Ollama server is reachable.
     */
    private function checkOllamaStatus(string $baseUrl): bool
    {
        // Use stream_context_create for a quick head request or similar
        // For simplicity, we'll try to fetch a known endpoint.
        $url = $baseUrl . '/api/tags'; // Endpoint to list models, good for status check
        $options = ['http' => ['method' => 'HEAD', 'timeout' => 2]];
        $context = stream_context_create($options);
        // Suppress errors as we're just checking reachability
        if (@file_get_contents($url, false, $context) !== false) {
            return true;
        }

        // Alternative for API status if HEAD fails or doesn't confirm it's working
        // Try a /api/version if available or a simple GET to base_url
        $options = ['http' => ['method' => 'GET', 'timeout' => 2, 'ignore_errors' => true]];
        $context = stream_context_create($options);
        $response = @file_get_contents($baseUrl, false, $context);
        return $response !== false;
    }

    /**
     * Retrieves a list of models installed in Ollama.
     */
    private function getOllamaModels(string $baseUrl): array
    {
        $url = $baseUrl . '/api/tags';
        $options = ['http' => ['method' => 'GET', 'timeout' => 5, 'ignore_errors' => true]];
        $context = stream_context_create($options);
        $response = @file_get_contents($url, false, $context);

        if ($response === false) {
            return []; // Cannot connect or error
        }

        $data = json_decode($response, true);
        if (json_last_error() !== JSON_ERROR_NONE || !isset($data['models'])) {
            return []; // Invalid response
        }

        $models = [];
        foreach ($data['models'] as $model) {
            $models[] = [
                'name' => $model['name'],
                'size' => $this->formatBytes($model['size']),
            ];
        }
        return $models;
    }

    /**
     * Formats bytes into a human-readable string.
     */
    private function formatBytes(int $bytes, int $precision = 2): string
    {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        $bytes = max($bytes, 0);
        $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
        $pow = min($pow, count($units) - 1);

        $bytes /= (1 << (10 * $pow));

        return round($bytes, $precision) . ' ' . $units[$pow];
    }
}
