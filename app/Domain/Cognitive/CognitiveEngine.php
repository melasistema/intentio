<?php

declare(strict_types=1);

namespace Intentio\Domain\Cognitive;

use Intentio\Domain\Space\Space;
use Intentio\Domain\Model\LLMInterface;
use Intentio\Domain\Model\ImageRendererInterface;

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
     * @return array The 'answer' and what it was built from: 'pinned' (files given in full, as paths relative
     *               to the knowledge folder), 'retrieved' (passages, each with 'source' and 'score'),
     *               and 'warning' (null, or why the model may not have seen all of it).
     */
    public function chat(Space $space, string $message, string $promptTemplate = '{{QUERY}}', array $pinnedFiles = []): array
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
            'answer' => $this->llmAdapter->generate($prompt),
            'pinned' => $pinnedPaths,
            'retrieved' => $retrieved,
            'warning' => $this->contextWindowWarning($prompt),
        ];
    }

    /**
     * Compares the size of a prompt with the model's context window.
     * The size is an estimate: about four characters make a token.
     */
    private function contextWindowWarning(string $prompt): ?string
    {
        $estimatedTokens = (int) ceil(strlen($prompt) / 4);

        if ($estimatedTokens <= $this->contextWindow) {
            return null;
        }

        return "the prompt is about {$estimatedTokens} tokens and the context window is {$this->contextWindow}, "
            . "so the model did not see all of it. Pin fewer files, or raise llm.options.num_ctx in the configuration.";
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

    public function render(Space $space, string $query, array $options): string
    {
        // Construct space-specific renderer folder path
        $spaceRendererFolder = $space->getPath() . '/renderer_images';

        // Now use the dedicated image renderer
        return $this->imageRenderer->render($query, $spaceRendererFolder, $options);
    }

    public function clear(Space $space): void
    {
        $this->vectorStore->clear($space);
    }
}