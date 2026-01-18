<?php

declare(strict_types=1);

namespace Intentio\Storage;

use Intentio\Cli\Output;
use SQLite3; // Use the native SQLite3 extension

/**
 * A local, SQLite-based vector store.
 *
 * This class represents the storage and retrieval mechanism for
 * document embeddings, utilizing a local SQLite database for persistence.
 * It is inspectable and stores vectors alongside clear metadata.
 */
final class SQLiteVectorStore
{
    private SQLite3 $db;
    private string $fullCognitiveSpacePath; // Changed from knowledgeSpaceName
    private string $dbDirectory;

    public function __construct(string $fullCognitiveSpacePath, string $basePath) // Updated parameter name
    {
        $this->fullCognitiveSpacePath = $fullCognitiveSpacePath;
        $this->dbDirectory = $basePath;
        
        // Ensure the database directory exists
        if (!is_dir($this->dbDirectory)) {
            Output::writeln("Creating SQLite database directory: " . $this->dbDirectory);
            mkdir($this->dbDirectory, 0755, true);
        }

        // Use MD5 hash of the full cognitive space path for a unique and safe filename
        $dbFileName = md5($this->fullCognitiveSpacePath) . '.sqlite';
        $dbFilePath = $this->dbDirectory . DIRECTORY_SEPARATOR . $dbFileName;
        
        try {
            $this->db = new SQLite3($dbFilePath);
            $this->db->busyTimeout(5000); // Set a busy timeout for concurrent access safety
            $this->createSchema();
        } catch (\Exception $e) {
            Output::error("Failed to open or create SQLite database: " . $e->getMessage());
            throw new \RuntimeException("Could not initialize SQLiteVectorStore for '{$this->fullCognitiveSpacePath}'.", 0, $e);
        }
    }

    private function createSchema(): void
    {
        $this->db->exec('CREATE TABLE IF NOT EXISTS chunks (
            id TEXT PRIMARY KEY,
            content TEXT NOT NULL,
            metadata_json TEXT NOT NULL,
            vector_json TEXT NOT NULL
        )');
    }

    /**
     * Adds a content chunk and its embedding to the store.
     *
     * @param array $chunkData An associative array containing 'content' (string) and 'metadata' (array).
     * @param array $vector The embedding vector as an array of floats.
     */
    public function add(array $chunkData, array $vector): void
    {
        if (!isset($chunkData['content'])) {
            throw new \InvalidArgumentException("Chunk data must contain 'content'.");
        }

        // Generate a unique ID for the chunk (can be improved with persistent IDs from source)
        $id = md5($chunkData['content'] . json_encode($chunkData['metadata'])); // More robust ID

        $stmt = $this->db->prepare('INSERT OR REPLACE INTO chunks (id, content, metadata_json, vector_json) VALUES (:id, :content, :metadata, :vector)');
        $stmt->bindValue(':id', $id, SQLITE3_TEXT);
        $stmt->bindValue(':content', $chunkData['content'], SQLITE3_TEXT);
        $stmt->bindValue(':metadata', json_encode($chunkData['metadata']), SQLITE3_TEXT);
        $stmt->bindValue(':vector', json_encode($vector), SQLITE3_TEXT);
        
        $result = $stmt->execute();
        if ($result === false) {
            throw new \RuntimeException("Failed to add chunk to SQLite database: " . $this->db->lastErrorMsg());
        }
        $result->finalize();
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
        // For now, load all vectors and perform similarity search in PHP.
        // For very large datasets, this would need optimization (e.g., in-db vector search, FAISS).
        $allChunks = $this->getAllChunks();

        if (empty($allChunks)) {
            return [];
        }

        $similarities = [];
        foreach ($allChunks as $chunk) {
            // Ensure vector is decoded from JSON
            $chunkVector = json_decode($chunk['vector_json'], true);
            if (!is_array($chunkVector)) {
                Output::error("Corrupt vector_json found for chunk ID: " . $chunk['id']);
                continue;
            }
            $similarity = $this->cosineSimilarity($queryVector, $chunkVector);
            $similarities[$chunk['id']] = $similarity;
        }

        arsort($similarities); // Sort by similarity in descending order

        $results = [];
        $count = 0;
        foreach ($similarities as $id => $score) {
            if ($count >= $limit) {
                break;
            }
            // Retrieve full chunk data for the top N similar ones
            $stmt = $this->db->prepare('SELECT content, metadata_json FROM chunks WHERE id = :id');
            $stmt->bindValue(':id', $id, SQLITE3_TEXT);
            $result = $stmt->execute();
            $row = $result->fetchArray(SQLITE3_ASSOC);
            $result->finalize();

            if ($row) {
                $results[] = [
                    'content' => $row['content'],
                    'metadata' => json_decode($row['metadata_json'], true),
                    'score' => $score,
                ];
            }
            $count++;
        }

        return $results;
    }

    /**
     * Retrieves all chunks from the database.
     */
    private function getAllChunks(): array
    {
        $chunks = [];
        $result = $this->db->query('SELECT id, vector_json FROM chunks');
        while ($row = $result->fetchArray(SQLITE3_ASSOC)) {
            $chunks[] = $row;
        }
        $result->finalize();
        return $chunks;
    }


    // The save() and load() methods are no longer needed as SQLite handles persistence directly.
    // public function save(): void {}
    // private function load(): void {}

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
    
    // Destructor to close the database connection
    public function __destruct()
    {
        if (isset($this->db)) {
            $this->db->close();
        }
    }
}
