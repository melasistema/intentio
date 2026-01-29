<?php

declare(strict_types=1);

namespace Intentio\Domain\Cognitive;

use Intentio\Domain\Space\Space;
use Intentio\Shared\Exceptions\IntentioException;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;

final class PromptResolver
{
    /**
     * Resolves a prompt for a given cognitive space, extracting its content, instruction, and referenced knowledge files.
     *
     * @param Space $space The cognitive space.
     * @param string $promptKey The key of the prompt to resolve (e.g., 'default', 'analyze_hook').
     * @return array An associative array with 'content', 'instruction', and 'context_files'.
     * @throws IntentioException If the prompt cannot be resolved.
     */
    public function resolve(Space $space, string $promptKey = 'default'): array
    {
        $promptPath = $space->getPromptsPath() . DIRECTORY_SEPARATOR . $promptKey . '.md';

        if (!file_exists($promptPath) || !is_readable($promptPath)) {
            // Fallback to a generic default prompt if a specific one isn't found
            if ($promptKey !== 'default') {
                return $this->resolve($space, 'default');
            }
            throw new IntentioException("Prompt '{$promptKey}' not found or not readable in space '{$space->getName()}'.");
        }

        $fileContent = file_get_contents($promptPath);
        fwrite(STDOUT, "DEBUG: PromptResolver - fileContent type: " . gettype($fileContent) . ", length: " . (is_string($fileContent) ? strlen($fileContent) : 'N/A') . PHP_EOL); // DEBUG
        if ($fileContent === false) {
            throw new IntentioException("Failed to read prompt file: {$promptPath}");
        }

        $instruction = '';
        $mainContent = $fileContent;
        $contextFiles = [];

        // Parse YAML front-matter
        if (preg_match('/^---\s*(.*?)\s*---(?s)(.*)$/', $fileContent, $matches)) {
            fwrite(STDOUT, "DEBUG: PromptResolver - Front-matter matched." . PHP_EOL); // DEBUG
            $frontMatterRaw = $matches[1];
            $mainContent = trim($matches[2]);
            fwrite(STDOUT, "DEBUG: PromptResolver - mainContent after front-matter type: " . gettype($mainContent) . ", length: " . (is_string($mainContent) ? strlen($mainContent) : 'N/A') . PHP_EOL); // DEBUG
            // Simple YAML-like parser for front-matter (key: value)
            $frontMatter = [];
            foreach (explode("\n", $frontMatterRaw) as $line) {
                if (str_contains($line, ':')) {
                    list($key, $value) = explode(':', $line, 2);
                    $frontMatter[trim($key)] = trim($value);
                }
            }
            $instruction = $frontMatter['instruction'] ?? '';
        } else {
            fwrite(STDOUT, "DEBUG: PromptResolver - Front-matter NOT matched." . PHP_EOL); // DEBUG
        }
        fwrite(STDOUT, "DEBUG: PromptResolver - Returning content type: " . gettype($mainContent) . PHP_EOL); // DEBUG

        // Identify referenced .md files within the prompt content for contextual knowledge
        // This regex looks for patterns like `filename.md` or `path/filename.md`
        if (preg_match_all('/`?([a-zA-Z0-9_\-\.\/]+\.md)`?/', $mainContent, $matches)) {
            foreach ($matches[1] as $referencedFile) {
                // Search for the referenced file within the knowledge path of the space
                $foundPath = $this->findFileInKnowledge($space, $referencedFile);
                if ($foundPath) {
                    $contextFiles[basename($foundPath)] = $foundPath; // Store unique basename => full path
                }
            }
        }

        return [
            'content' => $mainContent,
            'instruction' => $instruction,
            'context_files' => array_values($contextFiles), // Return just the paths
        ];
    }

    /**
     * Helper to list available prompt keys in a space.
     * @param Space $space
     * @return string[]
     */
    public function listPromptKeys(Space $space): array
    {
        $promptDir = $space->getPromptsPath();
        if (!is_dir($promptDir)) {
            return [];
        }

        $keys = [];
        $iterator = new \DirectoryIterator($promptDir);
        foreach ($iterator as $fileinfo) {
            if ($fileinfo->isFile() && $fileinfo->getExtension() === 'md') {
                $keys[] = $fileinfo->getBasename('.md');
            }
        }
        sort($keys);
        return $keys;
    }

    /**
     * Recursively searches for a file within the space's knowledge directory.
     * @param Space $space The cognitive space.
     * @param string $filename The name of the file to find (e.g., 'platform_specs.md' or 'memory/past_performance.md').
     * @return string|null The full path to the file if found, otherwise null.
     */
    private function findFileInKnowledge(Space $space, string $filename): ?string
    {
        $knowledgePath = $space->getKnowledgePath();
        if (!is_dir($knowledgePath)) {
            return null;
        }

        // Normalize filename for consistent matching
        $normalizedFilename = str_replace('\\', '/', $filename); // Handle Windows paths if any

        $iterator = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($knowledgePath, RecursiveDirectoryIterator::SKIP_DOTS),
            RecursiveIteratorIterator::LEAVES_ONLY
        );

        foreach ($iterator as $file) {
            if ($file->isFile()) {
                // Check if the exact filename matches (e.g., "file.md")
                if ($file->getFilename() === basename($normalizedFilename)) {
                    // Also ensure that if the referenced filename included a path (e.g., "sub/file.md"),
                    // the found file's relative path matches that part.
                    // This is crude, but better than just basename.
                    $relativePath = str_replace($knowledgePath . '/', '', $file->getPathname());
                    if (str_ends_with($relativePath, $normalizedFilename)) {
                         return $file->getPathname();
                    }
                }
            }
        }
        return null;
    }
}
