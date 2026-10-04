<?php

declare(strict_types=1);

namespace Intentio\Application;

use Intentio\Application\Console\Commands\RenderCommand;
use Intentio\Application\Console\ConsoleApplication;
use Intentio\Application\Console\Commands\ChatCommand;
use Intentio\Application\Console\Commands\ClearCommand;
use Intentio\Application\Console\Commands\IngestCommand;
use Intentio\Application\Console\Commands\InitCommand;
use Intentio\Application\Console\Commands\InteractCommand;
use Intentio\Application\Console\Commands\StatusCommand;
use Intentio\Application\Console\Commands\SpacesCommand;

use Intentio\Infrastructure\Filesystem\FileProcessor;
use Intentio\Infrastructure\LLM\OllamaAdapter;
use Intentio\Infrastructure\Embeddings\LocalEmbeddingAdapter;
use Intentio\Infrastructure\Filesystem\LocalSpaceRepository;
use Intentio\Infrastructure\Filesystem\LocalBlueprintRepository;
use Intentio\Infrastructure\Filesystem\FileCopier;
use Intentio\Infrastructure\Storage\SQLiteVectorStore;
use Intentio\Infrastructure\ImageRenderer\OllamaImageRenderer;
use Intentio\Domain\Cognitive\VectorStoreInterface;
use Intentio\Domain\Model\ImageRendererInterface;

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
                $ollamaConfig,
                $this->config['embedding']['model_name'] ?? 'nomic-embed-text'
            );
            $fileCopier = new FileCopier();
            $fileProcessor = new FileProcessor();
            $localSpaceRepository = new LocalSpaceRepository($this->config['spaces_base_path'] ?? __DIR__ . '/../../../spaces');
            $localBlueprintRepository = new LocalBlueprintRepository($this->config['blueprints_base_path'] ?? __DIR__ . '/../../../packages');
            $vectorStore = new SQLiteVectorStore();
            $ollamaImageRenderer = new OllamaImageRenderer($this->config['image_renderer']);

            // Domain dependencies
            $spaceFactory = new SpaceFactory();
            $ingestionService = new IngestionService(
                $fileProcessor,
                $embeddingAdapter,
                $vectorStore
            );
            $retrievalService = new RetrievalService(
                $embeddingAdapter,
                $vectorStore
            );
            $promptResolver = new PromptResolver();

            $cognitiveEngine = new CognitiveEngine(
                $ollamaAdapter,
                $ingestionService,
                $retrievalService,
                $vectorStore,
                $ollamaImageRenderer
            );

            $consoleApplication = new ConsoleApplication($this->config['app_name'] ?? 'INTENTIO', $this->config['app_version'] ?? 'unknown');

            // Register Commands
            $consoleApplication->addCommand(new InitCommand(
                $spaceFactory,
                $localSpaceRepository,
                $localBlueprintRepository,
                $fileCopier,
                $this->config // Pass config for paths etc.
            ));
            $consoleApplication->addCommand(new IngestCommand($cognitiveEngine, $localSpaceRepository));
            $consoleApplication->addCommand(new ChatCommand($cognitiveEngine, $localSpaceRepository, $promptResolver));
            $consoleApplication->addCommand(new ClearCommand($cognitiveEngine, $localSpaceRepository));
            $consoleApplication->addCommand(new InteractCommand(
                $cognitiveEngine,
                $localSpaceRepository,
                $spaceFactory,
                $localBlueprintRepository,
                $fileCopier,
                $this->config,
                $promptResolver
            ));
            $consoleApplication->addCommand(new StatusCommand(
                $localSpaceRepository,
                $this->config
            ));
            $consoleApplication->addCommand(new SpacesCommand($localSpaceRepository));
            $consoleApplication->addCommand(new RenderCommand($cognitiveEngine, $localSpaceRepository));

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
