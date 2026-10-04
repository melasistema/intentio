<?php

declare(strict_types=1);

namespace Intentio\Tests\Unit;

use Intentio\Infrastructure\Storage\SQLiteVectorStore;
use Intentio\Tests\SpaceTestCase;

/**
 * How the index stores files and finds the chunks closest to a query.
 * The vectors here have two dimensions, so each similarity can be worked out by hand.
 */
final class SQLiteVectorStoreTest extends SpaceTestCase
{
    public function testChunksAreReturnedBestFirstWithTheirCosineSimilarity(): void
    {
        $store = new SQLiteVectorStore();
        $this->index($store, 'notes.md', ['opposite' => [-1.0, 0.0], 'diagonal' => [1.0, 1.0], 'same' => [1.0, 0.0], 'unrelated' => [0.0, 1.0]]);

        $found = $store->findSimilar($this->space, [1.0, 0.0], 10, -1.0);

        $this->assertSame(['same', 'diagonal', 'unrelated', 'opposite'], array_column($found, 'content'));
        $this->assertEqualsWithDelta([1.0, 0.7071, 0.0, -1.0], array_column($found, 'score'), 0.0001);
    }

    public function testTheLengthOfAVectorDoesNotChangeItsSimilarity(): void
    {
        $store = new SQLiteVectorStore();
        $this->index($store, 'notes.md', ['long' => [50.0, 0.0], 'short' => [0.02, 0.0]]);

        $found = $store->findSimilar($this->space, [3.0, 0.0], 10, 0.0);

        $this->assertEqualsWithDelta([1.0, 1.0], array_column($found, 'score'), 0.0001);
    }

    public function testChunksBelowTheMinimumSimilarityAreLeftOut(): void
    {
        $store = new SQLiteVectorStore();
        $this->index($store, 'notes.md', ['same' => [1.0, 0.0], 'diagonal' => [1.0, 1.0], 'unrelated' => [0.0, 1.0]]);

        $this->assertSame(['same', 'diagonal'], array_column($store->findSimilar($this->space, [1.0, 0.0], 10, 0.5), 'content'));
        $this->assertSame(['same'], array_column($store->findSimilar($this->space, [1.0, 0.0], 10, 0.9), 'content'));
        $this->assertSame([], $store->findSimilar($this->space, [0.0, -1.0], 10, 0.5));
    }

    public function testNoMoreThanTheLimitIsReturned(): void
    {
        $store = new SQLiteVectorStore();
        $this->index($store, 'notes.md', ['unrelated' => [0.0, 1.0], 'diagonal' => [1.0, 1.0], 'same' => [1.0, 0.0]]);

        $found = $store->findSimilar($this->space, [1.0, 0.0], 2, -1.0);

        $this->assertSame(['same', 'diagonal'], array_column($found, 'content'));
    }

    public function testExcludedFilesAreLeftOut(): void
    {
        $store = new SQLiteVectorStore();
        $this->index($store, 'pinned.md', ['from the pinned file' => [1.0, 0.0]]);
        $this->index($store, 'frameworks/other.md', ['from another file' => [1.0, 0.1]]);

        $found = $store->findSimilar($this->space, [1.0, 0.0], 10, 0.0, ['pinned.md']);

        $this->assertSame(['from another file'], array_column($found, 'content'));
    }

    public function testAChunkEmbeddedWithAnotherNumberOfDimensionsIsNotCompared(): void
    {
        $store = new SQLiteVectorStore();
        $this->index($store, 'old.md', ['three dimensions' => [1.0, 0.0, 0.0]]);
        $this->index($store, 'new.md', ['two dimensions' => [1.0, 0.0]]);

        $found = $store->findSimilar($this->space, [1.0, 0.0], 10, -1.0);

        $this->assertSame(['two dimensions'], array_column($found, 'content'));
    }

    public function testAVectorOfZeroesIsStoredAndScoresZero(): void
    {
        $store = new SQLiteVectorStore();
        $this->index($store, 'notes.md', ['nothing' => [0.0, 0.0]]);

        $found = $store->findSimilar($this->space, [1.0, 0.0], 10, -1.0);

        $this->assertSame([0.0], array_column($found, 'score'));
    }

    public function testContentAndMetadataComeBackAsTheyWereStored(): void
    {
        $store = new SQLiteVectorStore();
        $chunk = [
            'content' => "## Caffè\nIl caffè costa 1 € in città; 日本語.",
            'metadata' => ['relative_path' => 'città/caffè.md', 'headings' => ['Menù', 'Caffè'], 'chunk_index' => 0],
        ];
        $store->replaceFile($this->space, 'città/caffè.md', 'fingerprint', [$chunk], [[1.0, 0.0]]);

        $found = $store->findSimilar($this->space, [1.0, 0.0], 10, 0.0);

        $this->assertSame($chunk['content'], $found[0]['content']);
        $this->assertSame($chunk['metadata'], $found[0]['metadata']);
    }

    public function testIndexingAFileAgainReplacesItsChunks(): void
    {
        $store = new SQLiteVectorStore();
        $this->index($store, 'notes.md', ['first version' => [1.0, 0.0], 'also first version' => [1.0, 0.1]], 'fingerprint-1');
        $this->index($store, 'notes.md', ['second version' => [1.0, 0.0]], 'fingerprint-2');

        $found = $store->findSimilar($this->space, [1.0, 0.0], 10, -1.0);

        $this->assertSame(['second version'], array_column($found, 'content'));
        $this->assertSame(['notes.md' => 'fingerprint-2'], $store->indexedFiles($this->space));
    }

    public function testARemovedFileLeavesTheOthersInPlace(): void
    {
        $store = new SQLiteVectorStore();
        $this->index($store, 'kept.md', ['kept' => [1.0, 0.0]], 'fingerprint-kept');
        $this->index($store, 'removed.md', ['removed' => [1.0, 0.0]], 'fingerprint-removed');

        $store->removeFile($this->space, 'removed.md');

        $this->assertSame(['kept.md' => 'fingerprint-kept'], $store->indexedFiles($this->space));
        $this->assertSame(['kept'], array_column($store->findSimilar($this->space, [1.0, 0.0], 10, -1.0), 'content'));
    }

    public function testReadingASpaceThatWasNeverIngestedCreatesNoIndex(): void
    {
        $store = new SQLiteVectorStore();

        $this->assertSame([], $store->indexedFiles($this->space));
        $this->assertSame([], $store->findSimilar($this->space, [1.0, 0.0], 10, -1.0));
        $this->assertDirectoryDoesNotExist($this->space->getPath() . '/.intentio_store');
    }

    public function testClearRemovesTheIndex(): void
    {
        $store = new SQLiteVectorStore();
        $this->index($store, 'notes.md', ['a chunk' => [1.0, 0.0]]);

        $store->clear($this->space);

        $this->assertSame([], $store->indexedFiles($this->space));
        $this->assertDirectoryDoesNotExist($this->space->getPath() . '/.intentio_store');
    }

    /**
     * Indexes one file whose chunks are given as content => vector.
     */
    private function index(SQLiteVectorStore $store, string $path, array $vectorsByContent, string $fingerprint = 'fingerprint'): void
    {
        $chunks = [];
        foreach (array_keys($vectorsByContent) as $content) {
            $chunks[] = ['content' => $content, 'metadata' => ['relative_path' => $path, 'headings' => []]];
        }

        $store->replaceFile($this->space, $path, $fingerprint, $chunks, array_values($vectorsByContent));
    }
}
