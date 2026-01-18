<?php

declare(strict_types=1);

namespace Intentio\Knowledge;

use Intentio\Cli\Output;

/**
 * Represents a designed cognitive space.
 *
 * This class understands that the filesystem structure carries meaning.
 * It is responsible for discovering, categorizing, and interpreting the
 * knowledge assets within a given directory, respecting the semantic
 * signals of the folder structure (e.g., identity, memory, reference).
 */
final class Space
{
    public function __construct(private readonly string $rootPath)
    {
        if (!is_dir($this->rootPath)) {
            Output::writeln("Knowledge space '{$this->rootPath}' not found. Creating it.");
            if (!mkdir($this->rootPath, 0755, true)) {
                throw new \RuntimeException("Could not create knowledge space directory.");
            }
        }
    }

    public function getRootPath(): string
    {
        return realpath($this->rootPath);
    }

    /**
     * Scans the knowledge space and returns a structured list of assets,
     * including their cognitive category.
     *
     * @return array An array of assets, each an associative array with 'path' and 'category'.
     */
    public function scan(): array
    {
        Output::writeln("Scanning knowledge space: " . $this->getRootPath());
        
        $assets = [];
        $iterator = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($this->rootPath, \RecursiveDirectoryIterator::SKIP_DOTS),
            \RecursiveIteratorIterator::SELF_FIRST
        );

        $rootPathStrLen = strlen($this->getRootPath()) + 1; // +1 for the trailing slash

        foreach ($iterator as $file) {
            if ($file->isDir()) {
                continue;
            }

            $filePath = $file->getRealPath();
            $relativePath = substr($filePath, $rootPathStrLen);
            
            $parts = explode(DIRECTORY_SEPARATOR, $relativePath);
            
            $category = 'general'; // Default category for files in the root
            if (count($parts) > 1) {
                // The category is the name of the first-level directory
                $category = $parts[0];
            }

            $assets[] = [
                'path' => $filePath,
                'category' => $category,
            ];
        }

        return $assets;
    }
}
