<?php

declare(strict_types=1);

namespace Intentio\Tests\Unit;

use Intentio\Infrastructure\Filesystem\FileProcessor;
use Intentio\Shared\Exceptions\IntentioException;
use Intentio\Tests\SpaceTestCase;

/**
 * How a knowledge file is split into chunks.
 */
final class FileProcessorTest extends SpaceTestCase
{
    public function testAFileIsSplitAtItsHeadings(): void
    {
        $chunks = $this->chunk("# Lean Canvas\nNine blocks.\n\n## Problem\nThe top three problems.\n\n## Solution\nThe smallest fix.\n\n# Pricing\nCharge early.");

        $this->assertSame(
            [
                "# Lean Canvas\nNine blocks.",
                "## Problem\nThe top three problems.",
                "## Solution\nThe smallest fix.",
                "# Pricing\nCharge early.",
            ],
            array_column($chunks, 'content')
        );
    }

    public function testAChunkRemembersTheHeadingsAboveIt(): void
    {
        $chunks = $this->chunk("# Lean Canvas\nNine blocks.\n\n## Problem\nThe top three problems.\n\n### Examples\nLate invoices.\n\n## Solution\nThe smallest fix.\n\n# Pricing\nCharge early.");

        $this->assertSame(
            [
                ['Lean Canvas'],
                ['Lean Canvas', 'Problem'],
                ['Lean Canvas', 'Problem', 'Examples'],
                ['Lean Canvas', 'Solution'],
                ['Pricing'],
            ],
            $this->headingsOf($chunks)
        );
    }

    public function testAHeadingWithNothingUnderItIsNotAChunkButStaysInThePath(): void
    {
        $chunks = $this->chunk("# Lean Canvas\n\n## Problem\nThe top three problems.");

        $this->assertSame(["## Problem\nThe top three problems."], array_column($chunks, 'content'));
        $this->assertSame([['Lean Canvas', 'Problem']], $this->headingsOf($chunks));
    }

    public function testTextBeforeTheFirstHeadingIsAChunkWithoutHeadings(): void
    {
        $chunks = $this->chunk("A note before anything else.\n\n# Lean Canvas\nNine blocks.");

        $this->assertSame('A note before anything else.', $chunks[0]['content']);
        $this->assertSame([], $chunks[0]['metadata']['headings']);
        $this->assertSame(['Lean Canvas'], $chunks[1]['metadata']['headings']);
    }

    public function testClosingHashesAreNotPartOfAHeading(): void
    {
        $chunks = $this->chunk("## Problem ##\nThe top three problems.");

        $this->assertSame([['Problem']], $this->headingsOf($chunks));
    }

    public function testAHashLineInsideACodeBlockIsNotAHeading(): void
    {
        $chunks = $this->chunk("# Setup\nRun this:\n\n```bash\n# install the tool\nmake install\n```\n\nThen restart.");

        $this->assertCount(1, $chunks);
        $this->assertSame([['Setup']], $this->headingsOf($chunks));
        $this->assertStringContainsString('# install the tool', $chunks[0]['content']);
    }

    public function testAFileWithoutHeadingsIsSplitIntoParagraphs(): void
    {
        $chunks = $this->chunk("First paragraph,\non two lines.\n\nSecond paragraph.\n\n\n\nThird paragraph.");

        $this->assertSame(
            ["First paragraph,\non two lines.", 'Second paragraph.', 'Third paragraph.'],
            array_column($chunks, 'content')
        );
        $this->assertSame([[], [], []], $this->headingsOf($chunks));
    }

    public function testWindowsLineEndingsAreSplitTheSameWay(): void
    {
        $chunks = $this->chunk("# Lean Canvas\r\nNine blocks.\r\n\r\n## Problem\r\nThe top three problems.");

        $this->assertSame([['Lean Canvas'], ['Lean Canvas', 'Problem']], $this->headingsOf($chunks));
    }

    /**
     * Characters such as "à" and "Å" contain bytes that, read one byte at a time, look like a space or a line ending.
     * A chunker that reads bytes cuts those characters in two.
     */
    public function testCharactersOutsideAsciiAreKeptWhole(): void
    {
        $text = "# Caffè à la carte\nIl caffè costa 1 € in città.\n\n## Ångström\nÅ è l'unità; 日本語 … fine.";

        $chunks = $this->chunk($text);

        $this->assertSame([['Caffè à la carte'], ['Caffè à la carte', 'Ångström']], $this->headingsOf($chunks));
        $this->assertSame("# Caffè à la carte\nIl caffè costa 1 € in città.", $chunks[0]['content']);
        $this->assertSame("## Ångström\nÅ è l'unità; 日本語 … fine.", $chunks[1]['content']);
    }

    public function testParagraphsWithCharactersOutsideAsciiAreKeptWhole(): void
    {
        $chunks = $this->chunk("La città è là.\n\nÅ sta per Ångström à Paris.");

        $this->assertSame(['La città è là.', 'Å sta per Ångström à Paris.'], array_column($chunks, 'content'));
    }

    public function testAnEmptyFileHasNoChunks(): void
    {
        $this->assertSame([], $this->chunk(''));
        $this->assertSame([], $this->chunk("\n\n  \n"));
    }

    public function testAChunkCarriesItsFileCategoryAndPosition(): void
    {
        $chunks = $this->chunk("# One\nFirst.\n\n# Two\nSecond.", 'frameworks');

        $this->assertSame('notes.md', $chunks[1]['metadata']['filename']);
        $this->assertSame('frameworks', $chunks[1]['metadata']['category']);
        $this->assertSame(1, $chunks[1]['metadata']['chunk_index']);
        $this->assertSame(strlen("# Two\nSecond."), $chunks[1]['metadata']['chunk_length']);
    }

    public function testAFileThatIsNotUtf8IsRefused(): void
    {
        $path = $this->writeFile('knowledge/latin1.md', "# Caff\xE8\nNot UTF-8.");

        $this->expectException(IntentioException::class);
        $this->expectExceptionMessage('not valid UTF-8');
        (new FileProcessor())->process($path, 'knowledge');
    }

    public function testAMissingFileIsRefused(): void
    {
        $this->expectException(IntentioException::class);
        (new FileProcessor())->process($this->space->getKnowledgePath() . '/absent.md', 'knowledge');
    }

    public function testOnlyMarkdownAndTextFilesAreScanned(): void
    {
        $this->writeFile('knowledge/a.md', 'a');
        $this->writeFile('knowledge/deep/er/b.txt', 'b');
        $this->writeFile('knowledge/image.png', 'c');
        $this->writeFile('knowledge/.hidden', 'd');

        $files = (new FileProcessor())->scanDirectory($this->space->getKnowledgePath());
        sort($files);

        $this->assertSame(
            [$this->space->getKnowledgePath() . '/a.md', $this->space->getKnowledgePath() . '/deep/er/b.txt'],
            $files
        );
    }

    private function chunk(string $content, string $category = 'knowledge'): array
    {
        $path = $this->writeFile('knowledge/notes.md', $content);

        return (new FileProcessor())->process($path, $category);
    }

    private function headingsOf(array $chunks): array
    {
        $headings = [];
        foreach ($chunks as $chunk) {
            $headings[] = $chunk['metadata']['headings'];
        }

        return $headings;
    }
}
