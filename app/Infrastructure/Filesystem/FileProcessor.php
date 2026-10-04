<?php

declare(strict_types=1);

namespace Intentio\Infrastructure\Filesystem;

use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use Intentio\Shared\Exceptions\IntentioException;

final class FileProcessor
{
    /**
     * Scans a directory for relevant files.
     *
     * @param string $directoryPath The path to the directory to scan.
     * @return array An array of file paths.
     */
    public function scanDirectory(string $directoryPath): array
    {
        $files = [];
        if (!is_dir($directoryPath)) {
            return $files;
        }

        $iterator = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($directoryPath, RecursiveDirectoryIterator::SKIP_DOTS),
            RecursiveIteratorIterator::LEAVES_ONLY
        );

        foreach ($iterator as $file) {
            if ($file->isFile() && in_array($file->getExtension(), ['md', 'txt'])) {
                $files[] = $file->getPathname();
            }
        }
        return $files;
    }

    /**
     * Processes a single file and chunks its content.
     *
     * @param string $filePath The path to the file.
     * @param string $category The category of the file (e.g., 'reference', 'memory').
     * @return array An array of chunked content with metadata.
     * @throws IntentioException If the file cannot be read.
     */
    public function process(string $filePath, string $category): array
    {
        if (!file_exists($filePath) || !is_readable($filePath)) {
            throw new IntentioException("File not found or not readable: {$filePath}");
        }

        $content = file_get_contents($filePath);
        if ($content === false) {
            throw new IntentioException("Failed to read file content: {$filePath}");
        }

        if (preg_match('//u', $content) !== 1) {
            throw new IntentioException("File is not valid UTF-8 text: {$filePath}");
        }

        $chunks = [];
        foreach ($this->splitIntoSections($content) as $index => $section) {
            $chunks[] = [
                'content' => $section['text'],
                'metadata' => [
                    'filename' => basename($filePath),
                    'category' => $category,
                    'headings' => $section['headings'],
                    'chunk_index' => $index,
                    'chunk_length' => strlen($section['text']),
                ],
            ];
        }

        return $chunks;
    }

    /**
     * Splits a text at its Markdown headings. A section is a heading with the text under it,
     * and it remembers the headings above it, so a subsection still knows what it belongs to.
     * A text without headings is split into paragraphs instead.
     *
     * @return array An array of sections, each with 'text' and 'headings' (from the top-level heading down to its own).
     */
    private function splitIntoSections(string $content): array
    {
        $sections = [];
        $headings = []; // heading level => title, for the section being read
        $lines = [];
        $inCodeBlock = false;

        // The 'u' modifier matters: without it, \R and \s match single bytes inside multi-byte characters and cut them in two
        foreach (preg_split('/\R/u', $content) as $line) {
            if (preg_match('/^\s{0,3}(```|~~~)/u', $line)) {
                $inCodeBlock = !$inCodeBlock;
            }

            if (!$inCodeBlock && preg_match('/^(#{1,6})\s+(.+?)[\s#]*$/u', $line, $matches)) {
                $sections[] = ['text' => trim(implode("\n", $lines)), 'headings' => array_values($headings)];

                // A new heading closes every heading of the same or a deeper level
                $level = strlen($matches[1]);
                $headings = array_filter($headings, fn (int $openLevel) => $openLevel < $level, ARRAY_FILTER_USE_KEY);
                $headings[$level] = $matches[2];
                $lines = [];
            }

            $lines[] = $line;
        }
        $sections[] = ['text' => trim(implode("\n", $lines)), 'headings' => array_values($headings)];

        if (count($sections) === 1) {
            // No headings: the paragraph is the unit of meaning
            $paragraphs = preg_split('/(\R){2,}/u', $sections[0]['text'], -1, PREG_SPLIT_NO_EMPTY);
            return array_map(fn (string $paragraph) => ['text' => trim($paragraph), 'headings' => []], $paragraphs);
        }

        $sectionsWithContent = [];
        foreach ($sections as $index => $section) {
            // The first section is the text before the first heading; every other one starts with its heading line
            $isOnlyAHeading = $index > 0 && !str_contains($section['text'], "\n");

            // A heading with nothing under it is not kept on its own: its title lives on in the sections below it
            if ($section['text'] !== '' && !$isOnlyAHeading) {
                $sectionsWithContent[] = $section;
            }
        }

        return $sectionsWithContent;
    }
}
