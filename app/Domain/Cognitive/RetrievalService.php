<?php

declare(strict_types=1);

namespace Intentio\Domain\Cognitive;

use Intentio\Domain\Space\Space;
use Intentio\Domain\Model\EmbeddingInterface;
use Intentio\Shared\Exceptions\IntentioException;

final class RetrievalService
{
    public function __construct(
        private readonly EmbeddingInterface $embeddingAdapter,
        private readonly VectorStoreInterface $vectorStore
    ) {
    }

    /**
     * Retrieves relevant information for a query from the given cognitive space.
     *
     * @param Space $space The cognitive space to retrieve from.
     * @param string $query The user's query.
     * @param int $limit The maximum number of results to retrieve.
     * @return array An array of retrieved relevant context/documents.
     */
    public function retrieve(Space $space, string $query, int $limit = 5): array
    {
        fwrite(STDOUT, "RetrievalService: Retrieving for query '{$query}' in space '{$space->getName()}'." . PHP_EOL);

        // Ensure the vector store is initialized for this space
        $this->vectorStore->initialize($space);

        // Embed the query
        $queryEmbedding = $this->embeddingAdapter->embed($query);

        fwrite(STDOUT, "RetrievalService: Generated query embedding (length: " . count($queryEmbedding) . ")." . PHP_EOL);

        // Retrieve from vector store
        $retrievedChunks = $this->vectorStore->findSimilar($space, $queryEmbedding, $limit); // Pass Space object

        $results = [];
        foreach ($retrievedChunks as $chunk) {
            $results[] = [
                'content' => $chunk['content'],
                'source' => $chunk['metadata']['filepath'] ?? 'unknown',
                'score' => $chunk['score'] ?? 0.0,
            ];
        }

        return $results;
    }
}
