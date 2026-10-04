<?php

declare(strict_types=1);

namespace Intentio\Domain\Cognitive;

use Intentio\Domain\Space\Space;
use Intentio\Domain\Model\EmbeddingInterface;

final class RetrievalService
{
    public function __construct(
        private readonly EmbeddingInterface $embeddingAdapter,
        private readonly VectorStoreInterface $vectorStore,
        private readonly int $limit,
        private readonly float $minScore,
        private readonly string $queryPrefix
    ) {
    }

    /**
     * Retrieves the passages of a cognitive space that are close enough to a query.
     * It may return nothing: a passage that is not about the query is not offered to the model.
     *
     * @param Space $space The cognitive space to retrieve from.
     * @param string $query The user's query.
     * @param string[] $excludedPaths Files to leave out, as paths relative to the knowledge folder.
     * @return array The passages, best first, each with 'content', 'source' and 'score'.
     */
    public function retrieve(Space $space, string $query, array $excludedPaths = []): array
    {
        // Embed the query
        $queryEmbedding = $this->embeddingAdapter->embed($this->queryPrefix . $query);

        // Retrieve from vector store
        $retrievedChunks = $this->vectorStore->findSimilar($space, $queryEmbedding, $this->limit, $this->minScore, $excludedPaths);

        $results = [];
        foreach ($retrievedChunks as $chunk) {
            // The source names the file and the headings that lead to the passage
            $source = $chunk['metadata']['relative_path'] ?? $chunk['metadata']['filename'] ?? 'unknown';
            foreach ($chunk['metadata']['headings'] ?? [] as $heading) {
                $source .= ' > ' . $heading;
            }

            $results[] = [
                'content' => $chunk['content'],
                'source' => $source,
                'score' => $chunk['score'] ?? 0.0,
            ];
        }

        return $results;
    }
}
