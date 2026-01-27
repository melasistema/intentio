<?php

declare(strict_types=1);

namespace Intentio\Application\Console\Commands;

use Intentio\Domain\Cognitive\CognitiveEngine;
use Intentio\Infrastructure\Filesystem\LocalSpaceRepository;
use Intentio\Shared\Exceptions\IntentioException;

final class IngestCommand implements CommandInterface
{
    private const NAME = 'ingest';
    private const DESCRIPTION = 'Ingests files from a specified cognitive space to create embeddings.';

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
            fwrite(STDERR, "Error: The 'ingest' command requires a '--space' option (e.g., --space=my_agent)." . PHP_EOL);
            return 1;
        }

        try {
            $space = $this->spaceRepository->findByName($spaceName);

            if ($space === null) {
                throw new IntentioException("Cognitive space '{$spaceName}' not found.");
            }

            fwrite(STDOUT, "Initiating ingestion for space: {$space->getName()}" . PHP_EOL);

            $this->cognitiveEngine->ingest($space);

            fwrite(STDOUT, "Ingestion complete for space: {$space->getName()}." . PHP_EOL);

            return 0;
        } catch (IntentioException $e) {
            fwrite(STDERR, "Error: " . $e->getMessage() . PHP_EOL);
            return 1;
        } catch (\Throwable $e) {
            fwrite(STDERR, "An unexpected error occurred during ingestion: " . $e->getMessage() . PHP_EOL);
            return 1;
        }
    }
}
