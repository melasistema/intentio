<?php

declare(strict_types=1);

namespace Intentio\Application\Console\Commands;

use Intentio\Domain\Space\SpaceFactory;
use Intentio\Infrastructure\Filesystem\LocalSpaceRepository;
use Intentio\Infrastructure\Filesystem\LocalBlueprintRepository;
use Intentio\Infrastructure\Filesystem\FileCopier;
use Intentio\Shared\Exceptions\IntentioException;

final class InitCommand implements CommandInterface
{
    private const NAME = 'init';
    private const DESCRIPTION = 'Initializes a new cognitive space from a blueprint.';

    public function __construct(
        private readonly SpaceFactory $spaceFactory,
        private readonly LocalSpaceRepository $spaceRepository,
        private readonly LocalBlueprintRepository $blueprintRepository,
        private readonly FileCopier $fileCopier,
        private readonly array $config
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
        $blueprintName = $arguments[0] ?? null;
        $spaceName = $options['space'] ?? null; // --space=<space_name> option

        if ($blueprintName === null) {
            return $this->handleInitInteractive($spaceName);
        } else {
            return $this->handleInitWithArgument($blueprintName, $spaceName);
        }
    }

    private function handleInitWithArgument(string $blueprintName, ?string $spaceName): int
    {
        try {
            $blueprint = $this->blueprintRepository->findByName($blueprintName);
            if ($blueprint === null) {
                fwrite(STDERR, "Error: Blueprint '{$blueprintName}' not found." . PHP_EOL);
                return 1;
            }

            $this->initializeSpace($blueprint->getName(), $spaceName);
            return 0;
        } catch (IntentioException $e) {
            fwrite(STDERR, "Error: " . $e->getMessage() . PHP_EOL);
            return 1;
        }
    }

    private function handleInitInteractive(?string $defaultSpaceName): int
    {
        $blueprints = $this->blueprintRepository->findAll();

        if (empty($blueprints)) {
            fwrite(STDERR, "Error: No blueprints found in the 'packages/' directory." . PHP_EOL);
            return 1;
        }

        fwrite(STDOUT, "Available Blueprints:" . PHP_EOL);
        foreach ($blueprints as $i => $blueprint) {
            fwrite(STDOUT, sprintf("  %d. %s" . PHP_EOL, $i + 1, $blueprint->getName()));
        }

        $choice = $this->askForBlueprintChoice(count($blueprints));
        $chosenBlueprint = $blueprints[$choice - 1];

        // Prompt for space name if not provided via --space
        $spaceName = $defaultSpaceName ?? $this->askForSpaceName($chosenBlueprint->getName());

        $this->initializeSpace($chosenBlueprint->getName(), $spaceName);
        return 0;
    }

    private function askForBlueprintChoice(int $numberOfBlueprints): int
    {
        while (true) {
            fwrite(STDOUT, "Enter the number of your chosen blueprint: ");
            $choice = trim(fgets(STDIN));

            if (!ctype_digit($choice)) {
                fwrite(STDERR, "Invalid choice. Please enter a number." . PHP_EOL);
                continue;
            }

            $choice = (int) $choice;

            if ($choice < 1 || $choice > $numberOfBlueprints) {
                fwrite(STDERR, "Invalid choice. Please choose a blueprint from the list." . PHP_EOL);
                continue;
            }

            return $choice;
        }
    }

    private function askForSpaceName(string $defaultBlueprintName): string
    {
        while (true) {
            fwrite(STDOUT, "Enter a name for your new space (e.g., 'my_{$defaultBlueprintName}_space'): ");
            $spaceName = trim(fgets(STDIN));

            if (empty($spaceName)) {
                fwrite(STDERR, "Space name cannot be empty." . PHP_EOL);
                continue;
            }
            // Basic validation for space name, can be expanded later
            if (!preg_match('/^[a-zA-Z0-9_\-]+$/', $spaceName)) {
                fwrite(STDERR, "Invalid space name. Use alphanumeric, hyphens, and underscores." . PHP_EOL);
                continue;
                // I will update it to allow periods and forward slashes to support nested folders
            }

            return $spaceName;
        }
    }

    /**
     * @throws IntentioException
     */
    private function initializeSpace(string $blueprintName, string $spaceName): void
    {
        // This relies on the 'spaces_base_path' config being set correctly.
        $spacesBasePath = rtrim($this->config['spaces_base_path'], '/');
        $spacePath = $spacesBasePath . '/' . $spaceName;

        if ($this->spaceRepository->exists($spaceName)) {
            throw new IntentioException("A space named '{$spaceName}' already exists.");
        }

        $blueprint = $this->blueprintRepository->findByName($blueprintName);
        if ($blueprint === null) {
             throw new IntentioException("Blueprint '{$blueprintName}' not found (this should have been caught earlier).");
        }

        // Copy blueprint files to the new space location
        $this->fileCopier->copyDirectory(
            $blueprint->getPath(),
            $spacePath,
            [] // Now copies all files, including manifest.md and README.md
        );

        // Create the Space domain object
        // The SpaceFactory will handle creating the actual directory structure for the space.
        $space = $this->spaceFactory->createSpace($spaceName, $spacePath);

        // Persist the space (e.g., create a metadata file or ensure directory structure if not done by factory)
        $this->spaceRepository->save($space);

        fwrite(STDOUT, "Space '{$spaceName}' initialized successfully from blueprint '{$blueprintName}'." . PHP_EOL);
        fwrite(STDOUT, "New space created at: {$space->getPath()}" . PHP_EOL);
    }
}