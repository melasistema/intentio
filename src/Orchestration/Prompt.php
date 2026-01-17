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
        $contextString = implode("\n---\n", $this->context);

        return "Context:\n{$contextString}\n\nQuery: {$this->query}\n\nAnswer:";
    }
}
