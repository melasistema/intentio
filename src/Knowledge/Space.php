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
        $knowledgePath = $this->getRootPath() . '/knowledge';
        Output::writeln("Scanning for knowledge in: " . $knowledgePath);
        
        if (!is_dir($knowledgePath)) {
            Output::writeln("Warning: 'knowledge' subdirectory not found in this space. No files to ingest.");
            return [];
        }

        $assets = [];
        $iterator = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($knowledgePath, \RecursiveDirectoryIterator::SKIP_DOTS),
            \RecursiveIteratorIterator::SELF_FIRST
        );

        $rootPathStrLen = strlen($knowledgePath) + 1; // +1 for the trailing slash

        foreach ($iterator as $file) {
            if ($file->isDir()) {
                continue;
            }

            $filePath = $file->getRealPath();
            $relativePath = substr($filePath, $rootPathStrLen);
            
            $parts = explode(DIRECTORY_SEPARATOR, $relativePath);
            
            $category = 'general'; // Default category for files in the root of 'knowledge'
            if (count($parts) > 1) {
                // The category is the name of the first-level directory inside 'knowledge'
                $category = $parts[0];
            }

            $assets[] = [
                'path' => $filePath,
                'category' => $category,
            ];
        }

        return $assets;
    }

    /**
     * Lists all available cognitive spaces (subdirectories) within a given base path.
     *
     * @param string $basePath The root directory where cognitive spaces are stored.
     * @return array An array of space names.
     */
    public static function getAvailableSpaces(string $basePath): array
    {
        $spaces = [];
        if (is_dir($basePath)) {
            $items = scandir($basePath);
            foreach ($items as $item) {
                if ($item === '.' || $item === '..') {
                    continue;
                }
                $fullPath = $basePath . DIRECTORY_SEPARATOR . $item;
                if (is_dir($fullPath)) {
                    $spaces[] = $item;
                }
            }
        }
        return $spaces;
    }
}
