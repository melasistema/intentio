<?php

declare(strict_types=1);

namespace Intentio\Application\Console\Commands;

use Intentio\Domain\Cognitive\CognitiveEngine;
use Intentio\Domain\Space\SpaceRepository;
use Intentio\Shared\Exceptions\IntentioException;

class RenderCommand implements CommandInterface
{
    private const NAME = 'render';
    private const DESCRIPTION = 'Renders an image from a manifest.';

    public function __construct(
        private readonly CognitiveEngine $cognitiveEngine,
        private readonly SpaceRepository $spaceRepository
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
            fwrite(STDERR, "Error: The 'render' command requires a '--space' option (e.g., --space=visual_intent_designer).\n");
            return 1;
        }

        if ($query === null) {
            fwrite(STDERR, "Error: Please provide a query for the render command.\n");
            return 1;
        }

        try {
            $space = $this->spaceRepository->findByName($spaceName);

            if ($space === null) {
                throw new IntentioException("Cognitive space '{$spaceName}' not found.");
            }

            fwrite(STDOUT, "Initiating render with space: {$space->getName()}" . PHP_EOL);

            $this->cognitiveEngine->render($space, $query, $options);

            fwrite(STDOUT, "Render command completed." . PHP_EOL);

            return 0;
        } catch (IntentioException $e) {
            fwrite(STDERR, "Error: " . $e->getMessage() . PHP_EOL);
            return 1;
        } catch (\Throwable $e) {
            fwrite(STDERR, "An unexpected error occurred during rendering: " . $e->getMessage() . PHP_EOL);
            return 1;
        }
    }
}
