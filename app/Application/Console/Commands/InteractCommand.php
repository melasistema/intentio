<?php

declare(strict_types=1);

namespace Intentio\Application\Console\Commands;

use Intentio\Domain\Cognitive\CognitiveEngine;
use Intentio\Domain\Cognitive\PromptResolver; // New dependency
use Intentio\Domain\Space\Space;
use Intentio\Domain\Space\SpaceFactory;
use Intentio\Infrastructure\Filesystem\LocalSpaceRepository;
use Intentio\Infrastructure\Filesystem\LocalBlueprintRepository;
use Intentio\Infrastructure\Filesystem\FileCopier;
use Intentio\Shared\Exceptions\IntentioException;

final class InteractCommand implements CommandInterface
{
    private const NAME = 'interact';
    private const DESCRIPTION = 'Initiates an interactive session with a cognitive space.';

    private PromptResolver $promptResolver; // To be injected via constructor

    public function __construct(
        private readonly CognitiveEngine $cognitiveEngine,
        private readonly LocalSpaceRepository $spaceRepository,
        private readonly SpaceFactory $spaceFactory,
        private readonly LocalBlueprintRepository $blueprintRepository,
        private readonly FileCopier $fileCopier,
        private readonly array $config,
        PromptResolver $promptResolver // Inject PromptResolver
    ) {
        $this->promptResolver = $promptResolver;
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
        try {
            $spaceName = $options['space'] ?? null;
            $space = $this->selectOrCreateSpace($spaceName);

            // Read manifest.md for additional configuration (e.g., default prompt template)
            $manifestConfig = $this->readSpaceManifest($space);
            $selectedPromptKey = $options['prompt_key'] // Option has highest precedence
                                ?? $manifestConfig['default_prompt'] // Corrected key from manifest
                                ?? $this->config['llm']['default_prompt_template_name'] // Fallback to main config
                                ?? 'default'; // Final fallback

            // Allow user to select prompt if not specified, or validate initial key
            $resolvedPrompt = $this->selectPromptTemplate($space, $selectedPromptKey);
            $currentPromptKey = $resolvedPrompt['key'];
            $currentPromptContent = $resolvedPrompt['content'];
            $currentPromptInstruction = $resolvedPrompt['instruction'];
            $currentPromptContextFiles = $resolvedPrompt['context_files'];


            // Check if ingestion is needed/up-to-date and prompt/perform
            $this->ensureSpaceIngested($space);

            fwrite(STDOUT, "\n--- Interactive Session with '{$space->getName()}' ---" . PHP_EOL);
            fwrite(STDOUT, "Type your query and press Enter. Type 'exit' to end the session." . PHP_EOL);
            fwrite(STDOUT, "Use 'switch_prompt' to change the active prompt template." . PHP_EOL);
            fwrite(STDOUT, "(Active Prompt Template: {$currentPromptKey})" . PHP_EOL);
            fwrite(STDOUT, "Instruction: {$currentPromptInstruction}" . PHP_EOL);

            while (true) {
                fwrite(STDOUT, "\n> ");
                $rawQuery = fgets(STDIN);
                $query = trim($rawQuery);

                // DEBUG LINE:
                fwrite(STDOUT, "DEBUG: Raw input: [" . rtrim($rawQuery, "\n") . "]\n");
                fwrite(STDOUT, "DEBUG: Trimmed input: [" . $query . "]\n");
                // END DEBUG LINE


                if (strtolower($query) === 'exit') {
                    fwrite(STDOUT, "Ending interactive session." . PHP_EOL);
                    break;
                }
                
                if (strtolower($query) === 'switch_prompt') {
                    $resolvedPrompt = $this->selectPromptTemplate($space, null); // Force selection
                    $currentPromptKey = $resolvedPrompt['key'];
                    $currentPromptContent = $resolvedPrompt['content'];
                    $currentPromptInstruction = $resolvedPrompt['instruction'];
                    $currentPromptContextFiles = $resolvedPrompt['context_files'];
                    fwrite(STDOUT, "(Active Prompt Template: {$currentPromptKey})" . PHP_EOL);
                    fwrite(STDOUT, "Instruction: {$currentPromptInstruction}" . PHP_EOL);
                    continue;
                }

                if (empty($query)) {
                    continue;
                }

                // Options for chat method
                $chatOptions = $options;
                $chatOptions['prompt_key'] = $currentPromptKey; // Original key
                $chatOptions['prompt_instruction'] = $currentPromptInstruction; // Pass instruction separately
                $chatOptions['prompt_content'] = $currentPromptContent;     // Pass full prompt content
                $chatOptions['context_files'] = $currentPromptContextFiles; // Pass identified context files

                $response = $this->cognitiveEngine->chat($space, $query, $chatOptions);

                fwrite(STDOUT, "\n--- INTENTIO Response ---" . PHP_EOL);
                fwrite(STDOUT, $response . PHP_EOL);
                fwrite(STDOUT, "-------------------------\n" . PHP_EOL);
            }

            return 0;
        } catch (IntentioException $e) {
            fwrite(STDERR, "Error: " . $e->getMessage() . PHP_EOL);
            return 1;
        } catch (\Throwable $e) {
            fwrite(STDERR, "An unexpected error occurred during interactive session: " . $e->getMessage() . PHP_EOL);
            return 1;
        }
    }

    private function selectOrCreateSpace(?string $spaceName): Space
    {
        // ... (existing code, but update createSpaceFromName call) ...
        if ($spaceName !== null) {
            $space = $this->spaceRepository->findByName($spaceName);
            if ($space === null) {
                fwrite(STDOUT, "Cognitive space '{$spaceName}' not found. Would you like to create it? (yes/no): ");
                $confirm = trim(fgets(STDIN));
                if (strtolower($confirm) === 'yes') {
                    return $this->createSpaceInteractive($spaceName);
                } else {
                    throw new IntentioException("Space '{$spaceName}' not found and creation cancelled.");
                }
            }
            return $space;
        }

        $availableSpaces = $this->spaceRepository->findAll();
        if (empty($availableSpaces)) {
            fwrite(STDOUT, "No cognitive spaces found. Let's create one first." . PHP_EOL);
            return $this->createSpaceInteractive(null); // Pass null to trigger interactive naming
        }

        fwrite(STDOUT, "Available Cognitive Spaces:" . PHP_EOL);
        foreach ($availableSpaces as $i => $s) {
            fwrite(STDOUT, sprintf("  %d. %s" . PHP_EOL, $i + 1, $s->getName()));
        }

        while (true) {
            fwrite(STDOUT, "Enter the number of the space to interact with, or 'new' to create one: ");
            $choice = trim(fgets(STDIN));

            if (strtolower($choice) === 'new') {
                return $this->createSpaceInteractive(null); // Pass null for interactive naming
            }

            if (!ctype_digit($choice)) {
                fwrite(STDERR, "Invalid choice. Please enter a number or 'new'." . PHP_EOL);
                continue;
            }

            $choice = (int) $choice;
            if ($choice < 1 || $choice > count($availableSpaces)) {
                fwrite(STDERR, "Invalid choice. Please choose from the list." . PHP_EOL);
                continue;
            }
            return $availableSpaces[$choice - 1];
        }
    }

    private function createSpaceInteractive(?string $predefinedName = null): Space
    {
        $blueprints = $this->blueprintRepository->findAll();
        if (empty($blueprints)) {
            throw new IntentioException("No blueprints available to create a new space.");
        }

        fwrite(STDOUT, "\nAvailable Blueprints for new space:" . PHP_EOL);
        foreach ($blueprints as $i => $blueprint) {
            fwrite(STDOUT, sprintf("  %d. %s" . PHP_EOL, $i + 1, $blueprint->getName()));
        }

        $chosenBlueprint = null;
        while (true) {
            fwrite(STDOUT, "Enter the number of the blueprint to use: ");
            $choice = trim(fgets(STDIN));
            if (!ctype_digit($choice) || (int)$choice < 1 || (int)$choice > count($blueprints)) {
                fwrite(STDERR, "Invalid choice." . PHP_EOL);
                continue;
            }
            $chosenBlueprint = $blueprints[(int)$choice - 1];
            break;
        }

        $spaceName = $predefinedName;
        if ($spaceName === null) {
            while (true) {
                fwrite(STDOUT, "Enter a name for your new space (e.g., 'my_{$chosenBlueprint->getName()}_space'): ");
                $inputName = trim(fgets(STDIN));
                if (empty($inputName) || !preg_match('/^[a-zA-Z0-9_\-]+$/', $inputName)) {
                    fwrite(STDERR, "Invalid space name. Use alphanumeric, hyphens, and underscores." . PHP_EOL);
                    continue;
                }
                $spaceName = $inputName;
                break;
            }
        }

        if ($this->spaceRepository->exists($spaceName)) {
            throw new IntentioException("A space named '{$spaceName}' already exists. Please choose another name.");
        }

        $spacePath = ($this->config['spaces_base_path'] ?? __DIR__ . '/../../../spaces') . '/' . $spaceName;

        $this->fileCopier->copyDirectory(
            $chosenBlueprint->getPath(),
            $spacePath,
            [] // Now copies all files, including manifest.md and README.md
        );

        $space = $this->spaceFactory->createSpace($spaceName, $spacePath);
        $this->spaceRepository->save($space);

        fwrite(STDOUT, "New space '{$spaceName}' created successfully from blueprint '{$chosenBlueprint->getName()}'." . PHP_EOL);
        return $space;
    }


    private function readSpaceManifest(Space $space): array
    {
        $manifestPath = $space->getPath() . '/manifest.md';
        if (!file_exists($manifestPath) || !is_readable($manifestPath)) {
            return []; // No warning for missing manifest
        }

        $content = file_get_contents($manifestPath);
        if ($content === false) {
            return [];
        }

        $config = [];
        $lines = explode("\n", $content);
        foreach ($lines as $line) {
            $trimmedLine = trim($line);
            if (empty($trimmedLine) || str_starts_with($trimmedLine, '#')) {
                // Stop reading on first blank line or comment after finding some config.
                // Or skip initial blank lines/comments.
                if (!empty($config) && (empty($trimmedLine) || str_starts_with($trimmedLine, '#'))) {
                    break;
                }
                continue;
            }

            if (str_contains($trimmedLine, ':')) {
                list($key, $value) = explode(':', $trimmedLine, 2);
                $config[trim($key)] = trim($value);
            } else {
                // If a line doesn't conform to key: value and we already parsed some config,
                // assume config block is over.
                if (!empty($config)) {
                    break;
                }
            }
        }
        return $config;
    }

    private function selectPromptTemplate(Space $space, ?string $initialPromptKey = null): array
    {
        $availablePromptKeys = $this->promptResolver->listPromptKeys($space);

        if (empty($availablePromptKeys)) {
            throw new IntentioException("No prompt templates found in space '{$space->getName()}'.");
        }

        // Try to resolve initial key without user interaction
        if ($initialPromptKey !== null && in_array($initialPromptKey, $availablePromptKeys)) {
            $resolved = $this->promptResolver->resolve($space, $initialPromptKey);
            return array_merge(['key' => $initialPromptKey], $resolved);
        }
        
        // No auto-fallback to "default" if initialPromptKey is null. Always ask.

        fwrite(STDOUT, "\nAvailable Prompt Templates for '{$space->getName()}':" . PHP_EOL);
        foreach ($availablePromptKeys as $i => $key) {
            fwrite(STDOUT, sprintf("  %d. %s" . PHP_EOL, $i + 1, $key));
        }

        while (true) {
            fwrite(STDOUT, "Enter the number of the prompt template to use: ");
            $choice = trim(fgets(STDIN));

            if (!ctype_digit($choice)) {
                fwrite(STDERR, "Invalid choice. Please enter a number." . PHP_EOL);
                continue;
            }

            $choice = (int) $choice;
            if ($choice < 1 || $choice > count($availablePromptKeys)) { // Corrected bounds check
                fwrite(STDERR, "Invalid choice. Please choose from the list." . PHP_EOL);
                continue;
            }

            $selectedKey = $availablePromptKeys[$choice - 1];
            $resolved = $this->promptResolver->resolve($space, $selectedKey);
            return array_merge(['key' => $selectedKey], $resolved);
        }
    }

    private function ensureSpaceIngested(Space $space): void
    {
        // For now, a very basic check: does the vector store exist?
        // This could be made more sophisticated by checking file modification dates etc.
        $vectorStoreDbPath = $space->getPath() . '/.intentio_store/' . md5($space->getPath()) . '.sqlite'; // Consistent with SQLiteVectorStore
        
        if (!file_exists($vectorStoreDbPath)) {
            fwrite(STDOUT, "Ingested data not found for space '{$space->getName()}'. Initiating ingestion..." . PHP_EOL);
            $this->cognitiveEngine->ingest($space);
            fwrite(STDOUT, "Ingestion complete." . PHP_EOL);
        } else {
            fwrite(STDOUT, "Ingested data found for space '{$space->getName()}'." . PHP_EOL);
        }
    }
}
