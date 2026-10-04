<?php

declare(strict_types=1);

namespace Intentio\Infrastructure\Storage;

use Intentio\Domain\Cognitive\VectorStoreInterface;
use Intentio\Domain\Space\Space;
use Intentio\Shared\Exceptions\IntentioException;
use SQLite3;

final class SQLiteVectorStore implements VectorStoreInterface
{
    private function getDbDirectoryForSpace(Space $space): string
    {
        return $space->getPath() . '/.intentio_store';
    }

    private function getDbPathForSpace(Space $space): string
    {
        return $this->getDbDirectoryForSpace($space) . '/index.sqlite';
    }

    private function connect(Space $space): SQLite3
    {
        $dbDirectory = $this->getDbDirectoryForSpace($space);

        if (!is_dir($dbDirectory)) {
            if (!mkdir($dbDirectory, 0777, true)) {
                throw new IntentioException("Could not create database directory: {$dbDirectory}");
            }
        }
        try {
            $db = new SQLite3($this->getDbPathForSpace($space));
            $db->enableExceptions(true);
            $db->exec('CREATE TABLE IF NOT EXISTS files (
                path TEXT PRIMARY KEY,
                fingerprint TEXT NOT NULL
            )');
            $db->exec('CREATE TABLE IF NOT EXISTS chunks (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                path TEXT NOT NULL,
                content TEXT NOT NULL,
                metadata TEXT NOT NULL,
                embedding BLOB NOT NULL
            )');
            return $db;
        } catch (\Exception $e) {
            throw new IntentioException("Failed to open the index of space '{$space->getName()}': " . $e->getMessage());
        }
    }

    public function indexedFiles(Space $space): array
    {
        // A space that was never ingested has no index, and reading it must not create one
        if (!file_exists($this->getDbPathForSpace($space))) {
            return [];
        }

        $db = $this->connect($space);
        $results = $db->query('SELECT path, fingerprint FROM files');

        $files = [];
        while ($row = $results->fetchArray(SQLITE3_ASSOC)) {
            $files[$row['path']] = $row['fingerprint'];
        }
        $db->close();

        return $files;
    }

    public function replaceFile(Space $space, string $path, string $fingerprint, array $chunks, array $embeddings): void
    {
        $db = $this->connect($space);
        $db->exec('BEGIN');

        try {
            $this->deleteFile($db, $path);

            $stmt = $db->prepare('INSERT INTO files (path, fingerprint) VALUES (:path, :fingerprint)');
            $stmt->bindValue(':path', $path, SQLITE3_TEXT);
            $stmt->bindValue(':fingerprint', $fingerprint, SQLITE3_TEXT);
            $stmt->execute();

            $stmt = $db->prepare('INSERT INTO chunks (path, content, metadata, embedding) VALUES (:path, :content, :metadata, :embedding)');
            foreach ($chunks as $index => $chunk) {
                $stmt->bindValue(':path', $path, SQLITE3_TEXT);
                $stmt->bindValue(':content', $chunk['content'], SQLITE3_TEXT);
                $stmt->bindValue(':metadata', json_encode($chunk['metadata']), SQLITE3_TEXT);
                // Vectors are stored with length 1, as packed 32-bit floats
                $stmt->bindValue(':embedding', pack('g*', ...$this->normalize($embeddings[$index])), SQLITE3_BLOB);
                $stmt->execute();
            }

            $db->exec('COMMIT');
        } catch (\Throwable $e) {
            $db->exec('ROLLBACK');
            $db->close();
            throw new IntentioException("Failed to index '{$path}' in space '{$space->getName()}': " . $e->getMessage());
        }
        $db->close();
    }

    public function removeFile(Space $space, string $path): void
    {
        $db = $this->connect($space);
        $db->exec('BEGIN');
        $this->deleteFile($db, $path);
        $db->exec('COMMIT');
        $db->close();
    }

    private function deleteFile(SQLite3 $db, string $path): void
    {
        foreach (['DELETE FROM chunks WHERE path = :path', 'DELETE FROM files WHERE path = :path'] as $sql) {
            $stmt = $db->prepare($sql);
            $stmt->bindValue(':path', $path, SQLITE3_TEXT);
            $stmt->execute();
        }
    }

    public function findSimilar(Space $space, array $queryEmbedding, int $limit = 5): array
    {
        if (!file_exists($this->getDbPathForSpace($space))) {
            return [];
        }

        $queryVector = $this->normalize($queryEmbedding);
        $dimensions = count($queryVector);

        $db = $this->connect($space);
        $results = $db->query('SELECT content, metadata, embedding FROM chunks');

        $scoredResults = [];
        while ($row = $results->fetchArray(SQLITE3_ASSOC)) {
            $chunkVector = array_values(unpack('g*', $row['embedding']));
            if (count($chunkVector) !== $dimensions) {
                // Embedded with a different model than the query: not comparable
                continue;
            }

            // Both vectors have length 1, so their dot product is their cosine similarity
            $score = 0.0;
            for ($i = 0; $i < $dimensions; $i++) {
                $score += $queryVector[$i] * $chunkVector[$i];
            }

            // Only include results with a score greater than 0 (i.e., not perfectly orthogonal)
            if ($score > 0) {
                $scoredResults[] = [
                    'content' => $row['content'],
                    'metadata' => json_decode($row['metadata'], true),
                    'score' => $score,
                ];
            }
        }
        $db->close();

        // Sort by score in descending order
        usort($scoredResults, function ($a, $b) {
            return $b['score'] <=> $a['score'];
        });

        return array_slice($scoredResults, 0, $limit);
    }

    public function clear(Space $space): void
    {
        $dbDirectory = $this->getDbDirectoryForSpace($space);
        if (!is_dir($dbDirectory)) {
            return;
        }

        // Every index file is removed, including those written by earlier versions under another name
        foreach (glob($dbDirectory . '/*.sqlite') as $dbPath) {
            if (!unlink($dbPath)) {
                throw new IntentioException("Failed to delete vector store database file for space '{$space->getName()}': {$dbPath}");
            }
        }

        // Also remove the containing directory if it's empty
        if (count(glob($dbDirectory . '/*')) === 0) {
            rmdir($dbDirectory);
        }
    }

    /**
     * Scales a vector to length 1. A zero vector is returned as it is.
     */
    private function normalize(array $vector): array
    {
        $magnitude = 0.0;
        foreach ($vector as $value) {
            $magnitude += $value * $value;
        }
        $magnitude = sqrt($magnitude);

        if ($magnitude == 0.0) {
            return $vector;
        }

        $normalized = [];
        foreach ($vector as $value) {
            $normalized[] = $value / $magnitude;
        }
        return $normalized;
    }
}
