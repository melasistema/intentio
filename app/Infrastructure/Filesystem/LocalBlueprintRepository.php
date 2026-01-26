<?php

declare(strict_types=1);

namespace Intentio\Infrastructure\Filesystem;

use DirectoryIterator;
use Intentio\Domain\Blueprint\Blueprint;
use Intentio\Domain\Blueprint\BlueprintRepository;

final class LocalBlueprintRepository implements BlueprintRepository
{
    private string $blueprintsBasePath;

    public function __construct(string $blueprintsBasePath)
    {
        $this->blueprintsBasePath = rtrim($blueprintsBasePath, '/');
    }

    public function findByName(string $name): ?Blueprint
    {
        $blueprintPath = $this->blueprintsBasePath . '/' . $name;
        if (is_dir($blueprintPath) && file_exists($blueprintPath . '/manifest.md')) {
            return new Blueprint($name, $blueprintPath);
        }
        return null;
    }

    public function findAll(): array
    {
        $foundBlueprints = [];
        if (!is_dir($this->blueprintsBasePath)) {
            return [];
        }

        $iterator = new DirectoryIterator($this->blueprintsBasePath);
        foreach ($iterator as $fileinfo) {
            if ($fileinfo->isDot()) {
                continue;
            }

            if ($fileinfo->isDir()) {
                $blueprintName = $fileinfo->getFilename();
                $blueprintPath = $this->blueprintsBasePath . '/' . $blueprintName;

                // A blueprint must have a manifest.md file
                if (file_exists($blueprintPath . '/manifest.md')) {
                    $foundBlueprints[] = new Blueprint($blueprintName, $blueprintPath);
                }
            }
        }
        return $foundBlueprints;
    }
}
