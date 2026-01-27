<?php

declare(strict_types=1);

namespace Intentio\Application\Console\Commands;

use Intentio\Infrastructure\Filesystem\LocalSpaceRepository;
use Intentio\Shared\Exceptions\IntentioException;

final class SpacesCommand implements CommandInterface
{
    private const NAME = 'spaces';
    private const DESCRIPTION = 'Lists all available cognitive spaces.';

    public function __construct(
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
        try {
            $spaces = $this->spaceRepository->findAll();

            if (empty($spaces)) {
                fwrite(STDOUT, "No cognitive spaces found." . PHP_EOL);
                return 0;
            }

            fwrite(STDOUT, "Available Cognitive Spaces:" . PHP_EOL);
            foreach ($spaces as $space) {
                fwrite(STDOUT, sprintf("  - %s (Path: %s)" . PHP_EOL, $space->getName(), $space->getPath()));
            }

            return 0;
        } catch (IntentioException $e) {
            fwrite(STDERR, "Error: " . $e->getMessage() . PHP_EOL);
            return 1;
        } catch (\Throwable $e) {
            fwrite(STDERR, "An unexpected error occurred while listing spaces: " . $e->getMessage() . PHP_EOL);
            return 1;
        }
    }
}
