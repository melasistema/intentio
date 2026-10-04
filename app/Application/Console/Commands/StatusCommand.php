<?php

declare(strict_types=1);

namespace Intentio\Application\Console\Commands;

use Intentio\Infrastructure\Filesystem\LocalSpaceRepository;
use Intentio\Infrastructure\Omlx\OmlxClient;
use Intentio\Shared\Exceptions\IntentioException;

final class StatusCommand implements CommandInterface
{
    private const NAME = 'status';
    private const DESCRIPTION = 'Displays the current INTENTIO system status and configuration.';

    public function __construct(
        private readonly LocalSpaceRepository $spaceRepository,
        private readonly OmlxClient $omlxClient,
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

        // 3. oMLX Configuration
        $omlxConfig = $this->config['omlx'] ?? [];
        fwrite(STDOUT, "\noMLX Configuration:" . PHP_EOL);
        fwrite(STDOUT, sprintf("  Base URL: %s" . PHP_EOL, $this->omlxClient->getBaseUrl()));
        fwrite(STDOUT, sprintf("  API Key: %s" . PHP_EOL, ($omlxConfig['api_key'] ?? '') === '' ? 'not set' : 'set'));
        fwrite(STDOUT, sprintf("  Timeout: %d seconds" . PHP_EOL, $omlxConfig['timeout'] ?? 120));

        // 4. LLM Configuration
        $llmConfig = $this->config['llm'] ?? [];
        fwrite(STDOUT, "\nLLM Configuration:" . PHP_EOL);
        fwrite(STDOUT, sprintf("  Model Name: %s" . PHP_EOL, $llmConfig['model_name'] ?? 'N/A'));
        fwrite(STDOUT, sprintf("  Context Window: %s tokens" . PHP_EOL, $llmConfig['context_window'] ?? 'N/A'));
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

        // 7. oMLX Server Status & Model Availability
        fwrite(STDOUT, "\noMLX Server Status:" . PHP_EOL);
        try {
            $models = $this->omlxClient->get('/v1/models')['data'] ?? [];
            fwrite(STDOUT, "  Server Reachable: Yes" . PHP_EOL);
            fwrite(STDOUT, "  Available Models on Server:" . PHP_EOL);

            $configured = [
                'LLM' => $llmConfig['model_name'] ?? '',
                'Embedding' => $embeddingConfig['model_name'] ?? '',
            ];
            $available = [];
            foreach ($models as $model) {
                $available[] = $model['id'];
                $line = "    - " . $model['id'];
                foreach (array_keys($configured, $model['id'], true) as $role) {
                    $line .= " (Configured {$role})";
                }
                fwrite(STDOUT, $line . PHP_EOL);
            }

            foreach ($configured as $role => $name) {
                if (!in_array($name, $available, true)) {
                    fwrite(STDOUT, "  Missing: the configured {$role} model '{$name}' is not on the server." . PHP_EOL);
                }
            }
        } catch (IntentioException $e) {
            fwrite(STDOUT, "  Server Reachable: No" . PHP_EOL);
            fwrite(STDERR, "  Error: " . $e->getMessage() . PHP_EOL);
        }

        fwrite(STDOUT, "\n--- End Status Report ---" . PHP_EOL);

        return 0;
    }
}
