<?php

declare(strict_types=1);

namespace Intentio\Orchestration;

use Intentio\Cli\Output;

/**
 * Builds the final prompt for the language model.
 *
 * Prompts are not tricks; they are values, boundaries, and tone-setters.
 * This class is responsible for assembling the retrieved context and the
 * user's query into a clear, minimal, and effective prompt that respects
 * the defined cognitive space.
 */
final class Prompt
{
    public readonly string $templateBody;
    public readonly ?string $instruction;

    public function __construct(
        private readonly string $templateName,
        string $templateBody,
        ?string $instruction,
        private readonly array $context,
        private readonly string $query,
        private readonly ?string $packageName = null
    ) {
        $this->templateBody = $templateBody;
        $this->instruction = $instruction;
    }

    /**
     * Factory method to create a Prompt instance from a template file.
     */
    public static function fromTemplateFile(
        string $templateName,
        array $context,
        string $query,
        ?string $packageName = null
    ): self {
        if ($packageName === null) {
            Output::error("Cannot load prompt template '{$templateName}': No package is active.");
            throw new \RuntimeException("Cannot load prompt: No package/space is active.");
        }

        $templateFile = $_SERVER['PWD'] . '/packages/' . $packageName . '/prompts/' . $templateName . '.md';

        if (!file_exists($templateFile) || !is_readable($templateFile)) {
            Output::error("Prompt template '{$templateName}' not found in package '{$packageName}'.");
            throw new \RuntimeException("Prompt template '{$templateName}' not found in '{$packageName}'.");
        }

        $rawContent = file_get_contents($templateFile);
        if ($rawContent === false) {
            Output::error("Failed to read prompt template file: {$templateFile}");
            throw new \RuntimeException("Failed to load prompt template content.");
        }

        return self::parseFileContent($templateName, $rawContent, $context, $query, $packageName);
    }

    /**
     * Static method to parse front matter and content from a raw string.
     * Returns a new Prompt instance.
     */
    private static function parseFileContent(
        string $templateName,
        string $rawContent,
        array $context,
        string $query,
        ?string $packageName = null
    ): self {
        $instruction = null;
        $templateBody = $rawContent;

        $pattern = '/^---\s*\R(.*?)\R---\s*\R/s';
        if (preg_match($pattern, $rawContent, $matches)) {
            $frontMatter = $matches[1];
            $templateBody = str_replace($matches[0], '', $rawContent);

            // Simple regex to parse 'instruction' key
            if (preg_match('/^instruction:\s*("?)(.*?)\1\s*$/m', $frontMatter, $instructionMatches)) {
                $instruction = $instructionMatches[2];
            }
        }

        return new self($templateName, $templateBody, $instruction, $context, $query, $packageName);
    }

    public function build(): string
    {
        $contextString = implode("\n---\n", $this->context);

        $finalPrompt = str_replace(
            ['{{CONTEXT}}', '{{QUERY}}'],
            [$contextString, $this->query],
            $this->templateBody
        );

        return $finalPrompt;
    }

    /**
     * Lists all available prompt template names (without extensions) from given paths.
     * Prioritizes package-specific templates over global ones if names conflict.
     *
     * @param string $globalTemplatesPath The directory where global prompt template files are stored.
     * @param ?string $packageName Optional. The name of the active package to also scan for templates.
     * @return array An array of unique template names.
     */
    public static function getAvailableTemplates(?string $packageName = null): array
    {
        if ($packageName === null) {
            return []; // No package, no templates
        }

        $allTemplates = [];

        $packageTemplatesPath = $_SERVER['PWD'] . '/packages/' . $packageName . '/prompts';
        if (is_dir($packageTemplatesPath)) {
            $package = self::scanTemplatesInPath($packageTemplatesPath);
            foreach ($package as $template) {
                $allTemplates[$template] = $template;
            }
        }
        
        ksort($allTemplates); // Sort alphabetically for consistent display

        return array_values($allTemplates); // Return only the names
    }

    /**
     * Helper to scan a single directory for .md template files.
     *
     * @param string $path The directory to scan.
     * @return array An array of template names found in the path.
     */
    public static function scanTemplatesInPath(string $path): array
    {
        $templateNames = [];
        if (is_dir($path)) {
            $items = scandir($path);
            foreach ($items as $item) {
                if ($item === '.' || $item === '..') {
                    continue;
                }
                $fileInfo = pathinfo($item);
                if (isset($fileInfo['extension']) && $fileInfo['extension'] === 'md') {
                    $templateNames[] = $fileInfo['filename'];
                }
            }
        }
        return $templateNames;
    }
}


