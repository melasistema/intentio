<?php

declare(strict_types=1);

namespace Intentio\Application\Console\Commands;

use Intentio\Application\Console\SourceList;
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

            $selectedPromptKey = $options['prompt-key']
                                ?? $this->readDefaultPrompt($space)
                                ?? $this->config['llm']['default_prompt_template_name']
                                ?? 'default';

            $prompt = $this->selectPromptTemplate($space, $selectedPromptKey);

            $this->ensureSpaceIngested($space);

            fwrite(STDOUT, "\n--- Interactive Session with '{$space->getName()}' ---" . PHP_EOL);
            fwrite(STDOUT, "Type your query and press Enter. Type 'exit' to end the session." . PHP_EOL);
            fwrite(STDOUT, "Use 'switch_prompt' to change the active prompt template." . PHP_EOL);
            $this->showActivePrompt($prompt);

            while (true) {
                // The active prompt template stays in view, and stays active until it is switched
                fwrite(STDOUT, "\n[{$prompt['key']}] > ");
                $query = $this->readLine();

                if ($query === null || strtolower($query) === 'exit') {
                    fwrite(STDOUT, "Ending interactive session." . PHP_EOL);
                    break;
                }

                if (strtolower($query) === 'switch_prompt') {
                    $prompt = $this->selectPromptTemplate($space, null);
                    $this->showActivePrompt($prompt);
                    continue;
                }

                if ($query === '') {
                    continue;
                }

                try {
                    $result = $this->cognitiveEngine->chat($space, $query, $prompt['content'], $prompt['context_files']);

                    fwrite(STDOUT, "\n--- INTENTIO Response ---" . PHP_EOL);
                    fwrite(STDOUT, $result['answer'] . PHP_EOL);
                    fwrite(STDOUT, "-------------------------" . PHP_EOL);
                    SourceList::write($result);

                    if ($prompt['render']) {
                        $this->offerRender($space, $result['answer']);
                    }
                } catch (IntentioException $e) {
                    // A failed answer or image does not end the session
                    fwrite(STDERR, "Error: " . $e->getMessage() . PHP_EOL);
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

    /**
     * Reads one line the user typed, without the line ending.
     *
     * @return string|null The line, or null when the input has ended.
     */
    private function readLine(): ?string
    {
        $line = fgets(STDIN);

        return $line === false ? null : trim($line);
    }

    /**
     * Reads an answer the session cannot continue without.
     */
    private function readChoice(): string
    {
        $line = $this->readLine();
        if ($line === null) {
            throw new IntentioException("The input ended before a choice was made.");
        }

        return $line;
    }

    private function showActivePrompt(array $prompt): void
    {
        fwrite(STDOUT, "(Active Prompt Template: {$prompt['key']})" . PHP_EOL);
        fwrite(STDOUT, "Instruction: {$prompt['instruction']}" . PHP_EOL);
    }

    /**
     * Reads the prompt template a space starts with from its manifest.
     */
    private function readDefaultPrompt(Space $space): ?string
    {
        $manifestPath = $space->getPath() . '/manifest.md';
        if (!file_exists($manifestPath)) {
            return null;
        }

        if (preg_match('/^default_prompt:\s*(\S+)\s*$/mu', file_get_contents($manifestPath), $matches)) {
            return $matches[1];
        }

        return null;
    }

    /**
     * After an answer from a prompt template marked 'render: true': offers to turn the answer into an image.
     * The image prompt is the text the model wrote between the render tags.
     */
    private function offerRender(Space $space, string $answer): void
    {
        if (!preg_match('/<<<RENDER_PROMPT>>>(.*?)<<<END_RENDER_PROMPT>>>/su', $answer, $matches) || trim($matches[1]) === '') {
            fwrite(STDERR, "Nothing to render: the answer has no text between <<<RENDER_PROMPT>>> and <<<END_RENDER_PROMPT>>>." . PHP_EOL);
            return;
        }

        while (true) {
            fwrite(STDOUT, "\nRender this image? (yes/no): ");
            $choice = strtolower($this->readChoice());

            if (in_array($choice, ['yes', 'y'])) {
                $this->cognitiveEngine->render($space, trim($matches[1]));
                fwrite(STDOUT, "Image rendering complete." . PHP_EOL);
                return;
            }

            if (in_array($choice, ['no', 'n'])) {
                fwrite(STDOUT, "Rendering skipped." . PHP_EOL);
                return;
            }

            fwrite(STDERR, "Invalid choice. Please enter 'yes' or 'no'." . PHP_EOL);
        }
    }

    private function selectOrCreateSpace(?string $spaceName): Space
    {
        if ($spaceName !== null) {
            $space = $this->spaceRepository->findByName($spaceName);
            if ($space === null) {
                fwrite(STDOUT, "Cognitive space '{$spaceName}' not found. Would you like to create it? (yes/no): ");
                $confirm = $this->readChoice();
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
            $choice = $this->readChoice();

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
            $choice = $this->readChoice();
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
                $inputName = $this->readChoice();
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
            $choice = $this->readChoice();

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
        $pending = $this->cognitiveEngine->pendingChanges($space);

        if (empty($pending['changed']) && empty($pending['removed'])) {
            fwrite(STDOUT, "The index of space '{$space->getName()}' is up to date." . PHP_EOL);
            return;
        }

        if ($pending['unchanged'] === 0 && empty($pending['removed'])) {
            fwrite(STDOUT, "Ingested data not found for space '{$space->getName()}'. Initiating ingestion..." . PHP_EOL);
        } else {
            fwrite(STDOUT, sprintf(
                "The knowledge of space '%s' has changed since it was ingested (%d new or modified, %d removed)." . PHP_EOL,
                $space->getName(),
                count($pending['changed']),
                count($pending['removed'])
            ));
            fwrite(STDOUT, "Update the index now? (yes/no): ");
            if (!in_array(strtolower($this->readChoice()), ['yes', 'y'])) {
                fwrite(STDOUT, "Index left as it is." . PHP_EOL);
                return;
            }
        }

        $summary = $this->cognitiveEngine->ingest($space);
        if ($summary['failed'] > 0) {
            throw new IntentioException("{$summary['failed']} file(s) could not be indexed in space '{$space->getName()}'.");
        }
        fwrite(STDOUT, "Ingestion complete." . PHP_EOL);
    }
}
