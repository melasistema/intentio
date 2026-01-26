<?php

declare(strict_types=1);

namespace Intentio\Application;

use Intentio\Application\Console\ConsoleApplication;
use Intentio\Application\Console\Commands\ChatCommand;
use Intentio\Application\Console\Commands\ClearCommand;
use Intentio\Application\Console\Commands\IngestCommand;
use Intentio\Application\Console\Commands\InitCommand;
use Intentio\Application\Console\Commands\InteractCommand; // Uncommented
use Intentio\Application\Console\Commands\StatusCommand;   // Uncommented
use Intentio\Application\Console\Commands\SpacesCommand;

// Placeholder for Infrastructure dependencies that will be injected
use Intentio\Infrastructure\Filesystem\FileProcessor;
use Intentio\Infrastructure\LLM\OllamaAdapter;
use Intentio\Infrastructure\Embeddings\LocalEmbeddingAdapter;
use Intentio\Infrastructure\Filesystem\LocalSpaceRepository;
use Intentio\Infrastructure\Filesystem\LocalBlueprintRepository;
use Intentio\Infrastructure\Filesystem\FileCopier;
use Intentio\Infrastructure\Storage\SQLiteVectorStore; // New
use Intentio\Domain\Cognitive\VectorStoreInterface; // New

use Intentio\Domain\Space\SpaceFactory;
use Intentio\Domain\Cognitive\IngestionService;
use Intentio\Domain\Cognitive\RetrievalService;
use Intentio\Domain\Cognitive\PromptResolver;
use Intentio\Domain\Cognitive\CognitiveEngine;
use Intentio\Shared\Exceptions\IntentioException;

final class Kernel
{
    private array $config;

    public function __construct(array $config)
    {
        $this->config = $config;
    }

    public function run(): int
    {
        try {
            // Infrastructure dependencies
            $ollamaConfig = $this->config['ollama'] ?? [];
            $llmModel = $this->config['llm']['model_name'] ?? 'llama2';
            $llmOptions = $this->config['llm']['options'] ?? [];

            $ollamaAdapter = new OllamaAdapter(
                $ollamaConfig,
                $llmModel,
                $llmOptions
            );
            $embeddingAdapter = new LocalEmbeddingAdapter(
                $ollamaConfig, // Pass ollamaConfig here
                $this->config['embedding']['model_name'] ?? 'nomic-embed-text'
            );
            $fileCopier = new FileCopier(); // Needs proper construction/config
            $fileProcessor = new FileProcessor(); // New
            $localSpaceRepository = new LocalSpaceRepository($this->config['spaces_base_path'] ?? __DIR__ . '/../../../spaces');
            $localBlueprintRepository = new LocalBlueprintRepository($this->config['blueprints_base_path'] ?? __DIR__ . '/../../../packages');
            $vectorStore = new SQLiteVectorStore(); // New - no constructor args needed anymore

            // Domain dependencies
            $spaceFactory = new SpaceFactory(); // Needs construction logic
            $ingestionService = new IngestionService(
                $fileProcessor,
                $embeddingAdapter,
                $vectorStore
            );
            $retrievalService = new RetrievalService(
                $embeddingAdapter,
                $vectorStore
            );
            $promptResolver = new PromptResolver(); // Needs construction logic

            $cognitiveEngine = new CognitiveEngine(
                $ollamaAdapter,
                $embeddingAdapter,
                $ingestionService,
                $retrievalService,
                $promptResolver,
                $vectorStore // New
            );

            $consoleApplication = new ConsoleApplication($this->config['app_name'] ?? 'INTENTIO', '0.1.0');

            // Register Commands
            $consoleApplication->addCommand(new InitCommand(
                $spaceFactory,
                $localSpaceRepository,
                $localBlueprintRepository,
                $fileCopier,
                $this->config // Pass config for paths etc.
            ));
            $consoleApplication->addCommand(new IngestCommand($cognitiveEngine, $localSpaceRepository));
            $consoleApplication->addCommand(new ChatCommand($cognitiveEngine, $localSpaceRepository));
            $consoleApplication->addCommand(new ClearCommand($cognitiveEngine, $localSpaceRepository));
            $consoleApplication->addCommand(new InteractCommand(
                $cognitiveEngine,
                $localSpaceRepository,
                $spaceFactory,
                $localBlueprintRepository,
                $fileCopier,
                $this->config,
                $promptResolver // Added promptResolver
            ));
            $consoleApplication->addCommand(new StatusCommand( // Uncommented and instantiated
                $localSpaceRepository,
                $this->config
            ));
            $consoleApplication->addCommand(new SpacesCommand($localSpaceRepository)); // New command to list spaces

            return $consoleApplication->run();
        } catch (IntentioException $e) {
            // This is a placeholder for a more sophisticated error handler
            fwrite(STDERR, "Error: " . $e->getMessage() . PHP_EOL);
            return 1;
        } catch (\Throwable $e) {
            fwrite(STDERR, "An unexpected error occurred: " . $e->getMessage() . PHP_EOL);
            return 1;
        }
    }
}
