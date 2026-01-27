<?php

declare(strict_types=1);

namespace Intentio\Infrastructure\Filesystem;

use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use Intentio\Shared\Exceptions\IntentioException;

final class FileProcessor
{
    /**
     * Scans a directory for relevant files.
     *
     * @param string $directoryPath The path to the directory to scan.
     * @return array An array of file paths.
     */
    public function scanDirectory(string $directoryPath): array
    {
        $files = [];
        if (!is_dir($directoryPath)) {
            return $files;
        }

        $iterator = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($directoryPath, RecursiveDirectoryIterator::SKIP_DOTS),
            RecursiveIteratorIterator::LEAVES_ONLY
        );

        foreach ($iterator as $file) {
            if ($file->isFile() && in_array($file->getExtension(), ['md', 'txt'])) {
                $files[] = $file->getPathname();
            }
        }
        return $files;
    }

    /**
     * Processes a single file and chunks its content.
     *
     * @param string $filePath The path to the file.
     * @param string $category The category of the file (e.g., 'reference', 'memory').
     * @return array An array of chunked content with metadata.
     * @throws IntentioException If the file cannot be read.
     */
    public function process(string $filePath, string $category): array
    {
        if (!file_exists($filePath) || !is_readable($filePath)) {
            throw new IntentioException("File not found or not readable: {$filePath}");
        }

        $content = file_get_contents($filePath);
        if ($content === false) {
            throw new IntentioException("Failed to read file content: {$filePath}");
        }

        // Basic chunking: split by double newline, then trim and filter empty chunks.
        // A more advanced chunking strategy could involve sentence splitting,
        // fixed-size chunks with overlap, or semantic chunking.
        $rawChunks = preg_split('/(\R){2,}/', $content, -1, PREG_SPLIT_NO_EMPTY);

        $chunks = [];
        foreach ($rawChunks as $index => $rawChunk) {
            $cleanedChunk = trim($rawChunk);
            if (empty($cleanedChunk)) {
                continue;
            }

            $chunks[] = [
                'content' => $cleanedChunk,
                'metadata' => [
                    'filename' => basename($filePath),
                    'filepath' => $filePath,
                    'category' => $category,
                    'chunk_index' => $index,
                    'chunk_length' => strlen($cleanedChunk),
                ],
            ];
        }

        return $chunks;
    }
}
