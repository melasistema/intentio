<?php

declare(strict_types=1);

namespace Intentio\Domain\Cognitive;

use Intentio\Domain\Space\Space;

interface VectorStoreInterface
{
    /**
     * Lists the files currently in the index of a space.
     *
     * @param Space $space The cognitive space.
     * @return array<string, string> The fingerprint each file had when it was indexed, keyed by its path relative to the knowledge folder.
     */
    public function indexedFiles(Space $space): array;

    /**
     * Replaces everything the index holds for one file, as a single operation:
     * the file is either indexed completely or left as it was.
     *
     * @param Space $space The cognitive space.
     * @param string $path The file's path relative to the knowledge folder.
     * @param string $fingerprint The fingerprint of the file's current content.
     * @param array $chunks The file's chunks, each an associative array containing 'content' and 'metadata'.
     * @param array $embeddings The vector representation of each chunk, in the same order.
     */
    public function replaceFile(Space $space, string $path, string $fingerprint, array $chunks, array $embeddings): void;

    /**
     * Removes one file and its chunks from the index.
     *
     * @param Space $space The cognitive space.
     * @param string $path The file's path relative to the knowledge folder.
     */
    public function removeFile(Space $space, string $path): void;

    /**
     * Finds similar content based on a query embedding within a specific space.
     *
     * @param Space $space The cognitive space.
     * @param array $queryEmbedding The embedding of the query.
     * @param int $limit The maximum number of similar chunks to return.
     * @param float $minScore The lowest similarity a chunk needs to be returned.
     * @param string[] $excludedPaths Files to leave out, as paths relative to the knowledge folder.
     * @return array An array of associative arrays, each containing 'content', 'metadata', and 'score', best first.
     */
    public function findSimilar(Space $space, array $queryEmbedding, int $limit, float $minScore, array $excludedPaths = []): array;

    /**
     * Clears all data from the vector store for a given space.
     *
     * @param Space $space The cognitive space to clear.
     */
    public function clear(Space $space): void;
}
