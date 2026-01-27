<?php

declare(strict_types=1);

namespace Intentio\Domain\Cognitive;

use Intentio\Domain\Space\Space;
use Intentio\Domain\Model\EmbeddingInterface;
use Intentio\Shared\Exceptions\IntentioException;
use Intentio\Infrastructure\Filesystem\FileProcessor;

final class IngestionService
{
    public function __construct(
        private readonly FileProcessor $fileProcessor,
        private readonly EmbeddingInterface $embeddingAdapter,
        private readonly VectorStoreInterface $vectorStore
    ) {
    }

    public function ingestSpace(Space $space): void
    {
        fwrite(STDOUT, "IngestionService: Initiating ingestion for space: '{$space->getName()}'." . PHP_EOL);

        // Scan the knowledge space for files within the knowledge and prompts directories
        $knowledgeFiles = $this->fileProcessor->scanDirectory($space->getKnowledgePath());
        $promptFiles = $this->fileProcessor->scanDirectory($space->getPromptsPath());
        
        $allFiles = array_merge($knowledgeFiles, $promptFiles);

        if (empty($allFiles)) {
            fwrite(STDOUT, "No files found in the knowledge or prompts directories to ingest for '{$space->getName()}'." . PHP_EOL);
            return;
        }

        fwrite(STDOUT, sprintf("Found %d files to process in '%s'." . PHP_EOL, count($allFiles), $space->getName()));

        // Ensure the vector store is initialized for this space
        $this->vectorStore->initialize($space);

        // Process each file
        foreach ($allFiles as $filePath) {
            fwrite(STDOUT, "  Processing file: " . basename($filePath) . PHP_EOL);

            try {
                // Determine category based on relative path
                $category = 'misc';
                $relativePath = null;

                if (str_starts_with($filePath, $space->getKnowledgePath())) {
                    $relativePath = substr($filePath, strlen($space->getKnowledgePath()) + 1);
                    $category = 'knowledge';
                    $parts = explode(DIRECTORY_SEPARATOR, $relativePath);
                    if (count($parts) > 1) { // If it's in a subdirectory of knowledge, use the subdirectory name as category
                        $category = $parts[0];
                    }
                } elseif (str_starts_with($filePath, $space->getPromptsPath())) {
                    $relativePath = substr($filePath, strlen($space->getPromptsPath()) + 1);
                    $category = 'prompt';
                }

                $chunks = $this->fileProcessor->process($filePath, $category);
                
                foreach ($chunks as $chunk) {
                    // Enhance chunk metadata with relative path if available
                    if ($relativePath !== null) {
                        $chunk['metadata']['relative_path'] = $relativePath;
                    }
                    fwrite(STDOUT, sprintf("    - Embedding chunk (length: %d)..." . PHP_EOL, $chunk['metadata']['chunk_length']));
                    $embedding = $this->embeddingAdapter->embed($chunk['content']);
                    $this->vectorStore->add($space, $chunk, $embedding); // Pass Space object
                }
            } catch (\Throwable $e) {
                fwrite(STDERR, "Error processing file {$filePath}: " . $e->getMessage() . PHP_EOL);
                // Continue to next file
            }
        }
        fwrite(STDOUT, "IngestionService: Ingestion complete for space: '{$space->getName()}'." . PHP_EOL);
    }
}