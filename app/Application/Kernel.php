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
use Intentio\Infrastructure\LLM\OmlxChatAdapter;
use Intentio\Infrastructure\Embeddings\OmlxEmbeddingAdapter;
use Intentio\Infrastructure\Omlx\OmlxClient;
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
            $omlxClient = new OmlxClient($this->config['omlx'] ?? []);

            $llmAdapter = new OmlxChatAdapter(
                $omlxClient,
                $this->config['llm']['model_name'] ?? '',
                $this->config['llm']['options'] ?? []
            );
            $embeddingModel = $this->config['embedding']['model_name'] ?? '';
            $embeddingAdapter = new OmlxEmbeddingAdapter($omlxClient, $embeddingModel);
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
                $vectorStore,
                $embeddingModel,
                $this->config['embedding']['document_prefix'] ?? ''
            );
            $retrievalService = new RetrievalService(
                $embeddingAdapter,
                $vectorStore,
                $this->config['retrieval']['limit'] ?? 5,
                $this->config['retrieval']['min_score'] ?? 0.5,
                $this->config['embedding']['query_prefix'] ?? ''
            );
            $promptResolver = new PromptResolver();

            $cognitiveEngine = new CognitiveEngine(
                $llmAdapter,
                $ingestionService,
                $retrievalService,
                $vectorStore,
                $ollamaImageRenderer,
                $this->config['llm']['context_window'] ?? 32768
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
                $omlxClient,
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
