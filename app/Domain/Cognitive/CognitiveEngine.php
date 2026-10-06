<?php

declare(strict_types=1);

namespace Intentio\Domain\Cognitive;

use Intentio\Domain\Space\Space;
use Intentio\Domain\Model\LLMInterface;
use Intentio\Domain\Model\ImageRendererInterface;
use Intentio\Shared\Exceptions\IntentioException;

final readonly class CognitiveEngine
{
    public function __construct(
        private LLMInterface         $llmAdapter,
        private IngestionService     $ingestionService,
        private RetrievalService     $retrievalService,
        private VectorStoreInterface $vectorStore,
        private ImageRendererInterface $imageRenderer,
        private int $contextWindow
    )
    {
    }

    /**
     * Brings the index of a space up to date with its knowledge folder.
     *
     * @return array The number of files 'indexed', 'removed', 'unchanged' and 'failed'.
     */
    public function ingest(Space $space): array
    {
        return $this->ingestionService->ingestSpace($space);
    }

    /**
     * Reports how the knowledge folder of a space differs from its index.
     *
     * @return array 'changed' (path => fingerprint), 'removed' (paths) and 'unchanged' (a count).
     */
    public function pendingChanges(Space $space): array
    {
        return $this->ingestionService->pendingChanges($space);
    }

    /**
     * Answers a query inside a space.
     *
     * @param string $promptTemplate The prompt template to use. Without one, the query itself is the prompt.
     * @param string[] $pinnedFiles Paths of knowledge files the template names, loaded in full.
     * @param callable|null $onText Called with each piece of the answer as the model writes it.
     * @return array The 'answer' and what it was built from: 'pinned' (files given in full, as paths relative
     *               to the knowledge folder), 'retrieved' (passages, each with 'source' and 'score'),
     *               and 'warning' (null, or why the model may not have seen all of it).
     */
    public function chat(Space $space, string $message, string $promptTemplate = '{{QUERY}}', array $pinnedFiles = [], ?callable $onText = null): array
    {
        $pinnedPaths = [];
        foreach ($pinnedFiles as $filePath) {
            $pinnedPaths[] = substr($filePath, strlen($space->getKnowledgePath()) + 1);
        }

        // A pinned file is already given in full, so its passages are not retrieved a second time
        $retrievedChunks = $this->retrievalService->retrieve($space, $message, $pinnedPaths);
        $prompt = $this->assemblePrompt($promptTemplate, $message, $this->formatContext($pinnedFiles, $retrievedChunks));

        $retrieved = [];
        foreach ($retrievedChunks as $chunk) {
            $retrieved[] = ['source' => $chunk['source'], 'score' => $chunk['score']];
        }

        return [
            'answer' => $this->llmAdapter->generate($prompt, $onText),
            'pinned' => $pinnedPaths,
            'retrieved' => $retrieved,
            'warning' => $this->contextWindowWarning($prompt),
        ];
    }

    /**
     * Compares the size of a prompt with the model's context window.
     * The size is an estimate: a token is between three and four bytes of English text, and 3.5 is used.
     */
    private function contextWindowWarning(string $prompt): ?string
    {
        $estimatedTokens = (int) ceil(strlen($prompt) / 3.5);

        if ($estimatedTokens <= $this->contextWindow) {
            return null;
        }

        return "the prompt is about {$estimatedTokens} tokens and the context window is {$this->contextWindow}, "
            . "so the model did not see all of it. Pin fewer files, or use a model with a larger window and set llm.context_window to it.";
    }

    /**
     * Builds the one text the model receives: the knowledge in scope and the prompt template with the query.
     * A template places the knowledge itself with {{CONTEXT}}; without that placeholder, the knowledge comes first.
     */
    private function assemblePrompt(string $template, string $query, string $context): string
    {
        if (!str_contains($template, '{{QUERY}}')) {
            $template .= "\n\n{{QUERY}}";
        }

        if (!str_contains($template, '{{CONTEXT}}') && $context !== '') {
            $template = "Context:\n{{CONTEXT}}\n\n" . $template;
        }

        // strtr replaces both placeholders in one pass, so a placeholder written inside the knowledge or the query is left as it is
        return strtr($template, ['{{CONTEXT}}' => $context, '{{QUERY}}' => $query]);
    }

    /**
     * Formats the knowledge in scope: the pinned files in full, then the retrieved passages with their source.
     */
    private function formatContext(array $pinnedFiles, array $retrievedChunks): string
    {
        $sections = [];

        $pinned = [];
        foreach ($pinnedFiles as $filePath) {
            if (file_exists($filePath) && is_readable($filePath)) {
                $pinned[] = "--- Content from " . basename($filePath) . " ---\n" . trim(file_get_contents($filePath));
            }
        }
        if (!empty($pinned)) {
            $sections[] = "### Knowledge Base\n" . implode("\n\n", $pinned);
        }

        $retrieved = [];
        foreach ($retrievedChunks as $chunk) {
            $retrieved[] = "Source: " . $chunk['source'] . "\nContent: " . $chunk['content'];
        }
        if (!empty($retrieved)) {
            $sections[] = "### Retrieved Information\n" . implode("\n\n", $retrieved);
        }

        return implode("\n\n", $sections);
    }

    /**
     * Renders an image from a prompt into the space's own image folder.
     *
     * @param string[] $uses Names of kept images the image model is shown, in the order the prompt refers to them.
     * @return string The path to the rendered image.
     */
    public function render(Space $space, string $prompt, array $uses = []): string
    {
        $missing = $this->missingImages($space, $uses);
        if (!empty($missing)) {
            throw new IntentioException("The space has no kept image named '" . implode("', '", $missing) . "'.");
        }

        $referenceImages = [];
        foreach ($uses as $name) {
            $referenceImages[] = $this->keptImagePath($space, $name);
        }

        return $this->imageRenderer->render($prompt, $space->getPath() . '/renderer_images', $referenceImages);
    }

    /**
     * Keeps a rendered image under a name, so that later renders in this space can use it.
     * The image is copied: the render itself stays where it is, and so does every render kept under this name before.
     *
     * @return string The path of the kept image.
     */
    public function keepImage(Space $space, string $imagePath, string $name): string
    {
        $keptPath = $this->keptImagePath($space, $name);

        if (!is_dir(dirname($keptPath)) && !mkdir(dirname($keptPath), 0777, true)) {
            throw new IntentioException("Failed to create the folder for kept images: '" . dirname($keptPath) . "'.");
        }
        if (!copy($imagePath, $keptPath)) {
            throw new IntentioException("Failed to keep '{$imagePath}' as '{$name}'.");
        }

        return $keptPath;
    }

    /**
     * Tells which of the named images the space has not kept yet.
     *
     * @param string[] $names
     * @return string[]
     */
    public function missingImages(Space $space, array $names): array
    {
        $missing = [];
        foreach ($names as $name) {
            if (!file_exists($this->keptImagePath($space, $name))) {
                $missing[] = $name;
            }
        }

        return $missing;
    }

    private function keptImagePath(Space $space, string $name): string
    {
        return $space->getPath() . '/renderer_images/kept/' . $name . '.png';
    }

    public function clear(Space $space): void
    {
        $this->vectorStore->clear($space);
    }
}