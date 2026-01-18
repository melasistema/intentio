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
        private readonly string $promptTemplatesPath,
        private readonly array $context,
        private readonly string $query
    ) {
        $this->loadTemplate();
    }

    private function loadTemplate(): void
    {
        $templateFile = $this->promptTemplatesPath . DIRECTORY_SEPARATOR . $this->templateName . '.md'; // Assuming markdown files

        if (!file_exists($templateFile) || !is_readable($templateFile)) {
            Output::error("Prompt template file not found or not readable: {$templateFile}");
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
     * Lists all available prompt template names (without extensions) from a given path.
     *
     * @param string $templatesPath The directory where prompt template files are stored.
     * @return array An array of template names.
     */
    public static function getAvailableTemplates(string $templatesPath): array
    {
        $templateNames = [];
        if (is_dir($templatesPath)) {
            $items = scandir($templatesPath);
            foreach ($items as $item) {
                if ($item === '.' || $item === '..') {
                    continue;
                }
                $fileInfo = pathinfo($item);
                if (isset($fileInfo['extension']) && $fileInfo['extension'] === 'md') { // Assuming .md templates
                    $templateNames[] = $fileInfo['filename'];
                }
            }
        }
        return $templateNames;
    }
}

