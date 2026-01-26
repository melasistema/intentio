<?php

declare(strict_types=1);

namespace Intentio\Infrastructure\Storage;

use Intentio\Domain\Cognitive\VectorStoreInterface;
use Intentio\Domain\Space\Space;
use Intentio\Shared\Exceptions\IntentioException;
use SQLite3; // Assuming SQLite3 extension is available

final class SQLiteVectorStore implements VectorStoreInterface
{
    public function __construct()
    {
        // No specific state needed for the SQLiteVectorStore instance itself
        // if it manages connections per operation.
    }

    private function getDbPathForSpace(Space $space): string
    {
        $dbDirectory = $space->getPath() . '/.intentio_store';
        // Use a hash of the space's path for the DB file name to ensure uniqueness and stability
        $dbFileName = md5($space->getPath()) . '.sqlite';
        return $dbDirectory . '/' . $dbFileName;
    }

    private function connect(Space $space): SQLite3
    {
        $dbPath = $this->getDbPathForSpace($space);
        $dbDirectory = dirname($dbPath);

        if (!is_dir($dbDirectory)) {
            if (!mkdir($dbDirectory, 0777, true)) {
                throw new IntentioException("Could not create database directory: {$dbDirectory}");
            }
        }
        try {
            $db = new SQLite3($dbPath);
            $db->enableExceptions(true); // Enable exceptions for better error handling
            return $db;
        } catch (\Exception $e) {
            throw new IntentioException("Failed to connect to SQLite database for space '{$space->getName()}': " . $e->getMessage());
        }
    }

    public function initialize(Space $space): void
    {
        $db = $this->connect($space);
        $db->exec('CREATE TABLE IF NOT EXISTS embeddings (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            content TEXT NOT NULL,
            metadata TEXT NOT NULL,
            embedding BLOB NOT NULL
        )');
        $db->close();
    }

    public function add(Space $space, array $chunkData, array $embedding): void
    {
        $db = $this->connect($space);
        $stmt = $db->prepare('INSERT INTO embeddings (content, metadata, embedding) VALUES (:content, :metadata, :embedding)');
        
        $stmt->bindValue(':content', $chunkData['content'], SQLITE3_TEXT);
        $stmt->bindValue(':metadata', json_encode($chunkData['metadata']), SQLITE3_TEXT);
        $stmt->bindValue(':embedding', json_encode($embedding), SQLITE3_BLOB); // Store embedding as JSON string for simplicity
        
        $result = $stmt->execute();
        if ($result === false) {
            throw new IntentioException("Failed to add embedding to database for space '{$space->getName()}': " . $db->lastErrorMsg());
        }
        $db->close();
    }

    public function findSimilar(Space $space, array $queryEmbedding, int $limit = 5): array
    {
        $db = $this->connect($space);
        
        $results = $db->query('SELECT id, content, metadata, embedding FROM embeddings');
        
        if ($results === false) {
            throw new IntentioException("Failed to query embeddings for space '{$space->getName()}': " . $db->lastErrorMsg());
        }

        $allEmbeddings = [];
        while ($row = $results->fetchArray(SQLITE3_ASSOC)) {
            $rowEmbedding = json_decode($row['embedding'], true);
            if (json_last_error() !== JSON_ERROR_NONE) {
                // Log or handle malformed embedding data
                continue;
            }
            $allEmbeddings[] = [
                'id' => $row['id'],
                'content' => $row['content'],
                'metadata' => json_decode($row['metadata'], true),
                'embedding' => $rowEmbedding,
            ];
        }
        $db->close();

        $scoredResults = [];
        foreach ($allEmbeddings as $data) {
            $score = $this->cosineSimilarity($queryEmbedding, $data['embedding']);
            // Only include results with a score greater than 0 (i.e., not perfectly orthogonal)
            if ($score > 0) {
                 $scoredResults[] = array_merge($data, ['score' => $score]);
            }
        }

        // Sort by score in descending order
        usort($scoredResults, function($a, $b) {
            return $b['score'] <=> $a['score'];
        });

        return array_slice($scoredResults, 0, $limit);
    }

    public function clear(Space $space): void
    {
        $dbPath = $this->getDbPathForSpace($space);
        
        if (file_exists($dbPath)) {
            if (unlink($dbPath)) {
                // Also remove the containing directory if it's empty
                $dbDirectory = dirname($dbPath);
                if (is_dir($dbDirectory) && count(glob($dbDirectory . '/*')) === 0) {
                    rmdir($dbDirectory);
                }
            } else {
                throw new IntentioException("Failed to delete vector store database file for space '{$space->getName()}': {$dbPath}");
            }
        }
    }

    private function cosineSimilarity(array $vecA, array $vecB): float
    {
        if (empty($vecA) || empty($vecB) || count($vecA) !== count($vecB)) {
            return 0.0; // Invalid input
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

        if ($magnitudeA == 0.0 || $magnitudeB == 0.0) {
            return 0.0; // Avoid division by zero
        }

        return $dotProduct / ($magnitudeA * $magnitudeB);
    }
}