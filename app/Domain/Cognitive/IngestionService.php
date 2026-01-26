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

        // Scan the knowledge space for files
        $files = $this->fileProcessor->scanDirectory($space->getPath());
        if (empty($files)) {
            fwrite(STDOUT, "No files found in the knowledge space to ingest for '{$space->getName()}'." . PHP_EOL);
            return;
        }

        fwrite(STDOUT, sprintf("Found %d files to process in '%s'." . PHP_EOL, count($files), $space->getName()));

        // Ensure the vector store is initialized for this space
        $this->vectorStore->initialize($space);

        // Process each file
        foreach ($files as $filePath) {
            fwrite(STDOUT, "  Processing file: " . basename($filePath) . PHP_EOL);

            try {
                // Determine category based on subdirectory (e.g., reference, memory, opinion)
                $category = 'misc'; // Default category
                if (str_starts_with($filePath, $space->getReferencePath())) {
                    $category = 'reference';
                } elseif (str_starts_with($filePath, $space->getMemoryPath())) {
                    $category = 'memory';
                } elseif (str_starts_with($filePath, $space->getOpinionPath())) {
                    $category = 'opinion';
                } elseif (str_starts_with($filePath, $space->getPromptsPath())) {
                    $category = 'prompt'; // Prompts might not be ingested for retrieval but good to identify
                }

                $chunks = $this->fileProcessor->process($filePath, $category);
                
                foreach ($chunks as $chunk) {
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
