<?php

declare(strict_types=1);

namespace Intentio\Domain\Cognitive;

use Intentio\Domain\Space\Space;
use Intentio\Shared\Exceptions\IntentioException;

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
        if ($fileContent === false) {
            throw new IntentioException("Failed to read prompt file: {$promptPath}");
        }

        $instruction = '';
        $mainContent = $fileContent;
        $contextFiles = [];

        // Parse YAML front-matter
        if (preg_match('/^---\s*(.*?)\s*---(?s)(.*)$/', $fileContent, $matches)) {
            $frontMatterRaw = $matches[1];
            $mainContent = trim($matches[2]);

            // Simple YAML-like parser for front-matter (key: value)
            $frontMatter = [];
            foreach (explode("\n", $frontMatterRaw) as $line) {
                if (str_contains($line, ':')) {
                    list($key, $value) = explode(':', $line, 2);
                    $frontMatter[trim($key)] = trim($value);
                }
            }
            $instruction = $frontMatter['instruction'] ?? '';
        }

        // Identify referenced .md files within the prompt content for contextual knowledge
        // This regex looks for patterns like `filename.md` or `path/filename.md`
        if (preg_match_all('/`?([a-zA-Z0-9_\-\.\/]+\.md)`?/', $mainContent, $matches)) {
            foreach ($matches[1] as $referencedFile) {
                // Construct full path for knowledge files (assuming they are in knowledge/ or subdirs)
                // This logic might need to be more sophisticated if knowledge files can be anywhere
                $possiblePaths = [
                    $space->getPath() . '/knowledge/' . $referencedFile,
                    $space->getPath() . '/knowledge/memory/' . $referencedFile,
                    $space->getPath() . '/knowledge/reference/' . $referencedFile,
                    $space->getPath() . '/knowledge/opinion/' . $referencedFile,
                    // Add more potential knowledge subdirectories as needed
                ];

                foreach ($possiblePaths as $path) {
                    if (file_exists($path) && is_readable($path)) {
                        $contextFiles[basename($path)] = $path; // Store unique basename => full path
                        break;
                    }
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
}