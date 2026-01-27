<?php

declare(strict_types=1);

namespace Intentio\Application\Console\Commands;

use Intentio\Domain\Cognitive\CognitiveEngine;
use Intentio\Infrastructure\Filesystem\LocalSpaceRepository;
use Intentio\Shared\Exceptions\IntentioException;

final class ClearCommand implements CommandInterface
{
    private const NAME = 'clear';
    private const DESCRIPTION = 'Clears all ingested data (e.g., vector store) for a specified cognitive space.';

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

        if ($spaceName === null) {
            fwrite(STDERR, "Error: The 'clear' command requires a '--space' option (e.g., --space=my_agent)." . PHP_EOL);
            return 1;
        }

        try {
            $space = $this->spaceRepository->findByName($spaceName);

            if ($space === null) {
                throw new IntentioException("Cognitive space '{$spaceName}' not found.");
            }

            fwrite(STDOUT, "--- Clearing Cognitive Space: '{$space->getName()}' ---" . PHP_EOL);
            fwrite(STDERR, "\033[33mWARNING: This will permanently delete all ingested data for this cognitive space.\033[0m" . PHP_EOL); // Yellow color for warning

            $confirmation = readline("Type 'yes' to confirm deletion: ");

            if (trim(strtolower($confirmation)) !== 'yes') {
                fwrite(STDOUT, "Deletion cancelled." . PHP_EOL);
                return 1; // Indicate cancellation
            }

            $this->cognitiveEngine->clear($space); // This will eventually remove the vector store and any other associated data

            fwrite(STDOUT, "Successfully cleared data for cognitive space '{$space->getName()}'." . PHP_EOL);
            fwrite(STDOUT, "You may now re-ingest this space if needed." . PHP_EOL);

            return 0;
        } catch (IntentioException $e) {
            fwrite(STDERR, "Error: " . $e->getMessage() . PHP_EOL);
            return 1;
        } catch (\Throwable $e) {
            fwrite(STDERR, "An unexpected error occurred during clearing: " . $e->getMessage() . PHP_EOL);
            return 1;
        }
    }
}
