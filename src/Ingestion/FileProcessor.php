<?php

declare(strict_types=1);

namespace Intentio\Ingestion;

use Intentio\Cli\Output;

/**
 * Processes files to extract content and metadata for ingestion.
 *
 * This class handles reading different file types and can be extended
 * to include chunking logic, metadata extraction, and type-specific
 * parsing.
 */
final class FileProcessor
{
    /**
     * Processes a single file, returning its content as chunks.
     *
     * @param string $filePath The path to the file to process.
     * @param string $category The cognitive category of the file.
     * @return array An array of content chunks, each with 'content' and 'metadata'.
     */
    public function process(string $filePath, string $category): array
    {
        if (!file_exists($filePath) || !is_readable($filePath)) {
            throw new \InvalidArgumentException("File not found or not readable: {$filePath}");
        }

        $fileContent = file_get_contents($filePath);
        if ($fileContent === false) {
            throw new \RuntimeException("Could not read content from file: {$filePath}");
        }

        $baseMetadata = [
            'source' => $filePath,
            'filename' => basename($filePath),
            'extension' => pathinfo($filePath, PATHINFO_EXTENSION),
            'timestamp' => filemtime($filePath),
            'category' => $category, // Add category to metadata
        ];

        $chunks = [];
        // Basic chunking: split by double newlines (paragraphs)
        $paragraphs = preg_split('/(\r?\n){2,}/', $fileContent, -1, PREG_SPLIT_NO_EMPTY);

        foreach ($paragraphs as $index => $paragraph) {
            $trimmedParagraph = trim($paragraph);
            if (!empty($trimmedParagraph)) {
                $chunkMetadata = array_merge($baseMetadata, [
                    'chunk_index' => $index,
                    'chunk_length' => strlen($trimmedParagraph),
                ]);
                $chunks[] = [
                    'content' => $trimmedParagraph,
                    'metadata' => $chunkMetadata,
                ];
            }
        }
        
        if (empty($chunks) && !empty($fileContent)) {
             $chunks[] = [
                'content' => trim($fileContent),
                'metadata' => array_merge($baseMetadata, [
                    'chunk_index' => 0,
                    'chunk_length' => strlen(trim($fileContent)),
                ]),
             ];
        }

        return $chunks;
    }
}
