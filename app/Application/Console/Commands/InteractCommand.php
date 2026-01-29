<?php

declare(strict_types=1);

namespace Intentio\Application\Console\Commands;

use Intentio\Domain\Cognitive\CognitiveEngine;
use Intentio\Domain\Cognitive\PromptResolver;
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

    private PromptResolver $promptResolver;

    public function __construct(
        private readonly CognitiveEngine $cognitiveEngine,
        private readonly LocalSpaceRepository $spaceRepository,
        private readonly SpaceFactory $spaceFactory,
        private readonly LocalBlueprintRepository $blueprintRepository,
        private readonly FileCopier $fileCopier,
        private readonly array $config,
        PromptResolver $promptResolver
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

            $manifestPath = $space->getPath() . '/manifest.md';
            $manifestConfig = [];
            if (file_exists($manifestPath)) {
                $manifestConfig = $this->parseManifest(file_get_contents($manifestPath));
            }

            $selectedPromptKey = $options['prompt_key']
                                ?? $manifestConfig['default_prompt']
                                ?? $this->config['llm']['default_prompt_template_name']
                                ?? 'default';

            $resolvedPrompt = $this->selectPromptTemplate($space, $selectedPromptKey);
            $currentPromptKey = $resolvedPrompt['key'];
            $currentPromptContent = $resolvedPrompt['content'];
            $currentPromptInstruction = $resolvedPrompt['instruction'];
            $currentPromptContextFiles = $resolvedPrompt['context_files'];

            $this->ensureSpaceIngested($space);

            fwrite(STDOUT, "\n--- Interactive Session with '{$space->getName()}' ---" . PHP_EOL);
            fwrite(STDOUT, "Type your query and press Enter. Type 'exit' to end the session." . PHP_EOL);
            fwrite(STDOUT, "Use 'switch_prompt' to change the active prompt template." . PHP_EOL);
            fwrite(STDOUT, "(Active Prompt Template: {$currentPromptKey})" . PHP_EOL);
            fwrite(STDOUT, "Instruction: {$currentPromptInstruction}" . PHP_EOL);

            while (true) {
                fwrite(STDOUT, "\n> ");
                $query = trim(fgets(STDIN));

                if (strtolower($query) === 'exit') {
                    fwrite(STDOUT, "Ending interactive session." . PHP_EOL);
                    break;
                }

                if (strtolower($query) === 'switch_prompt') {
                    $resolvedPrompt = $this->selectPromptTemplate($space, null);
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

                $chatOptions = $options;
                $chatOptions['prompt_key'] = $currentPromptKey;
                $chatOptions['prompt_instruction'] = $currentPromptInstruction;
                $chatOptions['prompt_content'] = $currentPromptContent;
                $chatOptions['context_files'] = $currentPromptContextFiles;

                $response = $this->cognitiveEngine->chat($space, $query, $chatOptions);

                fwrite(STDOUT, "\n--- INTENTIO Response ---" . PHP_EOL);
                fwrite(STDOUT, $response . PHP_EOL);
                fwrite(STDOUT, "-------------------------\n" . PHP_EOL);

                $actions = $manifestConfig['actions'] ?? [];
                $actionConfig = null;
                // Find which action corresponds to the prompt key we just used
                foreach ($actions as $action) {
                    if (isset($action['template']) && $action['template'] === $currentPromptKey) {
                        $actionConfig = $action;
                        break;
                    }
                }

                // As a fallback for design_intent, which isn't a formal action, we create a pseudo-action
                if ($actionConfig === null
                    && $currentPromptKey === ($manifestConfig['default_prompt'] ?? null)
                    && ($manifestConfig['default_prompt'] ?? null) !== null
                ) {
                    $actionConfig = ['updates_context' => 'lastGeneratedManifest'];
                }

                $contextVar = $actionConfig['updates_context'] ?? null;
                if ($contextVar) {
                    $contextFilePath = $space->getPath() . '/' . $contextVar . '.md';
                    file_put_contents($contextFilePath, $response);
                    fwrite(STDOUT, "(✓ Context '{$contextVar}' saved.)" . PHP_EOL);

                    $renderAction = null;
                    foreach ($actions as $action) {
                        if (isset($action['handler']) && $action['handler'] === 'image_renderer') {
                            $renderAction = $action;
                            break;
                        }
                    }

                    if ($renderAction) {
                        while (true) {
                            fwrite(STDOUT, "\nRender this manifest? (yes/no/refine): ");
                            $choice = trim(fgets(STDIN));

                            if (in_array(strtolower($choice), ['yes', 'y'])) {
                                $requiredContext = $renderAction['context_required'] ?? null;
                                if (!$requiredContext) {
                                     fwrite(STDERR, "Error: The render action in the manifest does not specify a 'context_required'." . PHP_EOL);
                                     break;
                                }
                                $requiredContextPath = $space->getPath() . '/' . $requiredContext . '.md';
                                if (!file_exists($requiredContextPath)) {
                                    fwrite(STDERR, "Error: Required context file '{$requiredContext}.md' not found." . PHP_EOL);
                                    break;
                                }

                                $manifestToRender = file_get_contents($requiredContextPath);

                                // Extract master prompt using regex from the manifest content
                                if (preg_match('/<<<RENDER_PROMPT>>>(.*?)<<<END_RENDER_PROMPT>>>/s', $manifestToRender, $matches)) {
                                    $masterPrompt = trim($matches[1]);
                                } else {
                                    $masterPrompt = '';
                                }

                                if (empty($masterPrompt)) {
                                    fwrite(STDERR, "Error: Could not extract master render prompt from the manifest using the expected tags." . PHP_EOL);
                                    break;
                                }

                                fwrite(STDOUT, "DEBUG: Extracted master prompt: '" . $masterPrompt . "'" . PHP_EOL);
                                $this->cognitiveEngine->render($space, $masterPrompt, []);
                                break;

                            } elseif (in_array(strtolower($choice), ['no', 'n'])) {
                                break;
                            } elseif (in_array(strtolower($choice), ['refine', 'r'])) {
                                fwrite(STDOUT, "Please provide your refinement instructions." . PHP_EOL);
                                break;
                            } else {
                                fwrite(STDERR, "Invalid choice. Please enter 'yes', 'no', or 'refine'." . PHP_EOL);
                            }
                        }
                    }
                }
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

    private function parseManifest(string $content): array
    {
        $config = [];
        $lines = explode("\n", $content);
        $inActionsBlock = false;
        $currentActionName = null;

        foreach ($lines as $line) {
            $trimmedLine = rtrim($line);

            if (rtrim($trimmedLine) === 'actions:') {
                $inActionsBlock = true;
                $config['actions'] = [];
                continue;
            }

            if ($inActionsBlock) {
                if (strlen($trimmedLine) > 0 && !str_starts_with($trimmedLine, ' ')) {
                    $inActionsBlock = false;
                    $currentActionName = null;
                } else {
                    if (preg_match('/^  (\w+):$/', $trimmedLine, $matches)) {
                        $currentActionName = $matches[1];
                        $config['actions'][$currentActionName] = [];
                        continue;
                    }
                    if ($currentActionName && preg_match('/^    (\w+): (.*)$/', $trimmedLine, $matches)) {
                        $key = $matches[1];
                        $value = trim($matches[2]);
                        // Strip quotes from string values
                        if (str_starts_with($value, '"') && str_ends_with($value, '"')) {
                            $value = substr($value, 1, -1);
                        }
                        // Basic type conversion
                        if ($value === 'null') $value = null;
                        elseif ($value === 'true') $value = true;
                        elseif ($value === 'false') $value = false;
                        $config['actions'][$currentActionName][$key] = $value;
                    }
                    continue;
                }
            }

            if (str_contains($trimmedLine, ':')) {
                list($key, $value) = explode(':', $trimmedLine, 2);
                if (trim($key) !== 'actions') {
                    $value = trim($value);
                    // Strip quotes from string values
                    if (str_starts_with($value, '"') && str_ends_with($value, '"')) {
                        $value = substr($value, 1, -1);
                    }
                    $config[trim($key)] = $value;
                }
            }
        }
        return $config;
    }

    private function selectOrCreateSpace(?string $spaceName): Space
    {
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
            throw new IntentioException("No cognitive spaces found. Let's create one first.");
        }

        fwrite(STDOUT, "Available Cognitive Spaces:" . PHP_EOL);
        foreach ($availableSpaces as $i => $s) {
            fwrite(STDOUT, sprintf("  %d. %s" . PHP_EOL, $i + 1, $s->getName()));
        }

        while (true) {
            fwrite(STDOUT, "Enter the number of the space to interact with, or 'new' to create one: ");
            $choice = trim(fgets(STDIN));

            if (strtolower($choice) === 'new') {
                return $this->createSpaceInteractive(null);
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
            []
        );

        $space = $this->spaceFactory->createSpace($spaceName, $spacePath);
        $this->spaceRepository->save($space);

        fwrite(STDOUT, "New space '{$spaceName}' created successfully from blueprint '{$chosenBlueprint->getName()}'." . PHP_EOL);
        return $space;
    }

    private function selectPromptTemplate(Space $space, ?string $initialPromptKey = null): array
    {
        $availablePromptKeys = $this->promptResolver->listPromptKeys($space);

        if (empty($availablePromptKeys)) {
            throw new IntentioException("No prompt templates found in space '{$space->getName()}'.");
        }

        if ($initialPromptKey !== null && in_array($initialPromptKey, $availablePromptKeys)) {
            $resolved = $this->promptResolver->resolve($space, $initialPromptKey);
            return array_merge(['key' => $initialPromptKey], $resolved);
        }
        
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
            if ($choice < 1 || $choice > count($availablePromptKeys)) {
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
        $vectorStoreDbPath = $space->getPath() . '/.intentio_store/' . md5($space->getPath()) . '.sqlite';
        
        if (!file_exists($vectorStoreDbPath)) {
            fwrite(STDOUT, "Ingested data not found for space '{$space->getName()}'. Initiating ingestion..." . PHP_EOL);
            $this->cognitiveEngine->ingest($space);
            fwrite(STDOUT, "Ingestion complete." . PHP_EOL);
        } else {
            fwrite(STDOUT, "Ingested data found for space '{$space->getName()}'." . PHP_EOL);
        }
    }
}