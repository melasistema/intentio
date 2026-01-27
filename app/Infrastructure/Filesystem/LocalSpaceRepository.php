<?php

declare(strict_types=1);

namespace Intentio\Infrastructure\Filesystem;

use DirectoryIterator;
use Intentio\Domain\Space\Space;
use Intentio\Domain\Space\SpaceRepository;
use Intentio\Shared\Exceptions\IntentioException;

final class LocalSpaceRepository implements SpaceRepository
{
    private string $spacesBasePath;

    public function __construct(string $spacesBasePath)
    {
        $this->spacesBasePath = rtrim($spacesBasePath, '/');
        if (!is_dir($this->spacesBasePath) && !mkdir($this->spacesBasePath, 0777, true)) {
            throw new IntentioException("Could not create base spaces directory: {$this->spacesBasePath}");
        }
    }

    public function findByName(string $name): ?Space
    {
        $spacePath = $this->spacesBasePath . '/' . $name;
        if (is_dir($spacePath)) {
            // A space is considered valid if its directory exists.
            // Further validation (e.g., checking for specific subdirectories) can be added via SpaceValidator if needed.
            return new Space($name, $spacePath);
        }
        return null;
    }

    public function save(Space $space): void
    {
        $spacePath = $space->getPath();
        if (!is_dir($spacePath) && !mkdir($spacePath, 0777, true)) {
            throw new IntentioException("Could not create space directory: {$spacePath}");
        }
        // No other persistence needed for now, as spaces are directory-based.
        // A manifest file or similar could be added here if more metadata is required for a space.
    }

    public function exists(string $name): bool
    {
        return is_dir($this->spacesBasePath . '/' . $name);
    }

    public function findAll(): array
    {
        $foundSpaces = [];
        if (!is_dir($this->spacesBasePath)) {
            return [];
        }

        $iterator = new DirectoryIterator($this->spacesBasePath);
        foreach ($iterator as $fileinfo) {
            if ($fileinfo->isDot()) {
                continue;
            }

            if ($fileinfo->isDir()) {
                $spaceName = $fileinfo->getFilename();
                $spacePath = $this->spacesBasePath . '/' . $spaceName;
                $foundSpaces[] = new Space($spaceName, $spacePath);
            }
        }
        return $foundSpaces;
    }

    public function delete(string $name): void
    {
        $spacePath = $this->spacesBasePath . '/' . $name;
        if (!is_dir($spacePath)) {
            throw new IntentioException("Space '{$name}' does not exist and cannot be deleted.");
        }

        // Recursively delete the directory
        $this->rmdirRecursive($spacePath);
    }

    private function rmdirRecursive(string $dir): void
    {
        if (!is_dir($dir)) {
            return;
        }
        $files = array_diff(scandir($dir), ['.', '..']);
        foreach ($files as $file) {
            (is_dir("$dir/$file")) ? $this->rmdirRecursive("$dir/$file") : unlink("$dir/$file");
        }
        rmdir($dir);
    }
}
