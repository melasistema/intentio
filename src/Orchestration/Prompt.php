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
    private string $templateContent;

    public function __construct(
        private readonly string $templateName,
        private readonly string $globalPromptTemplatesPath, // Renamed for clarity
        private readonly array $context,
        private readonly string $query,
        private readonly ?string $packageName = null // New optional parameter
    ) {
        $this->loadTemplate();
    }

    private function loadTemplate(): void
    {
        $templateFile = null;

        // 1. Try to load from the package-specific prompts directory
        if ($this->packageName !== null) {
            $packagePromptPath = $_SERVER['PWD'] . '/packages/' . $this->packageName . '/prompts/' . $this->templateName . '.md';
            if (file_exists($packagePromptPath) && is_readable($packagePromptPath)) {
                $templateFile = $packagePromptPath;
            }
        }

        // 2. If not found in package, fall back to global prompts directory
        if ($templateFile === null) {
            $globalTemplateFile = $this->globalPromptTemplatesPath . DIRECTORY_SEPARATOR . $this->templateName . '.md';
            if (file_exists($globalTemplateFile) && is_readable($globalTemplateFile)) {
                $templateFile = $globalTemplateFile;
            }
        }

        if ($templateFile === null) {
            Output::error("Prompt template '{$this->templateName}' not found in package '{$this->packageName}' or global prompts directory.");
            throw new \RuntimeException("Prompt template '{$this->templateName}' not found.");
        }

        $this->templateContent = file_get_contents($templateFile);
        if ($this->templateContent === false) {
            Output::error("Failed to read prompt template file: {$templateFile}");
            throw new \RuntimeException("Failed to load prompt template content.");
        }
    }

    public function build(): string
    {
        $contextString = implode("\n---\n", $this->context);

        $finalPrompt = str_replace(
            ['{{CONTEXT}}', '{{QUERY}}'],
            [$contextString, $this->query],
            $this->templateContent
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
    public static function getAvailableTemplates(string $globalTemplatesPath, ?string $packageName = null): array
    {
        $allTemplates = [];

        // Scan global templates
        $global = self::scanTemplatesInPath($globalTemplatesPath);
        foreach ($global as $template) {
            $allTemplates[$template] = $template; // Use name as key to handle uniqueness
        }

        // Scan package-specific templates if packageName is provided
        if ($packageName !== null) {
            $packageTemplatesPath = $_SERVER['PWD'] . '/packages/' . $packageName . '/prompts';
            if (is_dir($packageTemplatesPath)) {
                $package = self::scanTemplatesInPath($packageTemplatesPath);
                foreach ($package as $template) {
                    $allTemplates[$template] = $template; // Package templates overwrite global ones if names conflict
                }
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


