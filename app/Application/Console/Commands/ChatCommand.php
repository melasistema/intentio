<?php

declare(strict_types=1);

namespace Intentio\Application\Console\Commands;

use Intentio\Domain\Cognitive\CognitiveEngine;
use Intentio\Infrastructure\Filesystem\LocalSpaceRepository;
use Intentio\Shared\Exceptions\IntentioException;

final class ChatCommand implements CommandInterface
{
    private const NAME = 'chat';
    private const DESCRIPTION = 'Initiates a chat interaction with a cognitive space.';

    public function __construct(
        private readonly CognitiveEngine $cognitiveEngine,
        private readonly LocalSpaceRepository $spaceRepository
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

            fwrite(STDOUT, "Initiating chat with space: {$space->getName()}" . PHP_EOL);
            fwrite(STDOUT, "Your query: \"{$query}\"" . PHP_EOL);

            $response = $this->cognitiveEngine->chat($space, $query, $options);

            fwrite(STDOUT, "\n--- Interpreter Response ---" . PHP_EOL);
            fwrite(STDOUT, $response . PHP_EOL);
            fwrite(STDOUT, "----------------------------\n" . PHP_EOL);

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
