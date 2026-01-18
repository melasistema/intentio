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
    private const MAX_CHUNK_LENGTH = 500; // Max characters per chunk, rough proxy for token count

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

        $allChunks = [];
        $currentHeading = null;
        $sectionContent = '';
        $chunkIndex = 0;

        $lines = explode("\n", $fileContent);

        foreach ($lines as $line) {
            $trimmedLine = trim($line);

            // Check for Markdown headers
            if (preg_match('/^(#+)\s*(.*)$/', $trimmedLine, $matches)) {
                // If there's accumulated content in the previous section, process it
                if (!empty($sectionContent)) {
                    $allChunks = array_merge($allChunks, $this->splitSectionIntoChunks(
                        $sectionContent,
                        $baseMetadata,
                        $currentHeading,
                        $chunkIndex
                    ));
                    $sectionContent = ''; // Reset section content
                }
                $currentHeading = $matches[2]; // Update current heading
            } else {
                $sectionContent .= $trimmedLine . "\n"; // Accumulate content
            }
        }

        // Process any remaining content from the last section
        if (!empty($sectionContent)) {
            $allChunks = array_merge($allChunks, $this->splitSectionIntoChunks(
                $sectionContent,
                $baseMetadata,
                $currentHeading,
                $chunkIndex
            ));
        }
        
        // If no chunks were created but there was content (e.g., a single unheaded paragraph),
        // ensure it's still processed as a single chunk.
        if (empty($allChunks) && !empty(trim($fileContent))) {
            $allChunks = array_merge($allChunks, $this->splitSectionIntoChunks(
                $fileContent,
                $baseMetadata,
                null, // No heading
                $chunkIndex
            ));
        }

        return $allChunks;
    }

    /**
     * Splits a section's content into smaller chunks based on paragraphs and max length.
     */
    private function splitSectionIntoChunks(
        string $sectionContent,
        array $baseMetadata,
        ?string $heading,
        int &$globalChunkIndex // Pass by reference to maintain across sections
    ): array {
        $chunks = [];
        $paragraphs = preg_split('/(\r?\n){2,}/', $sectionContent, -1, PREG_SPLIT_NO_EMPTY);

        $currentChunkContent = '';
        foreach ($paragraphs as $paragraph) {
            $trimmedParagraph = trim($paragraph);
            if (empty($trimmedParagraph)) {
                continue;
            }

            // If adding the new paragraph exceeds MAX_CHUNK_LENGTH,
            // finalize the current chunk and start a new one.
            if (strlen($currentChunkContent) + strlen($trimmedParagraph) + 1 > self::MAX_CHUNK_LENGTH && !empty($currentChunkContent)) {
                $chunks[] = $this->createChunk($currentChunkContent, $baseMetadata, $heading, $globalChunkIndex);
                $currentChunkContent = '';
            }
            
            // If the paragraph itself is larger than MAX_CHUNK_LENGTH,
            // break it into smaller sub-chunks.
            if (strlen($trimmedParagraph) > self::MAX_CHUNK_LENGTH) {
                if (!empty($currentChunkContent)) { // Add any pending content
                    $chunks[] = $this->createChunk($currentChunkContent, $baseMetadata, $heading, $globalChunkIndex);
                    $currentChunkContent = '';
                }
                // Split the large paragraph into smaller pieces
                $subChunks = str_split($trimmedParagraph, self::MAX_CHUNK_LENGTH);
                foreach ($subChunks as $subChunk) {
                    $chunks[] = $this->createChunk($subChunk, $baseMetadata, $heading, $globalChunkIndex);
                }
            } else {
                $currentChunkContent .= (empty($currentChunkContent) ? '' : "\n") . $trimmedParagraph;
            }
        }

        // Add any remaining content
        if (!empty($currentChunkContent)) {
            $chunks[] = $this->createChunk($currentChunkContent, $baseMetadata, $heading, $globalChunkIndex);
        }

        return $chunks;
    }

    /**
     * Creates a single chunk array with content and metadata.
     */
    private function createChunk(string $content, array $baseMetadata, ?string $heading, int &$chunkIndex): array
    {
        $metadata = array_merge($baseMetadata, [
            'chunk_index' => $chunkIndex++, // Increment after use
            'chunk_length' => strlen($content),
        ]);
        if ($heading !== null) {
            $metadata['heading'] = $heading;
        }
        return [
            'content' => $content,
            'metadata' => $metadata,
        ];
    }
}

