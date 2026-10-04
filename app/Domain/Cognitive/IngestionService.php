<?php

declare(strict_types=1);

namespace Intentio\Domain\Cognitive;

use Intentio\Domain\Space\Space;
use Intentio\Domain\Model\EmbeddingInterface;
use Intentio\Infrastructure\Filesystem\FileProcessor;

final class IngestionService
{
    // Raised whenever files are chunked or embedded differently, so that existing indexes are rebuilt
    private const INDEX_FORMAT = '2';

    public function __construct(
        private readonly FileProcessor $fileProcessor,
        private readonly EmbeddingInterface $embeddingAdapter,
        private readonly VectorStoreInterface $vectorStore,
        private readonly string $embeddingModel
    ) {
    }

    /**
     * Compares the knowledge folder of a space with its index.
     *
     * @return array An associative array with 'changed' (new or modified files, as path => fingerprint),
     *               'removed' (indexed paths whose file no longer exists) and 'unchanged' (a count).
     */
    public function pendingChanges(Space $space): array
    {
        $indexed = $this->vectorStore->indexedFiles($space);
        $knowledgePath = $space->getKnowledgePath();

        $changed = [];
        $unchanged = 0;
        foreach ($this->fileProcessor->scanDirectory($knowledgePath) as $filePath) {
            $relativePath = substr($filePath, strlen($knowledgePath) + 1);
            // The embedding model is part of the fingerprint: vectors from another model cannot be compared
            $fingerprint = hash('sha256', self::INDEX_FORMAT . "\n" . $this->embeddingModel . "\n" . file_get_contents($filePath));

            if (($indexed[$relativePath] ?? null) === $fingerprint) {
                $unchanged++;
            } else {
                $changed[$relativePath] = $fingerprint;
            }
            unset($indexed[$relativePath]);
        }
        ksort($changed);

        return [
            'changed' => $changed,
            'removed' => array_keys($indexed),
            'unchanged' => $unchanged,
        ];
    }

    /**
     * Brings the index of a space up to date with its knowledge folder.
     * Only new and modified files are embedded, so ingesting twice changes nothing.
     *
     * @return array The number of files 'indexed', 'removed', 'unchanged' and 'failed'.
     */
    public function ingestSpace(Space $space): array
    {
        $pending = $this->pendingChanges($space);
        $indexed = 0;
        $failed = 0;

        foreach ($pending['changed'] as $relativePath => $fingerprint) {
            try {
                // A file in a subdirectory of knowledge takes the subdirectory name as its category
                $parts = explode(DIRECTORY_SEPARATOR, $relativePath);
                $category = count($parts) > 1 ? $parts[0] : 'knowledge';

                $chunks = $this->fileProcessor->process($space->getKnowledgePath() . DIRECTORY_SEPARATOR . $relativePath, $category);

                $texts = [];
                foreach ($chunks as $index => $chunk) {
                    $chunks[$index]['metadata']['relative_path'] = $relativePath;
                    // A section is embedded together with the headings above it, so that
                    // "Examples" under "Unfair Advantage" is found by a question about unfair advantage
                    $parentHeadings = array_slice($chunk['metadata']['headings'], 0, -1);
                    $texts[] = implode("\n", [...$parentHeadings, $chunk['content']]);
                }
                // The sections of a file are embedded in one request
                $embeddings = $this->embeddingAdapter->embedAll($texts);

                $this->vectorStore->replaceFile($space, $relativePath, $fingerprint, $chunks, $embeddings);
                fwrite(STDOUT, sprintf("  Indexed: %s (%d chunks)" . PHP_EOL, $relativePath, count($chunks)));
                $indexed++;
            } catch (\Throwable $e) {
                fwrite(STDERR, "  Failed: {$relativePath}: " . $e->getMessage() . PHP_EOL);
                $failed++;
                // Continue to next file; this one stays as it was in the index
            }
        }

        foreach ($pending['removed'] as $relativePath) {
            $this->vectorStore->removeFile($space, $relativePath);
            fwrite(STDOUT, "  Removed: {$relativePath}" . PHP_EOL);
        }

        return [
            'indexed' => $indexed,
            'removed' => count($pending['removed']),
            'unchanged' => $pending['unchanged'],
            'failed' => $failed,
        ];
    }
}
