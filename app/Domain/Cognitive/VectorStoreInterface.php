<?php

declare(strict_types=1);

namespace Intentio\Domain\Cognitive;

use Intentio\Domain\Space\Space;

interface VectorStoreInterface
{
    /**
     * Initializes the vector store for a specific space.
     * This might involve creating a database file or tables.
     *
     * @param Space $space The cognitive space to initialize.
     */
    public function initialize(Space $space): void;

    /**
     * Adds a chunk of content with its metadata and embedding to the vector store for a specific space.
     *
     * @param Space $space The cognitive space.
     * @param array $chunkData Associative array containing 'content' and 'metadata'.
     * @param array $embedding The vector representation of the content.
     */
    public function add(Space $space, array $chunkData, array $embedding): void;

    /**
     * Finds similar content based on a query embedding within a specific space.
     *
     * @param Space $space The cognitive space.
     * @param array $queryEmbedding The embedding of the query.
     * @param int $limit The maximum number of similar chunks to return.
     * @return array An array of associative arrays, each containing 'content', 'metadata', and 'score'.
     */
    public function findSimilar(Space $space, array $queryEmbedding, int $limit = 5): array;

    /**
     * Clears all data from the vector store for a given space.
     *
     * @param Space $space The cognitive space to clear.
     */
    public function clear(Space $space): void;
}