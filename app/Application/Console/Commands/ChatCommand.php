<?php

declare(strict_types=1);

namespace Intentio\Application\Console\Commands;

use Intentio\Application\Console\SourceList;
use Intentio\Domain\Cognitive\CognitiveEngine;
use Intentio\Domain\Cognitive\PromptResolver;
use Intentio\Infrastructure\Filesystem\LocalSpaceRepository;
use Intentio\Shared\Exceptions\IntentioException;

final class ChatCommand implements CommandInterface
{
    private const NAME = 'chat';
    private const DESCRIPTION = 'Initiates a chat interaction with a cognitive space.';

    public function __construct(
        private readonly CognitiveEngine $cognitiveEngine,
        private readonly LocalSpaceRepository $spaceRepository,
        private readonly PromptResolver $promptResolver
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
        $spaceName = $options['space'] ?? null;
        $query = $arguments[0] ?? null;
        $promptKey = $options['prompt-key'] ?? null;

        if ($spaceName === null) {
            fwrite(STDERR, "Error: The 'chat' command requires a '--space' option (e.g., --space=my_agent).\n");
            return 1;
        }

        if ($query === null) {
            fwrite(STDERR, "Error: Please provide a query for the chat command.\n");
            return 1;
        }

        try {
            $space = $this->spaceRepository->findByName($spaceName);

            if ($space === null) {
                throw new IntentioException("Cognitive space '{$spaceName}' not found.");
            }

            $pending = $this->cognitiveEngine->pendingChanges($space);
            if ($pending['unchanged'] === 0 && !empty($pending['changed'])) {
                throw new IntentioException("Space '{$spaceName}' has not been ingested. Run: ./intentio ingest --space={$spaceName}");
            }
            if (!empty($pending['changed']) || !empty($pending['removed'])) {
                fwrite(STDERR, "Note: the knowledge of this space has changed since it was ingested. Run: ./intentio ingest --space={$spaceName}" . PHP_EOL);
            }

            fwrite(STDOUT, "Initiating chat with space: {$space->getName()}" . PHP_EOL);
            fwrite(STDOUT, "Your query: \"{$query}\"" . PHP_EOL);

            // Without a prompt template, the query itself is the prompt
            $template = '{{QUERY}}';
            $pinnedFiles = [];
            if ($promptKey !== null) {
                $resolvedPrompt = $this->promptResolver->resolve($space, $promptKey);
                fwrite(STDOUT, "Prompt template: {$promptKey}" . PHP_EOL);
                $template = $resolvedPrompt['content'];
                $pinnedFiles = $resolvedPrompt['context_files'];
            }

            // The answer is shown while the model writes it
            fwrite(STDOUT, "\n--- Interpreter Response ---" . PHP_EOL);
            $result = $this->cognitiveEngine->chat($space, $query, $template, $pinnedFiles, function (string $text): void {
                fwrite(STDOUT, $text);
            });
            fwrite(STDOUT, PHP_EOL . "----------------------------" . PHP_EOL);
            SourceList::write($result);
            fwrite(STDOUT, PHP_EOL);

            return 0;
        } catch (IntentioException $e) {
            fwrite(STDERR, "Error: " . $e->getMessage() . PHP_EOL);
            return 1;
        } catch (\Throwable $e) {
            fwrite(STDERR, "An unexpected error occurred during chat: " . $e->getMessage() . PHP_EOL);
            return 1;
        }
    }
}
