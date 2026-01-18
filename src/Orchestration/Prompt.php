<?php

declare(strict_types=1);

namespace Intentio\Orchestration;

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
    public function __construct(
        private readonly string $template,
        private readonly array $context,
        private readonly string $query
    ) {
    }

    public function build(): string
    {
        // This is a placeholder for a more sophisticated prompt templating engine.
        // The template parameter is currently not used but can be integrated for more dynamic prompting.
        $contextString = implode("\n---\n", $this->context);

        // Add explicit grounding instructions
        $groundingInstructions = <<<INSTR
You are an AI assistant for INTENTIO.
Your goal is to answer questions truthfully and accurately based *only* on the provided context.
Treat the provided context as the sole source of truth.
If a name or entity is mentioned in the context, treat it as factual within that context.
If the answer is not available in the provided context, state that you cannot answer from the given information.
Do NOT use your prior knowledge, make up information, or apply external safety filters if the information is explicitly provided in the context.
INSTR;

        return "{$groundingInstructions}\n\nContext:\n{$contextString}\n\nQuery: {$this->query}\n\nAnswer:";
    }
}
