<?php

declare(strict_types=1);

namespace Intentio\Domain\Cognitive;

use Intentio\Domain\Space\Space;
use Intentio\Domain\Model\LLMInterface;
use Intentio\Domain\Model\ImageRendererInterface; // Import the new interface

final readonly class CognitiveEngine
{
    public function __construct(
        private LLMInterface         $llmAdapter,
        private IngestionService     $ingestionService,
        private RetrievalService     $retrievalService,
        private VectorStoreInterface $vectorStore,
        private ImageRendererInterface $imageRenderer // Add the new dependency
    )
    {
    }

    public function ingest(Space $space): void
    {
        $this->ingestionService->ingestSpace($space);
    }

    public function chat(Space $space, string $message, array $options = []): string
    {
        // Extract prompt details from options, provided by InteractCommand
        $promptContent = $options['prompt_content'] ?? '';
        $promptInstruction = $options['prompt_instruction'] ?? '';
        $promptContextFiles = $options['context_files'] ?? [];

        // 1. Retrieve relevant context from the space (from vector store)
        $retrievedContext = $this->retrievalService->retrieve($space, $message, $options['retrieval_limit'] ?? 5);

        // 2. Load additional context from referenced files
        $additionalContext = [];
        foreach ($promptContextFiles as $filePath) {
            if (file_exists($filePath) && is_readable($filePath)) {
                $additionalContext[] = "--- Content from " . basename($filePath) . " ---\n" . file_get_contents($filePath);
            }
        }
        $fullContextContent = implode("\n\n", $additionalContext);

        // Format retrieved context for the LLM
        $retrievedContextString = implode("\n\n", array_map(function ($item) {
            return "Source: {" . $item['source'] . "}\nContent: {" . $item['content'] . "}";
        }, $retrievedContext));

        // Combine all context: additional files + retrieved chunks
        $finalContext = [];
        if (!empty($fullContextContent)) {
            $finalContext[] = "### Knowledge Base\n" . $fullContextContent;
        }
        if (!empty($retrievedContextString)) {
            $finalContext[] = "### Retrieved Information\n" . $retrievedContextString;
        }
        $finalContextString = implode("\n\n", $finalContext);


        // 3. Construct the full prompt for the LLM
        $fullPrompt = sprintf(
            "%s\n%s\n\n%s", // Instruction, Context, Main Prompt (content with QUERY placeholder)
            empty($promptInstruction) ? '' : "Instruction: " . $promptInstruction, // Add instruction if present
            empty($finalContextString) ? '' : "Context:\n" . $finalContextString,
            str_replace('{{QUERY}}', $message, $promptContent) // Replace {{QUERY}} placeholder
        );
        // Clean up empty lines that might result from missing instruction or context
        $fullPrompt = preg_replace("/\n{2,}/", "\n\n", $fullPrompt);


        // 4. Get response from LLM
        return $this->llmAdapter->generate($fullPrompt, '', $options['llm_options'] ?? []);
    }

    public function render(Space $space, string $query, array $options): string // Changed return type to string
    {
        // Construct space-specific renderer folder path
        $spaceRendererFolder = $space->getPath() . '/renderer_images';

        // Now use the dedicated image renderer
        return $this->imageRenderer->render($query, $spaceRendererFolder, $options);
    }

    public function clear(Space $space): void
    {
        fwrite(STDOUT, "CognitiveEngine: Clearing ingested data for space '" . $space->getName() . "'." . PHP_EOL);
        $this->vectorStore->clear($space);
        fwrite(STDOUT, "CognitiveEngine: Ingested data cleared for space '" . $space->getName() . "'." . PHP_EOL);
    }
}