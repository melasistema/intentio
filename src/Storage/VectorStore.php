<?php

declare(strict_types=1);

namespace Intentio\Storage; // Updated namespace

use Intentio\Cli\Output;

/**
 * A local, file-based vector store.
 *
 * This class represents the storage and retrieval mechanism for
 * document embeddings. It is local, inspectable, and should
 * store vectors alongside clear metadata.
 */
final class VectorStore
{
    private string $dbPath;
    private array $vectors = []; // Stores ['id' => ['content' => '...', 'metadata' => [...], 'vector' => [...]]]

    public function __construct(string $knowledgeSpaceName, string $basePath)
    {
        // Each knowledge space gets its own vector store.
        $this->dbPath = "{$basePath}/{$knowledgeSpaceName}.json";
        $this->load();
    }

    /**
     * Adds a content chunk and its embedding to the store.
     *
     * @param array $chunkData An associative array containing 'content' (string) and 'metadata' (array).
     * @param array $vector The embedding vector (array of floats).
     */
    public function add(array $chunkData, array $vector): void
    {
        if (!isset($chunkData['content'])) {
            throw new \InvalidArgumentException("Chunk data must contain 'content'.");
        }

        // Generate a unique ID for the chunk (can be improved with persistent IDs)
        $id = md5($chunkData['content']); 
        
        $this->vectors[$id] = [
            'content' => $chunkData['content'],
            'metadata' => $chunkData['metadata'] ?? [],
            'vector' => $vector,
        ];
    }

    /**
     * Finds similar content chunks based on a query vector.
     *
     * @param array $queryVector The embedding vector of the query.
     * @param int $limit The maximum number of similar chunks to return.
     * @return array An array of similar chunks, sorted by similarity score (highest first).
     */
    public function findSimilar(array $queryVector, int $limit = 5): array
    {
        if (empty($this->vectors)) {
            return [];
        }

        $similarities = [];
        foreach ($this->vectors as $id => $chunk) {
            $similarity = $this->cosineSimilarity($queryVector, $chunk['vector']);
            $similarities[$id] = $similarity;
        }

        arsort($similarities); // Sort by similarity in descending order

        $results = [];
        $count = 0;
        foreach ($similarities as $id => $score) {
            if ($count >= $limit) {
                break;
            }
            $results[] = [
                'content' => $this->vectors[$id]['content'],
                'metadata' => $this->vectors[$id]['metadata'],
                'score' => $score,
            ];
            $count++;
        }

        return $results;
    }

    public function save(): void
    {
        $dir = dirname($this->dbPath);
        if (!is_dir($dir)) {
            Output::writeln("Creating vector store directory: " . $dir);
            mkdir($dir, 0755, true);
        }
        file_put_contents($this->dbPath, json_encode($this->vectors, JSON_PRETTY_PRINT));
    }

    private function load(): void
    {
        if (file_exists($this->dbPath)) {
            $contents = file_get_contents($this->dbPath);
            if ($contents === false) {
                Output::error("Failed to read vector store file: " . $this->dbPath);
                return;
            }
            $data = json_decode($contents, true);
            if (json_last_error() !== JSON_ERROR_NONE) {
                Output::error("Failed to decode vector store JSON: " . json_last_error_msg());
                return;
            }
            $this->vectors = $data;
        }
    }

    /**
     * Calculates the cosine similarity between two vectors.
     *
     * @param array $vecA The first vector (array of floats).
     * @param array $vecB The second vector (array of floats).
     * @return float The cosine similarity between the two vectors.
     */
    private function cosineSimilarity(array $vecA, array $vecB): float
    {
        if (count($vecA) !== count($vecB)) {
            throw new \InvalidArgumentException("Vectors must have the same dimension for cosine similarity.");
        }

        $dotProduct = 0.0;
        $magnitudeA = 0.0;
        $magnitudeB = 0.0;

        for ($i = 0; $i < count($vecA); $i++) {
            $dotProduct += $vecA[$i] * $vecB[$i];
            $magnitudeA += $vecA[$i] * $vecA[$i];
            $magnitudeB += $vecB[$i] * $vecB[$i];
        }

        $magnitudeA = sqrt($magnitudeA);
        $magnitudeB = sqrt($magnitudeB);

        if ($magnitudeA === 0.0 || $magnitudeB === 0.0) {
            return 0.0; // Avoid division by zero, vectors with zero magnitude are considered to have 0 similarity.
        }

        return $dotProduct / ($magnitudeA * $magnitudeB);
    }
}
